<?php
/**
 * Acoes do modulo "CampaignPreditive".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class CampaignPredictiveController extends BaseController
{

    public $attributeOrder = 't.id';
    public function init()
    {
        parent::init();
    }

    public function actionRead($asJson = true, $condition = null)
    {

        if (Yii::app()->session['id_team'] > 1) {
            $resultCampaign = Campaign::model()->findAll('predictive = 1 AND id_team = :key ', array(':key' => Yii::app()->session['id_team']));
        } else {
            $resultCampaign = Campaign::model()->findAll('predictive = 1');
        }
        $results = array();
        foreach ($resultCampaign as $key => $campaign) {

            $result                           = Campaign::model()->campaignPredictive($campaign);
            $result[0]['asr']                 = number_format($result[0]['asr'], 2) . " %";
            $result[0]['ring_delay']          = number_format($result[0]['ring_delay'], 0);
            $result[0]['answered_call_ratio'] = number_format($result[0]['answered_call_ratio'], 0);
            $results[]                        = $result[0];

        }

        echo json_encode(array(
            $this->nameRoot  => $results,
            $this->nameCount => count($results),
            $this->nameSum   => false,
        ));

    }

}
