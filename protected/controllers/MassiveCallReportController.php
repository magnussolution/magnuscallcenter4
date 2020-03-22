<?php
/**
 * Acoes do modulo "Cdr".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class MassiveCallReportController extends BaseController
{
    public $attributeOrder = 'id DESC';
    public $extraValues    = array('idUser' => 'username');
    public $defaultFilter  = 'status > 1';
    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => "CONCAT(username, ' ', name) ",
        ),
    );

    public function init()
    {
        $this->instanceModel = new MassiveCallPhoneNumber;
        $this->abstractModel = MassiveCallPhoneNumber::model();
        $this->titleReport   = Yii::t('yii', 'Massive Call report');

        parent::init();
    }

}
