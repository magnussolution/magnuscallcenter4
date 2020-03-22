<?php
/**
 * Acoes do modulo "UserOnline".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class LoginsCampaignController extends BaseController
{
    public $attributeOrder = 'starttime DESC';
    public $extraValues    = array('idUser' => 'username', 'idCampaign' => 'name');
    public $join           = 'JOIN pkg_user ON pkg_user.id = t.id_user
                            JOIN pkg_campaign ON pkg_campaign.id = t.id_campaign';

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
        $this->instanceModel = new LoginsCampaign;
        $this->abstractModel = LoginsCampaign::model();
        $this->titleReport   = Yii::t('yii', 'Logins Campaign');

        parent::init();
    }

    public function extraFilterCustomTeam($filter)
    {

        $sql = "SELECT id FROM pkg_campaign WHERE id_team = " . Yii::app()->session['id_team'];

        $filter .= ' AND t.id_campaign IN ( ' . $sql . ')';

        return $filter;
    }

}
