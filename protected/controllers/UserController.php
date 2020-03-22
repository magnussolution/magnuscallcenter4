<?php
/**
 * Actions of module "User".
 *
 * CallCenter <info@CallCenter.com>
 * 15/04/2013
 */

class UserController extends BaseController
{
    public $attributeOrder = 'id';
    public $titleReport    = 'User';

    public $nameModelRelated   = 'UserCampaign';
    public $nameFkRelated      = 'id_user';
    public $nameOtherFkRelated = 'id_campaign';

    public $extraValues = array('idGroup' => 'name,id_user_type', 'idTeam' => 'name');

    public $fieldsFkReport = array(
        'id_group'    => array(
            'table'       => 'pkg_group_user',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
        'id_campaign' => array(
            'table'       => 'pkg_campaign',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
        'id_team'     => array(
            'table'       => 'pkg_team',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );
    public function init()
    {
        $this->instanceModel        = new User;
        $this->abstractModel        = User::model();
        $this->abstractModelRelated = UserCampaign::model();

        $filter       = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
        $filter       = $this->createCondition(json_decode($filter));
        $this->filter = !preg_match("/status/", $filter) ? ' AND status = 1' : false;
        parent::init();
    }

    public function checkTeamEdit($values)
    {
        //not allow agent edit his account.
        if (Yii::app()->session['isTeam'] && !$this->isNewRecord && Yii::app()->session['id_user'] == $values['id']) {
            echo json_encode(array(
                'success' => false,
                'rows'    => array(),
                'errors'  => Yii::t('yii', 'You cannot EDIT your account.'),
            ));
            exit();

        }
    }

    public function beforeSave($values)
    {
        if (Yii::app()->session['isTeam']) {
            $this->checkTeamEdit($values);

            $values['id_team'] = Yii::app()->session['id_team'];

            $modelGroupUser     = GroupUser::model()->find('id_user_type = 2');
            $values['id_group'] = $modelGroupUser->id;
        }
        return $values;
    }
    public function afterSave($modelUser, $values)
    {

        if ($modelUser->idGroup->idUserType->id == 2) {

            $modelSip = $this->isNewRecord ?
            new Sip :
            Sip::model()->find('id_user =' . $modelUser->id);
            $modelSip->id_user     = $modelUser->id;
            $modelSip->accountcode = $modelSip->name = $modelSip->fromuser = $modelSip->defaultuser = $modelUser->username;
            $modelSip->allow       = 'g729,gsm,alaw,ulaw';
            $modelSip->host        = 'dynamic';
            $modelSip->insecure    = 'no';
            $modelSip->secret      = $modelUser->password;
            $modelSip->context     = 'magnuscallcenter';
            $modelSip->save();

            AsteriskAccess::generatePJSipPeers();
        }
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::generatePJSipPeers();
    }

}
