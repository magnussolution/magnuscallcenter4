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

class Callback
{
    public function callback0800($agi, $MAGNUS, $Calc, $mydnid)
    {

        $agi->verbose("MAGNUS 0800 CALLBACK");

        if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
            $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed", 25);
            $MAGNUS->hangup($agi);
            exit;
        }
        $destination = $MAGNUS->CallerID;

        $removeprefix = $MAGNUS->config['global']['callback_remove_prefix'];

        if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
            $destination = substr($destination, strlen($removeprefix));
        }

        $addprefix   = $MAGNUS->config['global']['callback_add_prefix'];
        $destination = $addprefix . $destination;

        if ($MAGNUS->config['global']['answer_callback'] == 1) {
            $agi->answer();
            sleep(2);
            $agi->stream_file('prepaid-callback', '#');
        }

        $modelPhoneBook = CampaignPhonebook::model()->find('id_campaign = ' . $mydnid[0]['id_campaign']);

        if (count($modelPhoneBook)) {

            $id_phonebook = $modelPhoneBook->id_phonebook;

            //verifica se o numero existe na campanha, se existe ativa ele
            $modelPhoneNumber = PhoneNumber::model()->find("number = :number AND id_phonebook = :id_phonebook",
                array(
                    ":id_phonebook" => $id_phonebook,
                    ":number"       => $destination,
                )
            );

            if (count($modelPhoneNumber)) {

                //if the customer have a schedule, change the chedule to now
                if ($modelPhoneNumber->id_category = 2) {
                    $modelPhoneNumber->datebackcall = date('Y-m-d H:i:s');
                }

                $agi->verbose("CHAMADA RECEBIDA NO 0800 CALLBACK, DE " . $destination . " JA EXISTIA NA CAMPANHA:" . $mydnid[0]['id_campaign']);
            } else {
                $modelPhoneNumber               = new PhoneNumber();
                $modelPhoneNumber->id_phonebook = $id_phonebook;
                $modelPhoneNumber->number       = $destination;

                $agi->verbose("CHAMADA RECEBIDA NO 0800 CALLBACK, DE " . $destination . " ADICIONAR NA CAMPANHA:" . $mydnid[0]['id_campaign']);

            }
            $modelPhoneNumber->id_category = 1;
            $modelPhoneNumber->status      = 1;

            try {
                $modelPhoneNumber->save();
            } catch (Exception $e) {
                Yii::log(print_r($e, true), 'error');
            }

        } else {
            $agi->verbose("CHAMADA RECEBIDA NO 0800 CALLBACK, DE " . $destination . " MAS NAO TEM AGENDA NA CAMPANHA ID:" . $mydnid[0]['id_campaign']);
        }
        $MAGNUS->hangup($agi);

    }

    public function callbackCID($agi, $MAGNUS, $Calc, $mydnid)
    {
        $agi->verbose("MAGNUS CID CALLBACK");
        $MAGNUS->agiconfig['cid_enable'] = 1;

        if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
            $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed", 25);
            $MAGNUS->hangup($agi);
            exit;
        }

        $agi->verbose('CallerID ' . $MAGNUS->CallerID);

        if (strlen($MAGNUS->CallerID) > 1 && is_numeric($MAGNUS->CallerID)) {
            $cia_res = Authenticate::authenticateUser($agi, $MAGNUS);

            if ($cia_res == 0) {

                if (substr($MAGNUS->dnid, 0, 4) == '0000') {
                    $MAGNUS->destination = substr($MAGNUS->dnid, 4);
                } elseif (substr($MAGNUS->dnid, 0, 3) == '000') {
                    $MAGNUS->destination = $MAGNUS->CallerID;
                } else {
                    $MAGNUS->destination = $MAGNUS->countryCode . $MAGNUS->CallerID;
                }

                $agi->verbose('$MAGNUS->destination =>' . $MAGNUS->destination);

                /*protabilidade*/
                $MAGNUS->destination = $MAGNUS->transform_number_ar_br($agi, $MAGNUS->destination);

                $agi->verbose($MAGNUS->countryCode, 15);
                $agi->verbose($MAGNUS->destination, 15);

                $SearchTariff = new SearchTariff();
                $resfindrate  = $SearchTariff->find($MAGNUS->destination, $MAGNUS->id_plan, $agi);

                $Calc->tariffObj    = $resfindrate;
                $Calc->number_trunk = count($resfindrate);

                if (substr("$MAGNUS->destination", 0, 4) == 1111) {
                    $MAGNUS->destination = str_replace(substr($MAGNUS->destination, 0, 7), "", $MAGNUS->destination);
                }

                $Calc->usedratecard = 0;
                if ($resfindrate != 0) {
                    $res_all_calcultimeout = $Calc->calculateAllTimeout($MAGNUS, $MAGNUS->credit, $agi);
                    if ($res_all_calcultimeout) {
                        $destination  = $MAGNUS->destination;
                        $providertech = $Calc->tariffObj[0]['rc_providertech'];
                        $ipaddress    = $Calc->tariffObj[0]['rc_providerip'];
                        $removeprefix = $Calc->tariffObj[0]['rc_removeprefix'];
                        $prefix       = $Calc->tariffObj[0]['rc_trunkprefix'];

                        if (strncmp($destination, $removeprefix, strlen($removeprefix)) == 0) {
                            $destination = substr($destination, strlen($removeprefix));
                        }

                        $dialstr = "$providertech/$ipaddress/$prefix$destination";
                        // $dialstr = 'SIP/24315';

                        // gerar os arquivos .call
                        $call = "Channel: " . $dialstr . "\n";
                        $call .= "Callerid: " . $MAGNUS->CallerID . "\n";
                        $call .= "Context: billing\n";
                        $call .= "Extension: " . $MAGNUS->destination . "\n";
                        $call .= "Priority: 1\n";
                        $call .= "Set:CALLED=" . $MAGNUS->destination . "\n";
                        $call .= "Set:TARRIFID=" . $Calc->tariffObj[0]['id_rate'] . "\n";
                        $call .= "Set:SELLCOST=" . $Calc->tariffObj[0]['rateinitial'] . "\n";
                        $call .= "Set:BUYCOST=" . $Calc->tariffObj[0]['buyrate'] . "\n";
                        $call .= "Set:CIDCALLBACK=1\n";
                        $call .= "Set:IDUSER=" . $MAGNUS->id_user . "\n";
                        $call .= "Set:IDPREFIX=" . $Calc->tariffObj[0]['id_prefix'] . "\n";
                        $call .= "Set:IDTRUNK=" . $Calc->tariffObj[0]['id_trunk'] . "\n";
                        $call .= "Set:IDPLAN=" . $MAGNUS->id_plan . "\n";
                        if (substr($MAGNUS->dnid, 0, 3) == '000') {
                            $call .= "Set:SECCALL=" . $MAGNUS->destination = substr($MAGNUS->dnid, 3) . "\n";
                        }
                        $agi->verbose($call);

                        $aleatorio    = str_replace(" ", "", microtime(true));
                        $arquivo_call = "/tmp/$aleatorio.call";
                        $fp           = fopen("$arquivo_call", "a+");
                        fwrite($fp, $call);
                        fclose($fp);

                        touch("$arquivo_call", mktime(date("H"), date("i"), date("s") + 1, date("m"), date("d"), date("Y")));
                        chown("$arquivo_call", "asterisk");
                        chgrp("$arquivo_call", "asterisk");
                        chmod("$arquivo_call", 0755);
                        exec("mv $arquivo_call /var/spool/asterisk/outgoing/$aleatorio.call");
                        $agi->answer();
                        $MAGNUS->hangup($agi);
                        exit;
                    }

                    $MAGNUS->hangup($agi);
                } else {
                    $agi->verbose("NO TARIFF FOUND");
                    $MAGNUS->hangup($agi);
                }
            } else {
                $MAGNUS->hangup($agi);
            }

        } else {
            $MAGNUS->hangup($agi);
        }

    }
}
