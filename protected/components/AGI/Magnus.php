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
class Magnus
{
    public $config;
    public $agiconfig;
    public $idconfig = 1;
    public $agentUsername;
    public $CallerID;
    public $channel;
    public $uniqueid;
    public $accountcode;
    public $dnid;
    public $extension;
    public $statchannel;
    public $destination;
    public $credit;
    public $id_plan;
    public $active;
    public $currency = 'usd';
    public $mode     = '';
    public $timeout;
    public $tech;
    public $prefix;
    public $username;
    public $typepaid          = 0;
    public $removeinterprefix = 1;
    public $restriction       = 1;
    public $redial;
    public $enableexpire;
    public $expirationdate;
    public $expiredays;
    public $creationdate;
    public $creditlimit = 0;
    public $id_user;
    public $countryCode;
    public $add_credit;
    public $dialstatus_rev_list;
    public $callshop;
    public $id_plan_agent;
    public $id_offer;
    public $record_call;
    public $mix_monitor_format = 'gsm';
    public $prefix_local;
    public $id_agent;
    public $portabilidade        = false;
    public $play_audio           = false;
    public $magnusFilesDirectory = '/usr/local/src/magnus/';
    public $forceIdCaterory;
    public $allow_direct_call = 0;

    public function Magnus()
    {

        $this->config             = LoadConfig::getConfig();
        $this->mix_monitor_format = $this->config['global']['MixMonitor_format'];
        $this->agiconfig          = $this->config['agi-conf1'];
        $this->record_call == $this->agiconfig['record_call'];

        $this->dialstatus_rev_list = $this->getDialStatus_Revert_List();
    }

    public function init()
    {
        $this->destination = '';
    }

    //hangup($agi);
    public function hangup(&$agi)
    {
        $agi->verbose('Hangup Call ' . $this->destination . ' Username ' . $this->username, 6);
        $agi->hangup();
        exit;
    }

    public function get_agi_request_parameter($agi)
    {

        $this->accountcode = $agi->request['agi_accountcode'];
        $this->dnid        = $agi->request['agi_extension'];

        $this->CallerID = $agi->request['agi_callerid'];
        $this->channel  = $agi->request['agi_channel'];
        $this->uniqueid = $agi->request['agi_uniqueid'];

        $this->lastapp = isset($agi->request['agi_lastapp']) ? $agi->request['agi_lastapp'] : null;

        $stat_channel      = $agi->channel_status($this->channel);
        $this->statchannel = $stat_channel["data"];

        if (preg_match('/Local/', $this->channel) && strlen($this->accountcode) < 4) {
            $sql    = "SELECT * FROM pkg_sip WHERE name='" . $this->dnid . "'";
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $agi->verbose($sql, 25);
            $this->accountcode = $result[0]['accountcode'];
        }

        $pos_lt = strpos($this->CallerID, '<');
        $pos_gt = strpos($this->CallerID, '>');
        if (($pos_lt !== false) && ($pos_gt !== false)) {
            $len_gt         = $pos_gt - $pos_lt - 1;
            $this->CallerID = substr($this->CallerID, $pos_lt + 1, $len_gt);
        }
        $msg = ' get_agi_request_parameter = ' . $this->statchannel . ' ; ' . $this->CallerID . ' ; ' . $this->channel . ' ; ' . $this->uniqueid . ' ; ' . $this->accountcode . ' ; ' . $this->dnid;
        $agi->verbose($msg, 15);
    }

    public function getDialStatus_Revert_List()
    {
        $dialstatus_rev_list                = array();
        $dialstatus_rev_list["ANSWER"]      = 1;
        $dialstatus_rev_list["BUSY"]        = 2;
        $dialstatus_rev_list["NOANSWER"]    = 3;
        $dialstatus_rev_list["CANCEL"]      = 4;
        $dialstatus_rev_list["CONGESTION"]  = 5;
        $dialstatus_rev_list["CHANUNAVAIL"] = 6;
        $dialstatus_rev_list["DONTCALL"]    = 7;
        $dialstatus_rev_list["TORTURE"]     = 8;
        $dialstatus_rev_list["INVALIDARGS"] = 9;
        return $dialstatus_rev_list;
    }

    public function checkNumber($agi, &$Calc, $try_num, $call2did = false)
    {
        $res = 0;

        if ($this->extension == 's') {
            $this->destination = $this->dnid;
        } else {
            $this->destination = $this->extension;
        }
        $agi->verbose("USE_DNID DESTINATION -> " . $this->destination, 10);

        $this->destination = preg_replace("/\-/", "", $this->destination);
        $this->dnid        = preg_replace("/\-/", "", $this->dnid);
        $this->destination = preg_replace("/\./", "", $this->destination);
        $this->dnid        = preg_replace("/\./", "", $this->dnid);
        $this->destination = preg_replace("/\(/", "", $this->destination);
        $this->dnid        = preg_replace("/\(/", "", $this->dnid);
        $this->destination = preg_replace("/\)/", "", $this->destination);
        $this->dnid        = preg_replace("/\)/", "", $this->dnid);

        $this->destination = $this->transform_number_ar_br($agi, $this->destination);

        $this->destination = rtrim($this->destination, "#");
        if ($this->destination <= 0) {
            $prompt = "prepaid-invalid-digits";
            $agi->verbose($prompt, 3);
            if (is_numeric($this->destination)) {
                $agi->answer();
            }

            $agi->stream_file($prompt, '#');
            $this->hangup($agi);
        }
        $this->destination = str_replace('*', '', $this->destination);

        $data = date("d-m-y");

        $agi->destination = $this->destination;

        /*call funtion for search rates*/
        $searchTariff = new SearchTariff();
        if ($this->allow_direct_call == 1) {
            $resfindrate = $searchTariff->find('0', $this->id_campaign, $agi);
        } else {
            $resfindrate = $searchTariff->find($this->destination, $this->id_campaign, $agi);
        }

        $Calc->usedtrunk           = $resfindrate[0]['id_trunk'];
        $Calc->tariffObj           = $resfindrate;
        $Calc->number_trunk        = 1;
        $Calc->portabilidadeTrunks = $resfindrate[0]['portabilidadeTrunks'];

        if ($resfindrate == 0) {
            $agi->verbose("ERROR ::> Nao tem permissao para chamar para $this->destination, campaign $this->id_campaign", 25);

            $agi->answer();
            $prompt = "prepaid-dest-unreachable";
            $agi->verbose("destination no found", 3);
            $agi->stream_file($prompt, '#');
            $this->hangup($agi);

        } else {
            $agi->verbose("NUMBER TARIFF FOUND -> " . $resfindrate, 10);
        }

        /* calculate timeout*/
        $this->timeout = 7200;
        $timeout       = $this->timeout;
        $agi->verbose("timeout ->> $timeout", 15);
        return 1;
    }

    public function save_redial_number($agi, $number)
    {
        if (($this->mode == 'did') || ($this->mode == 'callback')) {
            return;
        }
        $sql = "UPDATE pkg_user SET redial = '{$number}' WHERE username='" . $this->accountcode . "'";
        Yii::app()->db->createCommand($sql)->execute();
        $agi->verbose($sql, 25);
    }

    public function run_dial($agi, $dialstr)
    {
        /* Run dial command */
        if (strlen($this->agiconfig['amd']) > 1) {
            $dialstr .= $this->agiconfig['amd'];
        }
        $res_dial = $agi->execute("DIAL $dialstr");

        return $res_dial;
    }

    public function transform_number_ar_br($agi, $number)
    {

        $number = $this->number_translation($agi, $this->prefix_local, $number);

        //$number = Portabilidade::consulta($agi, $this, $number);

        return $number;
    }

    public function number_translation($agi, $translation, $destination)
    {
        #match / replace / if match length
        #0/54,4/543424/7,15/549342/9

        $translation = "0/55,*/5511/8,*/5511/9";

        $regexs = preg_split("/,/", $translation);

        foreach ($regexs as $key => $regex) {

            $regra   = preg_split("/\//", $regex);
            $grab    = $regra[0];
            $replace = isset($regra[1]) ? $regra[1] : '';
            $digit   = isset($regra[2]) ? $regra[2] : '';

            $agi->verbose("Grab :$grab Replacement: $replace Phone Before: $destination", 25);

            $number_prefix = substr($destination, 0, strlen($grab));

            if ($this->config['global']['base_country'] == 'brl' || $this->config['global']['base_country'] == 'BRL' || $this->config['global']['base_country'] == 'ARG' || $this->config['global']['base_country'] == 'arg') {
                if ($grab == '*' && strlen($destination) == $digit) {
                    $destination = $replace . $destination;
                } else if (strlen($destination) == $digit && $number_prefix == $grab) {
                    $destination = $replace . substr($destination, strlen($grab));
                } elseif ($number_prefix == $grab) {
                    $destination = $replace . substr($destination, strlen($grab));
                }

            } else {

                if (strlen($destination) == $digit) {
                    if ($grab == '*' && strlen($destination) == $digit) {
                        $destination = $replace . $destination;
                    } else if ($number_prefix == $grab) {
                        $destination = $replace . substr($destination, strlen($grab));
                    }
                }
            }
        }

        $agi->verbose("Phone After translation: $destination", 10);

        return $destination;
    }

    public function sqliteInsert($agi, $fields, $value, $table)
    {

        $sql        = "INSERT INTO $table ($fields) VALUES ($value)";
        $create     = false;
        $cache_path = '/etc/asterisk/cache_mbilling.sqlite';

        try {
            $db = new SQLite3($cache_path);

            $db->exec('CREATE TABLE IF NOT EXISTS ' . $table . ' (' . $fields . ');');
            $db->exec($sql);

        } catch (Exception $e) {
            $agi->verbose("\n\nError to connect to cache : $sqliteerror\n\n");
        }
    }

    public function round_precision($number)
    {
        $PRECISION = 6;
        return round($number, $PRECISION);
    }
};
