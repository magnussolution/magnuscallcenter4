<?php
/**
 * Acoes do modulo "PhoneBook".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class MassiveCallPhoneBookController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idTrunk' => 'trunkcode');

    public $join;

    public $fieldsInvisibleAgent = array(
        'id_trunk',
        'idTrunktrunkcode',
    );

    public function init()
    {

        $this->instanceModel = new MassiveCallPhoneBook;
        $this->abstractModel = MassiveCallPhoneBook::model();
        $this->titleReport   = Yii::t('yii', 'Massive Call PhoneBook');

        parent::init();
    }

}
