<?php
/**
 * Acoes do modulo "CampaignPoll".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class CampaignPollController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idCampaign' => 'name');

    public function init()
    {
        $this->instanceModel = new CampaignPoll;
        $this->abstractModel = CampaignPoll::model();
        $this->titleReport   = Yii::t('yii', 'Poll');
        parent::init();
    }

}
