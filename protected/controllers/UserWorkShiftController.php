<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class UserWorkShiftController extends BaseController
{
    public $attributeOrder = 't.id';

    public $extraValues = array('idUser' => 'username,name', 'idWorkShift' => 'day,turno');

    public function init()
    {
        $this->instanceModel = new UserWorkShift;
        $this->abstractModel = UserWorkShift::model();

        parent::init();
    }

}
