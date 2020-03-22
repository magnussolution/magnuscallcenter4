<?php
/**
 * Acoes do modulo "Sip".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/06/2012
 */

class SipController extends BaseController
{

    public $extraValues = array('idUser' => 'username');
    public function init()
    {
        $this->instanceModel = new Sip;
        $this->abstractModel = Sip::model();
        parent::init();
    }

    public function afterSave($model)
    {
        PeerFileHandler::generateSipFile();
    }
    public function afterDestroy($values)
    {
        PeerFileHandler::generateSipFile();
    }
}
