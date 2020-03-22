<?php
/**
 * Acoes do modulo "Billing".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class BillingController extends BaseController
{
    public $attributeOrder = 't.date DESC, t.id_campaign , t.turno';
    public $filterByUser   = false;
    public $extraValues    = array(
        'idUser'     => 'username,name',
        'idCampaign' => 'name',
    );
    public $join = 'JOIN pkg_user c ON t.id_user = c.id
		JOIN pkg_campaign d ON t.id_campaign = d.id';

    public $promedio = array();

    public $fieldsFkReport = array(
        'id_user'     => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => "CONCAT(username, ' ', name) ",
        ),
        'id_campaign' => array(
            'table'       => 'pkg_campaign',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );

    public function init()
    {
        $this->instanceModel = new Billing;
        $this->abstractModel = Billing::model();

        parent::init();
    }

    public function removeColumns($columns)
    {

        unset($columns[2]);
        return $columns;
    }

}
