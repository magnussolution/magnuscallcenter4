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

class MassiveCall
{
    public function processCall($agi, &$MAGNUS, &$Calc)
    {
        $agi->answer();
        $now      = time();
        $audioDir = $MAGNUS->magnusFilesDirectory . 'sounds/';

        if ($MAGNUS->dnid == 'failed' || !is_numeric($MAGNUS->dnid)) {
            $agi->verbose("Hangup becouse dnid is OutgoingSpoolFailed", 25);
            $MAGNUS->hangup($agi);
        }

        $id_phonenumber = $agi->get_variable("PHONENUMBER_ID", true);
        $id_campaign    = $agi->get_variable("CAMPAIGN_ID", true);

        $agi->verbose('MASSIVE CALL' . $id_campaign . ' - ' . $MAGNUS->dnid, 1);

        $modelMassiveCallCampaign = MassiveCallCampaign::model()->findByPk((int) $id_campaign);

        $agi->verbose($modelMassiveCallCampaign->id_campaign);

        /*AUDIO FOR CAMPAIN*/
        $audio = $audioDir . "idMassiveCallCampaign_" . $id_campaign;

        $agi->verbose('MASSIVE CALL' . $audio, 5);

        //se tiver audio 2, executar o audio 1 sem esperar DTMF
        if (strlen($modelMassiveCallCampaign->audio_2) > 1) {
            $agi->verbose('Execute audio 1. No DTMF');
            $agi->stream_file($audio, ' #');
        } else {
            $agi->verbose('Execute audio 1 DTMF');
            $res_dtmf = $agi->get_data($audio, 5000, 1);
        }

        if (strlen($modelMassiveCallCampaign->audio_2) > 1) {
            /*Execute audio 2*/
            $audio = $audioDir . "idMassiveCallCampaign_" . $id_campaign . "_2";

            $res_dtmf = $agi->get_data($audio, 5000, 1);

        }

        $agi->verbose('RESULT DTMF ' . $res_dtmf['result'], 25);

        if (strlen($modelMassiveCallCampaign->audio) < 5) {
            $res_dtmf['result'] = $modelMassiveCallCampaign->forward_number;
            $agi->verbose('CAMPAIN SEM AUDIO, ENVIA DIRETO PARA ' . $res_dtmf['result']);
        }

        $agi->verbose("have Forward number $forward_number", 5);
        MassiveCallPhoneNumber::model()->updateByPk($id_phonenumber, array('status' => 3, 'res_dtmf' => $res_dtmf['result'], 'queue_status' => 'CLIENT_ANSWER_CALL'));

        $agi->set_variable("CALLERID(num)", $destination);

        if ($modelMassiveCallCampaign->forward_number == '-1' || $res_dtmf['result'] == $modelMassiveCallCampaign->forward_number) {
            $agi->verbose("cleinte tranferido para queue $modelMassiveCallCampaign->id_campaign");
            Queue::queueMassivaCall($agi, $MAGNUS, $Calc, $modelMassiveCallCampaign, $id_phonenumber);
        } else {
            $MANGUS->hangup($agi);
        }

    }

}
