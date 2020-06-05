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
class Calc
{
    public $lastcost            = 0;
    public $lastbuycost         = 0;
    public $answeredtime        = 0;
    public $real_answeredtime   = 0;
    public $dialstatus          = 0;
    public $usedratecard        = 0;
    public $usedtrunk           = 0;
    public $freetimetocall_used = 0;
    public $dialstatus_rev_list;
    public $tariffObj           = array();
    public $freetimetocall_left = array();
    public $freecall            = array();
    public $offerToApply        = array();
    public $number_trunk        = 0;
    public $idCallCallBack      = 0;
    public $agent_bill          = 0;
    public $portabilidadeTrunks = array();

    public function Calc()
    {
        $this->dialstatus_rev_list = Magnus::getDialStatus_Revert_List();
    }

    public function init()
    {
        $this->number_trunk      = 0;
        $this->answeredtime      = 0;
        $this->real_answeredtime = 0;
        $this->dialstatus        = '';
        $this->usedratecard      = '';
        $this->usedtrunk         = '';
        $this->lastcost          = '';
        $this->lastbuycost       = '';
    }

    public function updateSystem(&$MAGNUS, &$agi, $calledstation, $terminatecauseid, $doibill = 1, $didcall = 0, $callback = 0)
    {
        $agi->verbose('Update System', 25);

        $dialstatus             = $this->dialstatus;
        $sessiontime            = $this->answeredtime > 0 ? $this->answeredtime : 0;
        $id_campaign_number     = $this->tariffObj[0]['id_campaign_number'];
        $id_phonebook           = $this->tariffObj[0]['id_phonebook'];
        $id_phonenumber         = $this->tariffObj[0]['id'];
        $MAGNUS->id_phonenumber = $this->tariffObj[0]['id'];

        if ($terminatecauseid == 1) {
            $terminatecauseid = 1;
        } else if (strlen($this->dialstatus_rev_list[$dialstatus]) > 0) {
            $terminatecauseid = $this->dialstatus_rev_list[$dialstatus];
        } else {
            $terminatecauseid = 0;
        }

        $MAGNUS->id_user = isset($MAGNUS->id_user) ? $MAGNUS->id_user : 'NULL';

        if (strlen($MAGNUS->forceIdCaterory) > 0) {
            $id_category = $MAGNUS->forceIdCaterory;
        } else {
            $id_category = "NULL";
        }

        if (!is_numeric($this->usedtrunk)) {
            $this->usedtrunk = "NULL";
        }

        $fields = "uniqueid, sessionid, id_category,  id_user, id_campaign, id_phonebook, id_phonenumber, id_trunk, starttime, stoptime, sessiontime, calledstation, terminatecauseid, real_sessiontime, dnid";
        $value  = "'$MAGNUS->uniqueid', '$MAGNUS->channel', $id_category, $MAGNUS->id_user, $id_campaign_number, $id_phonebook, $id_phonenumber, $this->usedtrunk, SUBDATE(CURRENT_TIMESTAMP, INTERVAL $sessiontime SECOND), now(), '$sessiontime', '$calledstation', '$terminatecauseid', '$this->real_answeredtime', '$MAGNUS->dnid'";

        $sql = "INSERT INTO pkg_cdr ($fields) VALUES ($value)";

        $agi->verbose($sql, 25);

        try {
            Yii::app()->db->createCommand($sql)->execute();
        } catch (Exception $e) {
            $agi->verbose($e->getMessage(), 1);
        }
        $sql = "SELECT * FROM pkg_campaign WHERE id = " . $id_campaign_number;
        $agi->verbose($sql, 25);
        $resultCampaign = Yii::app()->db->createCommand($sql)->queryAll();

        if ($sessiontime > 0) {

            $sql = "UPDATE pkg_trunk SET secondusedreal = secondusedreal + $sessiontime WHERE id='" . $this->usedtrunk . "'";
            Yii::app()->db->createCommand($sql)->execute();
        }
        $sql = "UPDATE pkg_operator_status SET categorizing = '1', time_start_cat = '" . time() . "' WHERE id_user='" . $MAGNUS->id_user . "'";
        $agi->verbose($sql, 25);
        Yii::app()->db->createCommand($sql)->execute();

        $modelUser = User::model()->findByPk($MAGNUS->id_user);
        $agi->verbose($modelUser->username . ' ' . $resultCampaign[0]['name'], 5);
        AsteriskAccess::instance()->queuePauseMember($modelUser->username, $resultCampaign[0]['name'], 'categorizing');

    }

    public function sendCall(&$MAGNUS, $agi, $destination, $typecall = 0)
    {

        $agi->verbose('sendCall', 6);

        $max_long = 2147483647;

        if (substr("$destination", 0, 4) == 1111) /*Retira o techprefix de numeros portados*/ {
            $destination = str_replace(substr($destination, 0, 7), "", $destination);
        }
        $old_destination = $destination;

        for ($k = 0; $k < count($this->tariffObj); $k++) {
            $destination = $old_destination;

            $prefix         = $this->tariffObj[$k]['trunkprefix'];
            $tech           = $this->tariffObj[$k]['providertech'];
            $removeprefix   = $this->tariffObj[$k]['removeprefix'];
            $failover_trunk = $this->tariffObj[$k]['failover_trunk'];
            $trunkcode      = $this->tariffObj[$k]['trunkcode'];
            $timeout        = 7200;

            if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                $destination = substr($destination, strlen($removeprefix));
            }

            $dialparams = str_replace("%timeout%", min($timeout * 1000, $max_long), $MAGNUS->agiconfig['dialcommand_param']);
            $dialparams = str_replace("%timeoutsec%", min($timeout, $max_long), $dialparams);

            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                if (substr($MAGNUS->destination, 0, 4) == 1111) /*Retira o techprefix de numeros portados*/ {
                    $number = str_replace(substr($MAGNUS->destination, 0, 7), "", $MAGNUS->destination);
                } else {
                    $number = $MAGNUS->destination;
                }
                $date  = date('dmY');
                $myres = $agi->execute("MixMonitor " . $MAGNUS->config['global']['record_patch'] . "/{$date}/{$MAGNUS->destination}.{$MAGNUS->uniqueid}.gsm,b");
                $agi->verbose("MixMonitor " . $MAGNUS->config['global']['record_patch'] . "/{$date}/{$MAGNUS->destination}.{$MAGNUS->uniqueid}.gsm,b");
            }

            $dialstr = "$tech/$prefix$destination@$trunkcode" . $dialparams;

            if (strlen($addparameter) > 0) {
                $addparameter = str_replace("%usename%", $MAGNUS->username, $addparameter);
                $addparameter = str_replace("%dialingnumber%", $prefix . $destination, $addparameter);
                $dialstr      = "$tech/$trunkcode/$prefix$destination" . $addparameter;
            }

            $outcid = 0;

            try {
                $MAGNUS->run_dial($agi, $dialstr);
            } catch (Exception $e) {
            }

            if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                $myres = $agi->execute("StopMixMonitor");
                $agi->verbose("EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")", 25);
                if (file_exists($MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm")) {
                    $agi->verbose("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                    exec("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                }
            }

            $answeredtime            = $agi->get_variable("ANSWEREDTIME");
            $this->real_answeredtime = $this->answeredtime = $answeredtime['data'];
            $dialstatus              = $agi->get_variable("DIALSTATUS");
            $this->dialstatus        = $dialstatus['data'];

            $loop_failover = 0;

            while (is_numeric($failover_trunk) && $failover_trunk > 0 && ($this->dialstatus == "CHANUNAVAIL" || $this->dialstatus == "CONGESTION") && $loop_failover < 10) {
                $loop_failover++;
                $this->real_answeredtime = $this->answeredtime = 0;
                $this->usedtrunk         = $failover_trunk;
                $agi->verbose("K=$k -> ANSWEREDTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus, 10);
                $destination = $old_destination;

                $sql = "SELECT trunkprefix, providertech, providerip, removeprefix, failover_trunk, trunkcode FROM pkg_trunk WHERE id='$failover_trunk'";
                $agi->verbose($sql, 25);
                $result = Yii::app()->db->createCommand($sql)->queryAll();

                if (is_array($result) && count($result) > 0) {
                    $prefix       = $result[0]['trunkprefix'];
                    $tech         = $result[0]['providertech'];
                    $removeprefix = $result[0]['removeprefix'];
                    $trunkcode    = $result[0]['trunkcode'];

                    $next_failover_trunk = !is_numeric($result[0]['failover_trunk']) && isset($this->portabilidadeTrunks[$loop_failover + 1]['id']) ?
                    $this->portabilidadeTrunks[$loop_failover + 1]['id'] : $result[0]['failover_trunk'];

                    $addparameter = str_replace("15", "", $addparameter);

                    if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                        $destination = substr($destination, strlen($removeprefix));
                    }
                    $agi->verbose("Now using failover trunk -> TRUNK => $trunkcode -> ERROR => $this->dialstatus ", 6);
                    $dialparams = preg_replace("/\%timeout\%/", min($timeout * 1000, $max_long), $MAGNUS->agiconfig['dialcommand_param']);

                    $dialstr = "$tech/$prefix$destination@$trunkcode" . $dialparams;

                    if (strlen($addparameter) > 0) {
                        $addparameter = str_replace("%cardnumber%", $MAGNUS->cardnumber, $addparameter);
                        $addparameter = str_replace("%dialingnumber%", $prefix . $destination, $addparameter);
                        $dialstr      = "$tech/$trunkcode/$prefix$destination/" . $addparameter;
                    }
                    $agi->verbose("FAILOVER app_callingcard: Dialing '$dialstr' with timeout of '$timeout'.", 15);

                    if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                        $date  = date("dmY");
                        $myres = $agi->execute("MixMonitor " . $MAGNUS->config['global']['record_patch'] . "/{$date}/{$MAGNUS->destination}.{$MAGNUS->uniqueid}.gsm,b");
                        $agi->verbose("MixMonitor " . $MAGNUS->config['global']['record_patch'] . "/{$date}/{$MAGNUS->destination}.{$MAGNUS->uniqueid}.gsm,b");
                    }

                    $MAGNUS->run_dial($agi, $dialstr);

                    if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
                        $myres = $agi->execute("StopMixMonitor");
                        $agi->verbose("EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")");
                        if (file_exists("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm")) {
                            if (!is_dir("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY'))) {
                                exec("mkdir " . $MAGNUS->config['global']['record_patch'] . "" . date('dmY'));
                            }
                            $agi->verbose("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                            exec("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                        }
                    }

                    $answeredtime            = $agi->get_variable("ANSWEREDTIME");
                    $this->real_answeredtime = $this->answeredtime = $answeredtime['data'];
                    $dialstatus              = $agi->get_variable("DIALSTATUS");
                    $this->dialstatus        = $dialstatus['data'];

                    $agi->verbose("[FAILOVER K=$k]:[ANSTIME=" . $this->answeredtime . "-DIALSTATUS=" . $this->dialstatus, 15);
                }
                $agi->verbose("$next_failover_trunk == $failover_trunk");
                /* IF THE FAILOVER TRUNK IS SAME AS THE ACTUAL TRUNK WE BREAK */
                if ($next_failover_trunk == $failover_trunk) {
                    break;
                } else {
                    $failover_trunk = $next_failover_trunk;
                }

            }

            if (($this->dialstatus == "CANCEL")) {
                return true;
            }

            if ($this->tariffObj[$k]['status'] != 1) /*Change dialstatus of the trunk for send for LCR/LCD prefix*/ {
                if ($MAGNUS->agiconfig['failover_lc_prefix']) {
                    continue;
                }

            }

            /* END FOR LOOP FAILOVER */
            /*# Ooh, something actually happened! */
            if ($this->dialstatus == "BUSY") {
                $this->real_answeredtime = $this->answeredtime = 0;
                $agi->stream_file('prepaid-isbusy', '#');

            } elseif ($this->dialstatus == "NOANSWER") {
                $this->real_answeredtime = $this->answeredtime = 0;
                $agi->stream_file('prepaid-noanswer', '#');

            } elseif ($this->dialstatus == "CANCEL") {
                $this->real_answeredtime = $this->answeredtime = 0;
            } elseif (($this->dialstatus == "CHANUNAVAIL") || ($this->dialstatus == "CONGESTION")) {
                $this->real_answeredtime = $this->answeredtime = 0;
                /* Check if we will failover for LCR/LCD prefix - better false for an exact billing on resell */
                if ($MAGNUS->agiconfig['failover_lc_prefix']) {
                    $agi->verbose("Call send for backup trunk -> ERROR => $this->dialstatus", 6);
                    continue;
                }
                return false;
            }

            $this->usedratecard = $k;
            $agi->verbose("USED TARIFF=" . $this->usedratecard, 10);
            return true;
        }
        /* End for */
        $this->usedratecard = $k - $loop_failover;
        $agi->verbose("USED TARIFF - FAIL =" . $this->usedratecard, 10);
        return false;
    }
};
