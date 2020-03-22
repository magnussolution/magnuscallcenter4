<?php
/**
 * Acoes do modulo "Provider".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/06/2012
 */

class ProviderController extends BaseController
{
    public $attributeOrder = 'id';

    public function init()
    {
        $this->instanceModel = new Provider;
        $this->abstractModel = Provider::model();
        $this->titleReport   = Yii::t('yii', 'Provider');
        parent::init();
    }
}
