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

class Queue
{
    public function callQueue($agi, &$MAGNUS, &$Calc, $result_did, $type = 'queue', $startTime = 0)
    {
        $agi->verbose("Queue module", 5);
        $agi->answer();
        $startTime = $startTime == 0 ? time() : $startTime;

        $MAGNUS->destination = $MAGNUS->dnid = $result_did[0]['did'];

        $agi->verbose(print_r($result_did, true));

        $sql = "SELECT * FROM pkg_campaign WHERE id =" . $result_did[0]['id_campaign'];
        $agi->verbose($sql);
        $campaignResult = Yii::app()->db->createCommand($sql)->queryAll();

        $nowtime = date('H:s');

        if ($nowtime > $campaignResult[0]['daily_morning_start_time'] &&
            $nowtime < $campaignResult[0]['daily_morning_stop_time']) {
            //echo "turno manha";
        } elseif ($nowtime > $campaignResult[0]['daily_afternoon_start_time'] &&
            $nowtime < $campaignResult[0]['daily_afternoon_stop_time']) {
            //echo "Turno Tarde";
        } else {
            $agi->verbose(' Campanha fora de turno' . $campaignResult[0]['name']);
            $MAGNUS->hangup();
        }

        $sql = "SELECT * FROM pkg_campaign_phonebook WHERE id_campaign = " . $campaignResult[0]['id'];
        $agi->verbose($sql);
        $resultPhoneBook = Yii::app()->db->createCommand($sql)->queryAll();

        if (count($resultPhoneBook) < 1) {
            $agi->verbose(' Campanha sem agenda: ' . $campaignResult[0]['name']);
            $MAGNUS->hangup();
        }

        $sql = "SELECT * FROM pkg_phonenumber WHERE number = '" . $MAGNUS->CallerID . "' AND
                    id_phonebook IN (SELECT id_phonebook FROM pkg_campaign_phonebook WHERE
                    id_campaign = " . $campaignResult[0]['id'] . ")";
        $agi->verbose($sql);
        $resultPhoneNumber = Yii::app()->db->createCommand($sql)->queryAll();

        if (count($resultPhoneNumber) > 0) {
            $idPhoneNumber = $resultPhoneNumber[0]['id'];
        } else {

            $sql = "INSERT INTO pkg_phonenumber (id_phonebook, number, status, id_category)
                        VALUES (:id_phonebook, :number, 1, 0)";
            $command = Yii::app()->db->createCommand($sql);
            $command->bindValue(":id_phonebook", $resultPhoneBook[0]['id_phonebook'], PDO::PARAM_INT);
            $command->bindValue(":number", $MAGNUS->CallerID, PDO::PARAM_STR);
            $command->execute();
            $idPhoneNumber = Yii::app()->db->lastInsertID;
        }
        $aleatorio = str_replace(" ", "", microtime(true));

        $sql = "INSERT INTO pkg_predictive (id_campaign, uniqueid, number) VALUES (" . $campaignResult[0]['id'] . ", '" . $MAGNUS->uniqueid . "', '" . $idPhoneNumber . "')";
        $agi->verbose($sql);
        Yii::app()->db->createCommand($sql)->execute();

        //salvamos os dados da chamada gerada
        $sql = "INSERT INTO pkg_predictive_gen (date, uniqueID,id_phonebook,ringing_time) VALUES ('" . time() . "', " . $MAGNUS->uniqueid . ", " . $resultPhoneBook[0]['id_phonebook'] . ",0)";
        $agi->verbose($sql);
        Yii::app()->db->createCommand($sql)->execute();

        $agi->set_variable("CALLERID(num)", $MAGNUS->CallerID);
        $agi->set_callerid($MAGNUS->CallerID);

        $agi->verbose('Receptivo - Send call to Campaign ' . $campaignResult[0]['name'], 5);
        //SET uniqueid para ser atualizado a tabela pkg_predictive quando a ligação for atendida
        $agi->set_variable("UNIQUEID", $MAGNUS->uniqueid);

        $agi->set_variable("CALLERID", $MAGNUS->CallerID);
        $agi->set_variable("CALLED", $MAGNUS->CallerID);
        $agi->set_variable("PHONENUMBER_ID", $idPhoneNumber);
        $agi->set_variable("IDPHONEBOOK", $resultPhoneBook[0]['id_phonebook']);
        $agi->set_variable("CAMPAIGN_ID", $campaignResult[0]['id']);
        $agi->set_variable("STARTCALL", time());
        $agi->set_variable("ALEARORIO", $aleatorio);

        $max_wait_time = $campaignResult[0]['max_wait_time'] > 0 ? $campaignResult[0]['max_wait_time'] : '';

        $agi->execute("Queue", $campaignResult[0]['name'] . ',tc,,,' . $max_wait_time . ',/var/www/html/callcenter/agi.php');
        if ($MAGNUS->agiconfig['record_call'] == 1 || $MAGNUS->record_call == 1) {
            $myres = $agi->execute("StopMixMonitor");
            $agi->verbose("EXEC StopMixMonitor (" . $MAGNUS->uniqueid . ")", 5);
            if (file_exists("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm")) {
                if (!is_dir("" . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY'))) {
                    exec("mkdir " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY'));
                }
                $agi->verbose("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

                exec("mv " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/" . $MAGNUS->dnid . "." . $MAGNUS->uniqueid . ".gsm " . $MAGNUS->config['global']['record_patch'] . "/" . date('dmY') . "/");

            }
        }

        $linha = exec(" egrep $MAGNUS->uniqueid /var/log/asterisk/queue_log | tail -1");
        $linha = explode('|', $linha);

        $agi->verbose(print_r($linha, true), 1);

        $linha[4] = isset($linha[4]) ? $linha[4] : '';

        if ($linha[4] == 'EXITWITHTIMEOUT') {
            if (strlen($campaignResult[0]['max_wait_time_action'])) {

                $data        = explode('/', strtoupper($campaignResult[0]['max_wait_time_action']));
                $actionType  = $data[0];
                $destination = $data[1];
                switch ($actionType) {
                    case 'PJSIP':

                        $dialstr = $campaignResult[0]['max_wait_time_action'];
                        $agi->verbose("DIAL $dialstr", 6);
                        $MAGNUS->run_dial($agi, $dialstr);
                        break;
                    case 'QUEUE':
                        $sql                          = "SELECT * FROM campaign WHERE UPPER(name) = '" . strtoupper($destination) . "' LIMIT 1";
                        $modelCampaign                = Yii::app()->db->createCommand($sql)->queryAll();
                        $result_did[0]['id_campaign'] = $modelCampaign[0]['id'];

                        Queue::callQueue($agi, $MAGNUS, $Calc, $result_did, 'queue', $startTime);
                        $noCDR = true;
                        break;
                    case 'IVR':
                        $sql = "SELECT * FROM pkg_ivr WHERE UPPER(name) = '" . strtoupper($destination) . "' LIMIT 1";

                        $modelIrv = Yii::app()->db->createCommand($sql)->queryAll();

                        $result_did[0]['id_ivr'] = $modelIrv[0]['id'];
                        IvrAgi::callIvr($agi, $MAGNUS, $Calc, $result_did, 'queue', $startTime);
                        break;
                }
            }
        }

        $agi->verbose(date("Y-m-d H:i:s") . " => $MAGNUS->dnid, " . $MAGNUS->uniqueid . " DELIGOU A CHAMADAS teste teste", 1);

        $endTime = time();

        $Calc->answeredtime = $Calc->real_answeredtime = $endTime - $startTime;

        //pega o usuario que atendeu a chamada

        $sql        = "SELECT id FROM pkg_user WHERE username = (SELECT operador FROM pkg_predictive WHERE uniqueid = '" . $MAGNUS->uniqueid . "')";
        $userResult = Yii::app()->db->createCommand($sql)->queryAll();
        $agi->verbose($sql, 25);

        $MAGNUS->id_user = $userResult[0]['id'];
        $agi->verbose(print_r($trunk, true) . ' ' . $MAGNUS->channel);
        $sql         = "SELECT id FROM pkg_trunk WHERE trunkcode LIKE '" . $trunk[0] . "'";
        $trunkResult = Yii::app()->db->createCommand($sql)->queryAll();

        $Calc->usedtrunk                          = is_numeric($trunkResult[0]['id']) ? $trunkResult[0]['id'] : null;
        $Calc->tariffObj[0]['id_campaign_number'] = $campaignResult[0]['id'];

        $Calc->tariffObj[0]['id_phonebook'] = $resultPhoneBook[0]['id_phonebook'];
        $Calc->tariffObj[0]['id']           = $idPhoneNumber;

        //chamada nao foi atendida pela queue, colocar id na categoria.
        if (preg_match('/ABANDON|EXITEMPTY|EXITWITHTIMEOUT|QUEUESTART/', $linha[4]) || $linha[4] == '') {
            $MAGNUS->forceIdCaterory = 3;
            $linha[4]                = 'NOANSWER';
        }

        $terminatecauseid = $Calc->answeredtime > 0 ? 1 : 0;
        $Calc->updateSystem($MAGNUS, $agi, $MAGNUS->CallerID . '-' . $linha[4], $terminatecauseid);
    }

    public function queueMassivaCall($agi, &$MAGNUS, &$Calc, $modelCampaign, $idPhoneNumber)
    {
        $agi->verbose("Queue module", 5);
        $agi->answer();
        $startTime = time();

        $startTime = strtotime("now");
        $agi->set_variable("CALLERID(num)", $agi->get_variable("CALLED", true));
        $agi->set_callerid($agi->get_variable("CALLED", true));

        $modelCampainForward = Campaign::model()->findByPk($modelCampaign->id_campaign);

        $modelCampaignPhonebook = CampaignPhonebook::model()->find('id_campaign = :key',
            array(':key' => $modelCampainForward->id));

        $agi->verbose('id_phonebook=' . $modelCampaignPhonebook->id_phonebook);

        $agi->verbose('Receptivo - Send call to Campaign ' . $modelCampainForward->name, 5);
        //SET uniqueid para ser atualizado a tabela pkg_predictive quando a ligação for atendida
        $agi->set_variable("UNIQUEID", $MAGNUS->uniqueid);

        $agi->set_variable("CALLERID", $MAGNUS->dnid);
        $agi->set_variable("CALLED", $MAGNUS->dnid);

        $agi->set_variable("CAMPAIGN_ID", $modelCampainForward->id);
        $agi->set_variable("STARTCALL", time());
        $agi->set_variable("ALEARORIO", $aleatorio);
        $agi->verbose("Find number data in massivecallphone id = " . $agi->get_variable("PHONENUMBER_ID", true) . " \n\n\n\n\n");

        $modelMassiveCallPhoneNumber = MassiveCallPhoneNumber::model()->findByPk((int) $agi->get_variable("PHONENUMBER_ID", true));

        $agi->verbose('id_phonebook=' . $modelCampaignPhonebook->id_phonebook);

        $agi->verbose('modelMassiveCallPhoneNumberID=' . $modelMassiveCallPhoneNumber->id);

        $modelPhoneNumber                           = new PhoneNumber();
        $modelPhoneNumber->id_phonebook             = $modelCampaignPhonebook->id_phonebook;
        $modelPhoneNumber->id_category              = 0;
        $modelPhoneNumber->status                   = 1;
        $modelPhoneNumber->number                   = $modelMassiveCallPhoneNumber->number;
        $modelPhoneNumber->name                     = $modelMassiveCallPhoneNumber->name;
        $modelPhoneNumber->email                    = $modelMassiveCallPhoneNumber->email;
        $modelPhoneNumber->info                     = $modelMassiveCallPhoneNumber->info;
        $modelPhoneNumber->city                     = $modelMassiveCallPhoneNumber->city;
        $modelPhoneNumber->address                  = $modelMassiveCallPhoneNumber->address;
        $modelPhoneNumber->state                    = $modelMassiveCallPhoneNumber->state;
        $modelPhoneNumber->country                  = $modelMassiveCallPhoneNumber->country;
        $modelPhoneNumber->dni                      = $modelMassiveCallPhoneNumber->dni;
        $modelPhoneNumber->mobile                   = $modelMassiveCallPhoneNumber->mobile;
        $modelPhoneNumber->number_home              = $modelMassiveCallPhoneNumber->number_home;
        $modelPhoneNumber->number_office            = $modelMassiveCallPhoneNumber->number_office;
        $modelPhoneNumber->zip_code                 = $modelMassiveCallPhoneNumber->zip_code;
        $modelPhoneNumber->company                  = $modelMassiveCallPhoneNumber->company;
        $modelPhoneNumber->birth_date               = $modelMassiveCallPhoneNumber->birth_date;
        $modelPhoneNumber->type_user                = $modelMassiveCallPhoneNumber->type_user;
        $modelPhoneNumber->sexo                     = $modelMassiveCallPhoneNumber->sexo;
        $modelPhoneNumber->edad                     = $modelMassiveCallPhoneNumber->edad;
        $modelPhoneNumber->profesion                = $modelMassiveCallPhoneNumber->profesion;
        $modelPhoneNumber->mobile_2                 = $modelMassiveCallPhoneNumber->mobile_2;
        $modelPhoneNumber->beneficio_number         = $modelMassiveCallPhoneNumber->beneficio_number;
        $modelPhoneNumber->quantidade_transacoes    = $modelMassiveCallPhoneNumber->quantidade_transacoes;
        $modelPhoneNumber->inicio_beneficio         = $modelMassiveCallPhoneNumber->inicio_beneficio;
        $modelPhoneNumber->beneficio_valor          = $modelMassiveCallPhoneNumber->beneficio_valor;
        $modelPhoneNumber->banco                    = $modelMassiveCallPhoneNumber->banco;
        $modelPhoneNumber->agencia                  = $modelMassiveCallPhoneNumber->agencia;
        $modelPhoneNumber->conta                    = $modelMassiveCallPhoneNumber->conta;
        $modelPhoneNumber->address_complement       = $modelMassiveCallPhoneNumber->address_complement;
        $modelPhoneNumber->telefone_fixo1           = $modelMassiveCallPhoneNumber->telefone_fixo1;
        $modelPhoneNumber->telefone_fixo2           = $modelMassiveCallPhoneNumber->telefone_fixo2;
        $modelPhoneNumber->telefone_fixo3           = $modelMassiveCallPhoneNumber->telefone_fixo3;
        $modelPhoneNumber->telefone_celular1        = $modelMassiveCallPhoneNumber->telefone_celular1;
        $modelPhoneNumber->telefone_celular2        = $modelMassiveCallPhoneNumber->telefone_celular2;
        $modelPhoneNumber->telefone_celular3        = $modelMassiveCallPhoneNumber->telefone_celular3;
        $modelPhoneNumber->telefone_fixo_comercial1 = $modelMassiveCallPhoneNumber->telefone_fixo_comercial1;
        $modelPhoneNumber->telefone_fixo_comercial2 = $modelMassiveCallPhoneNumber->telefone_fixo_comercial2;
        $modelPhoneNumber->telefone_fixo_comercial3 = $modelMassiveCallPhoneNumber->telefone_fixo_comercial3;
        $modelPhoneNumber->parente1                 = $modelMassiveCallPhoneNumber->parente1;
        $modelPhoneNumber->fone_parente1            = $modelMassiveCallPhoneNumber->fone_parente1;
        $modelPhoneNumber->parente2                 = $modelMassiveCallPhoneNumber->parente2;
        $modelPhoneNumber->fone_parente2            = $modelMassiveCallPhoneNumber->fone_parente2;
        $modelPhoneNumber->parente3                 = $modelMassiveCallPhoneNumber->parente3;
        $modelPhoneNumber->fone_parente3            = $modelMassiveCallPhoneNumber->fone_parente3;
        $modelPhoneNumber->vizinho1                 = $modelMassiveCallPhoneNumber->vizinho1;
        $modelPhoneNumber->telefone_vizinho1        = $modelMassiveCallPhoneNumber->telefone_vizinho1;
        $modelPhoneNumber->vizinho2                 = $modelMassiveCallPhoneNumber->vizinho2;
        $modelPhoneNumber->telefone_vizinho2        = $modelMassiveCallPhoneNumber->telefone_vizinho2;
        $modelPhoneNumber->vizinho3                 = $modelMassiveCallPhoneNumber->vizinho3;
        $modelPhoneNumber->telefone_vizinho3        = $modelMassiveCallPhoneNumber->telefone_vizinho3;
        $modelPhoneNumber->email2                   = $modelMassiveCallPhoneNumber->email2;
        $modelPhoneNumber->email3                   = $modelMassiveCallPhoneNumber->email3;

        try {
            $modelPhoneNumber->save();
        } catch (Exception $e) {
            $agi->verbose(print_r($e, true));
        }
        $agi->set_variable("PHONENUMBER_ID", $modelPhoneNumber->id);
        $agi->set_variable("IDPHONEBOOK", $modelCampaignPhonebook->id_phonebook);
        $agi->set_variable("IDPHONENUMBERMASSIVE", $modelMassiveCallPhoneNumber->id);

        $agi->verbose('=========================== MASSIVE CALL ADD number ' . $modelMassiveCallPhoneNumber->number . ' to campaign=' . $modelCampaign->id_campaign . ' added id_phonenumber=' . $modelPhoneNumber->id, 1);

        $agi->execute("Queue", $campaignResult[0]['name'] . ',,,,60,/var/www/html/callcenter/agi.php');

        $linha = exec(" egrep $MAGNUS->uniqueid /var/log/asterisk/queue_log | tail -1");
        $linha = explode('|', $linha);

        $agi->verbose(print_r($linha, true), 1);

        MassiveCallPhoneNumber::model()->updateByPk($modelMassiveCallPhoneNumber->id, array('queue_status' => $linha[4]));
    }
}
