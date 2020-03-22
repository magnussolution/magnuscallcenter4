<?php
/**
 * Acoes do modulo "UserOnline".
 *
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class UserOnlineController extends Controller
{
    public $attributeOrder = 'starttime DESC';
    public $extraValues    = array('idUser' => 'username', 'idCampaign' => 'name');
    public $join           = 'JOIN pkg_user ON pkg_user.id = t.id_user 
                            JOIN pkg_campaign ON pkg_campaign.id = t.id_campaign';


    public $fieldsFkReport = array(
        'id_user' => array(
            'table' => 'pkg_user',
            'pk' => 'id',
            'fieldReport' => "CONCAT(username, ' ', name) "
        ),
        'id_campaign' => array(
            'table' => 'pkg_campaign',
            'pk' => 'id',
            'fieldReport' => 'name'
        )
    );

    public function init()
    {
        $this->instanceModel = new UserOnline;
        $this->abstractModel = UserOnline::model();
        $this->titleReport   = Yii::t('yii','Login Users');

        parent::init();
    
    }

}