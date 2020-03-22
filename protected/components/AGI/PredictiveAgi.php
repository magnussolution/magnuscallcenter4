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

class PredictiveAgi
{
    public function send($agi, &$MAGNUS, &$Calc)
    {
        $agi->verbose("[Type Call Predictive]", 3);
        $agi->verbose(date("Y-m-d H:i:s") . " => $MAGNUS->dnid, Cliente Atendeu a chamada, campanha " . $agi->get_variable("CAMPAIGN_ID", true), 2);

        if ($agi->get_variable("SPEECH", true) > 0) {
            $agi->execute('AGI speech-recog.agi,"pt-BR",2,,NOBEEP,5000');
            $textASR = strtolower($agi->get_variable("utterance", true));

            $agi->verbose('O texto que você acabou de dizer: ' . $textASR);
            if (preg_match('/oi|alô,olá/', $textASR)) {
                $agi->verbose('E HUMANO');
            }

        }

        $modelCampaign = Campaign::model()->findByPk((int) $agi->get_variable("CAMPAIGN_ID", true));
        $agi->verbose("[CAMPAIGN NAME " . $modelCampaign->name . " " . $MAGNUS->uniqueid, 20);

        if ($agi->get_variable("MASSIVE_CALL", true) > 0) {
            $startTime = $agi->get_variable("STARTCALL", true);
        } else {
            $modelPredictive              = new Predictive();
            $modelPredictive->id_campaign = $modelCampaign->id;
            $modelPredictive->number      = $agi->get_variable("PHONENUMBER_ID", true);
            $modelPredictive->uniqueid    = $MAGNUS->uniqueid;
            if ($MAGNUS->config['agi-conf1']['amd'] == 1 && preg_match("/MACHINE/", $agi->get_variable("AMDSTATUS", true))) {
                $modelPredictive->amd = 1;
            }
            $modelPredictive->save();
            $startTime = time();
        }
        $callerID = $agi->get_variable("CALLED", true);
        $agi->set_callerid($callerID);
        $agi->set_variable("CALLERID(num)", $callerID);
        $agi->set_variable("CALLERID(all)", "$callerID <$callerID>");

        $agi->verbose('Predictive - Send call to Campaign ' . $modelCampaign->name, 5);

        //calcula o tempo que gastou para atender o numero
        $ringing_time = $startTime - $agi->get_variable("STARTCALL", true);
        $agi->verbose($agi->get_variable("ALEARORIO", true), 25);

        //SET uniqueid para ser atualizado a tabela pkg_predictive quando a ligação for atendida
        $agi->set_variable("UNIQUEID", $MAGNUS->uniqueid);

        if ($MAGNUS->config['agi-conf1']['amd'] == 1 && preg_match("/MACHINE/", $agi->get_variable("AMDSTATUS", true))) {

            $amd_status = $agi->get_variable("AMDSTATUS", true);
            $agi->verbose(date("Y-m-d H:i:s") . " => " . $MAGNUS->dnid . ': amd_status ' . $amd_status . ", hangup call", 5);

            PredictiveGen::model()->updateAll(
                array('ringing_time' => $ringing_time, 'amd' => 1),
                'uniqueID = :key',
                array(':key' => $agi->get_variable("ALEARORIO", true))
            );

            //pega o usuario que atendeu a chamada
            $modelUser               = User::model()->find('id_group = 1');
            $MAGNUS->forceIdCaterory = '-2';
        } else {
            PredictiveGen::model()->updateAll(
                array('ringing_time' => $ringing_time),
                'uniqueID = :key',
                array(':key' => $agi->get_variable("ALEARORIO", true))
            );

            $agi->verbose(date("Y-m-d H:i:s") . " => $MAGNUS->dnid, enviado para queue " . $modelCampaign->name, 10);

            if (!$agi->get_variable("MEMBERNAME", true)) {

                $callerID = $agi->get_variable("CALLED", true);
                $agi->verbose('$callerID' . $callerID, 6);
                $agi->set_callerid($callerID);
                $agi->set_variable("CALLERID(num)", $callerID);
                $agi->set_variable("CALLERID(all)", "$callerID <$callerID>");

                $agi->execute("Queue", $modelCampaign->name . ',,,,60,/var/www/html/callcenter/agi.php');

                if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                    $myres = $agi->execute("StopMixMonitor");
                    $agi->verbose("EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")", 5);
                    if (file_exists("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm")) {
                        if (!is_dir("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY'))) {
                            exec("mkdir " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY'));
                        }
                        $agi->verbose("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/", 15);

                        exec("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                    }
                }
            }

            $agi->verbose(date("Y-m-d H:i:s") . " => $MAGNUS->dnid, " . $MAGNUS->uniqueid . " DELIGOU A CHAMADAS", 15);
            //pega o usuario que atendeu a chamada
            $modelUser = User::model()->find('username = (SELECT operador FROM pkg_predictive WHERE uniqueid = :key)',
                array(':key' => $MAGNUS->uniqueid));

            if (!isset($modelUser->id)) {
                // phonenumber loave the queue without answered, active to call again
                PhoneNumber::model()->updateByPk($agi->get_variable("PHONENUMBER_ID", true), array('id_category' => 1, 'status' => 1));
                $MAGNUS->forceIdCaterory = '-3';
            }
        }

        $endTime = time();

        $Calc->answeredtime = $Calc->real_answeredtime = $endTime - $startTime;

        $MAGNUS->id_user = $modelUser->id;

        $Calc->usedtrunk                          = $agi->get_variable("IDTRUNK", true);
        $Calc->tariffObj[0]['id_campaign_number'] = $agi->get_variable("CAMPAIGN_ID", true);

        $Calc->tariffObj[0]['id_phonebook'] = $agi->get_variable("IDPHONEBOOK", true);
        $Calc->tariffObj[0]['id']           = $agi->get_variable("PHONENUMBER_ID", true);

        $MAGNUS->dnid     = $agi->get_variable("CALLERID", true);
        $terminatecauseid = $Calc->answeredtime > 0 ? 1 : 0;
        $Calc->updateSystem($MAGNUS, $agi, $MAGNUS->dnid, $terminatecauseid);
    }

}
