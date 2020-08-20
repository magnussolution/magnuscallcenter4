<?php
/**
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 *
 */

class IvrAgi
{
    public function callIvr($agi, &$MAGNUS, &$Calc, $result_did, $type = 'ivr', $startTime = 0)
    {
        $uploaddir = $MAGNUS->magnusFilesDirectory . 'sounds/';
        $agi->verbose("Ivr module", 5);
        $agi->answer();
        $startTime = $startTime == 0 ? time() : $startTime;

        $MAGNUS->destination = $result_did[0]['did'];

        $modelIvr = Ivr::model()->findByPk((int) $result_did[0]['id_ivr']);

        $work = IvrAgi::checkIVRSchedule($modelIvr);

        //esta dentro do hario de atencao
        if ($work == 'open') {
            $audioURA   = 'idIvrDidWork_';
            $optionName = 'option_';
        } else {
            $audioURA   = 'idIvrDidNoWork_';
            $optionName = 'option_out_';
        }

        $continue  = true;
        $insertCDR = false;
        $i         = 0;
        while ($continue == true) {
            $agi->verbose("EXECUTE IVR " . $modelIvr->name);
            $i++;

            if ($i == 10) {
                $continue = false;
                break;
            }
            $audio = $uploaddir . $audioURA . $result_did[0]['id_ivr'];

            if (file_exists($audio . ".gsm") || file_exists($audio . ".wav")) {
                $res_dtmf = $agi->get_data($audio, 3000, 1);
                $option   = $res_dtmf['result'];
            } else {
                $agi->verbose('NOT EXIST AUDIO TO IVR DEFAULT OPTION ' . $audio, 5);
                $option   = '10';
                $continue = false;
            }

            $agi->verbose(print_r($res_dtmf, true), 5);
            $option = $res_dtmf['result'];
            $agi->verbose('option' . $option);
            //se nao marcou
            if (strlen($option) < 1) {
                $agi->verbose('DEFAULT OPTION');
                $option   = '10';
                $continue = false;
            } else if (!isset($modelIvr->{$optionName . $option})) {
                $agi->verbose('option 77');
                break;
            } else if ($modelIvr->{$optionName . $option} == '') {
                //se marca uma opÃ§ao que esta em branco
                $agi->verbose('NUMBER INVALID');
                $agi->stream_file('prepaid-invalid-digits', '#');
                continue;
            }

            $dtmf        = explode(("|"), $modelIvr->{$optionName . $option});
            $optionType  = $dtmf[0];
            $optionValue = $dtmf[1];
            $agi->verbose("CUSTOMER PRESS $optionType -> $optionValue");

            //check if channel is available
            $asmanager = new AGI_AsteriskManager();
            $asmanager->connect('localhost', 'magnus', 'magnussolution');
            $resultChannel = $asmanager->command("core show channel " . $MAGNUS->channel);
            $arr           = explode("\n", $resultChannel["data"]);
            foreach ($arr as $key => $temp) {
                if (preg_match("/Blocking in/", $temp)) {
                    $arr3 = explode("Blocking in:", $temp);
                    if (preg_match("/Not Blocking/", $arr3[1])) {
                        $agi->verbose("Channel unavailable");
                        $optionType = 'hangup';
                    }
                }
            }

            $asmanager->disconnect();

            if ($optionType == 'user') // QUEUE
            {
                $insertCDR = true;
                $sql       = "SELECT username FROM pkg_user WHERE id = " . $optionValue;
                $agi->verbose($sql, 25);
                $resultSIP = Yii::app()->db->createCommand($sql)->queryAll();

                $dialparams = $dialparams = $MAGNUS->agiconfig['dialcommand_param_sipiax_friend'];
                $dialparams = str_replace("%timeout%", 3600, $dialparams);
                $dialparams = str_replace("%timeoutsec%", 3600, $dialparams);
                $dialstr    = 'SIP/' . $resultSIP[0]['username'] . $dialparams;
                $agi->verbose($dialstr, 25);
                if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                    $command_mixmonitor = "MixMonitor {$username}.{$MAGNUS->destination}.{$MAGNUS->uniqueid}." . $MAGNUS->mix_monitor_format . ",b";
                    $myres              = $agi->execute($command_mixmonitor);
                    $agi->verbose($command_mixmonitor, 5);
                }

                $myres = $MAGNUS->run_dial($agi, $dialstr);

                $dialstatus = $agi->get_variable("DIALSTATUS");
                $dialstatus = $dialstatus['data'];

                if ($dialstatus == "NOANSWER") {
                    $answeredtime = 0;
                    $agi->stream_file('prepaid-callfollowme', '#');
                    continue;
                } elseif (($dialstatus == "BUSY" || $dialstatus == "CHANUNAVAIL") || ($dialstatus == "CONGESTION")) {
                    $agi->stream_file('prepaid-isbusy', '#');
                    continue;
                } else {
                    break;
                }

            } else if ($optionType == 'repeat') // CUSTOM
            {
                $agi->verbose("repetir IVR");
                continue;
            } else if (preg_match("/hangup/", $optionType)) // hangup
            {
                $agi->verbose("Hangup IVR");
                $insertCDR = true;
                break;
            } else if (preg_match("/custom/", $optionType)) // CUSTOM
            {
                $insertCDR = true;
                $myres     = $MAGNUS->run_dial($agi, $optionValue);
            } else if ($optionType == 'ivr') // QUEUE
            {
                $result_did[0]['id_ivr'] = $optionValue;
                IvrAgi::callIvr($agi, $MAGNUS, $Calc, $result_did, $type, $startTime);
            } else if ($optionType == 'campaign') // QUEUE
            {
                $insertCDR                    = false;
                $result_did[0]['id_campaign'] = $optionValue;
                Queue::callQueue($agi, $MAGNUS, $Calc, $result_did, $type, $startTime);
            } else if (preg_match("/^number/", $optionType)) //envia para um fixo ou celular
            {
                $insertCDR = false;
                $agi->verbose("CALL number $optionValue");
                $result_did[0]['id_campaign'] =
                $MAGNUS->call_did($agi, $Calc, $result_did, $optionValue);
            }

            $agi->verbose("FIM do loop");

            $continue  = false;
            $insertCDR = true;

        }

        $stopTime = time();

        $answeredtime = $stopTime - $startTime;

        $terminatecauseid = 1;

        $siptransfer = $agi->get_variable("SIPTRANSFER");

        $linha = end(file('/var/log/asterisk/queue_log'));
        $linha = explode('|', $linha);
        $agi->verbose(print_r($linha, true), 25);

        $tipo = 9;

        if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
            $myres = $agi->execute("StopMixMonitor");
            $agi->verbose("EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")", 5);
        }

        if ($type == 'ivr') {
            $MAGNUS->hangup($agi);
        } else {
            return;
        }

    }

    public function checkIVRSchedule($modelIvr)
    {
        $weekDay = date('D');

        switch ($weekDay) {
            case 'Sun':
                $weekDay = $modelIvr->{'TimeOfDay_sun'};
                break;
            case 'Sat':
                $weekDay = $modelIvr->{'TimeOfDay_sat'};
                break;

            default:
                $weekDay = $modelIvr->{'TimeOfDay_monFri'};
                break;
        }

        $hours   = date('H');
        $minutes = date('i');
        $now     = ($hours * 60) + $minutes;

        $intervals = preg_split("/\|/", $weekDay);

        foreach ($intervals as $key => $interval) {
            $hours = explode('-', $interval);

            $start = $hours[0];
            $end   = $hours[1];

            #convert start hour to minutes
            $hourInterval = explode(':', $start);
            $starthour    = $hourInterval[0] * 60;
            $start        = $starthour + $hourInterval[1];

            #convert end hour to minutes
            $hourInterval = explode(':', $end);
            $starthour    = $hourInterval[0] * 60;
            $end          = $starthour + $hourInterval[1];

            if ($now >= $start && $now <= $end) {
                return "open";
            }
        }

        return "closed";

    }
}
