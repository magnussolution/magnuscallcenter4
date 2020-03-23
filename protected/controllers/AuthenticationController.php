<?php
/**
 * Actions of module "Authentication".
 *
 * CallCenter <info@CallCenter.com>
 * 15/04/2013
 */

class AuthenticationController extends BaseController
{
    private $menu = array();

    public function actionLogin()
    {
        $user     = $_REQUEST['user'];
        $password = $_REQUEST['password'];

        if (isset($_REQUEST['remote'])) {
            $modelUser = User::model()->find("(username LIKE :user OR email LIKE :user) AND MD5(password) LIKE :password",
                array(
                    ':user'     => $user,
                    ':password' => $password,
                ));
        } else {

            $modelUser = User::model()->find("(username LIKE :user OR email LIKE :user) AND password LIKE :password",
                array(
                    ':user'     => $user,
                    ':password' => $password,
                ));

        }

        Yii::app()->session['fieldsAllow']          = array();
        Yii::app()->session['labelExtraFields']     = array();
        Yii::app()->session['labelExtraFieldstype'] = array();
        Yii::app()->session['campaign_name']        = '';

        if (!$modelUser) {
            Yii::app()->session['logged'] = false;
            echo json_encode(array(
                'success' => false,
                'msg'     => 'Username or password is wrong',
            ));
            MagnusLog::insertLOG(1, 'Username or password is wrong - User ' . $user);
            return;
        }

        if ($modelUser->status == 0) {
            Yii::app()->session['logged'] = false;
            echo json_encode(array(
                'success' => false,
                'msg'     => 'Username is disabled',
            ));
            MagnusLog::insertLOG(1, 'Username is disabled - User ' . $user);
            return;
        }

        $idUserType = $modelUser->idGroup->idUserType->id;

        Yii::log('idUserType' . $idUserType, 'error');

        Yii::app()->session['id_campaign'] = $modelUser->id_campaign;
        Yii::app()->session['isAdmin']     = $idUserType == 1 ? true : false;
        Yii::app()->session['isOperator']  = $idUserType == 2 ? true : false;
        Yii::app()->session['isTeam']      = $idUserType == 3 ? true : false;
        Yii::app()->session['username']    = $modelUser->username;
        Yii::app()->session['logged']      = true;
        Yii::app()->session['id_user']     = $modelUser->id;
        Yii::app()->session['name_user']   = $modelUser->name;
        Yii::app()->session['id_group']    = $modelUser->id_group;
        Yii::app()->session['user_type']   = $idUserType;
        Yii::app()->session['systemName']  = $_SERVER['SCRIPT_FILENAME'];
        Yii::app()->session['webphone']    = $modelUser->webphone;
        Yii::app()->session['id_team']     = $modelUser->id_team;
        Yii::app()->session['loggeds']     = User::model()->count('id_campaign > 0');
        Yii::app()->session['licence']     = $this->config['global']['licence'];

        if (Yii::app()->session['id_campaign'] > 0) {
            $this->fieldsAllow();

        } elseif (Yii::app()->session['isOperator']) {
            WorkShift::check();
            $msg = WorkShift::checkTime();
        }

        Yii::app()->session['updateAll'] = $this->config['global']['updateAll'];
        Yii::app()->session['email']     = $this->config['global']['admin_email'];

        $modelUserCount = User::model()->count(array('condition' => 'id >1'));

        Yii::app()->session['userCount'] = $modelUserCount;

        $modelUser->last_login = date('Y-m-d H:i:s');
        $modelUser->save();

        MagnusLog::insertLOG(1, 'Username Login on the panel - User ' . $modelUser->username);

        if (isset($_REQUEST['remote'])) {
            header("Location: ../..");
        }

        $msg = isset($msg) ? $msg : '';

        echo json_encode(array(
            'success' => Yii::app()->session['username'],
            'msg'     => Yii::app()->session['name_user'] . $msg,
        ));
    }

    private function mountMenu()
    {
        $modelGroupModule = GroupModule::model()->getGroupModule(Yii::app()->session['id_group'],
            Yii::app()->session['id_campaign'], Yii::app()->session['isOperator']);
        Yii::app()->session['action'] = $this->getActions($modelGroupModule);
        Yii::app()->session['menu']   = $this->getMenu($modelGroupModule);
    }

    private function getActions($modules)
    {
        $actions = array();

        foreach ($modules as $key => $value) {
            if (!empty($value['action'])) {
                $actions[$value['module']] = $value['action'];
            }
        }

        return $actions;
    }

    private function getMenu($modules)
    {
        $menu = array();

        foreach ($modules as $value) {

            //check if team have permission to view trunks
            if (Yii::app()->session['id_team'] > 1) {
                $modelTeamTrunk = TeamTrunk::model()->find('id_team = :key', array(':key' => Yii::app()->session['id_team']));
                if (count($modelTeamTrunk) && $value['id'] == '3') {
                    $value['show_menu'] = 0;
                }
            }

            if ($value['module'] != 'buycredit') {
                if (!$value['show_menu']) {
                    continue;
                }
            }

            if (empty($value['id_module'])) {
                array_push($menu, array(
                    'text'    => preg_replace("/ Module/", "", $value['text']),
                    'iconCls' => $value['icon_cls'],
                    'rows'    => $this->getSubMenu($modules, $value['id']),
                ));
            }
        }

        return $menu;
    }

    private function getSubMenu($modules, $idOwner)
    {
        $subModulesOwner = Util::arrayFindByProperty($modules, 'id_module', $idOwner);
        $subMenu         = array();

        foreach ($subModulesOwner as $value) {

            if ($value['module'] != 'buycredit') {
                if (!$value['show_menu']) {
                    continue;
                }
            }

            if (!empty($value['module'])) {

                $arraySubModule = array(
                    'text'             => $value['text'],
                    'iconCls'          => $value['icon_cls'],
                    'module'           => $value['module'],
                    'action'           => $value['action'],
                    'leaf'             => true,
                    'createShortCut'   => $value['createShortCut'],
                    'createQuickStart' => $value['createQuickStart'],
                );

                if ($value['module'] === 'phonenumber') {
                    $arraySubModule['fieldsAllow']          = Yii::app()->session['isOperator'] ? Yii::app()->session['fieldsAllow'] : array();
                    $arraySubModule['labelExtraFields']     = Yii::app()->session['isOperator'] ? Yii::app()->session['labelExtraFields'] : array();
                    $arraySubModule['labelExtraFieldsType'] = Yii::app()->session['isOperator'] ? Yii::app()->session['labelExtraFieldsType'] : array();
                }

                array_push($subMenu, $arraySubModule);

            } else {
                array_push($subMenu, array(
                    'text'    => $value['text'],
                    'iconCls' => $value['icon_cls'],
                    'rows'    => $this->getSubMenu($modules, $value['id']),
                ));
            }
        }

        return $subMenu;
    }

    public function fieldsAllow()
    {

        $fieldsAllow = array();

        if (Yii::app()->session['isOperator']) {
            //mostrar os campos permitidos
            $modelCampaign       = Campaign::model()->findByPk((int) Yii::app()->session['id_campaign']);
            $modelLoginsCampaign = LoginsCampaign::model()->find(
                "id_user = :id_user AND
                    stoptime = '0000-00-00 00:00:00' AND
                    login_type = 'PAUSE'",
                array(":id_user" => Yii::app()->session['id_user']));

            if (count($modelLoginsCampaign) == 1) {
                $pausetime                       = strtotime($modelLoginsCampaign->starttime);
                $stoptime                        = strtotime(date("Y-m-d H:i:s"));
                $totalTime                       = $stoptime - $pausetime;
                Yii::app()->session['pauseTime'] = $totalTime;
            } else {
                unset(Yii::app()->session['pauseTime']);
            }

            if (count($modelCampaign) > 0) {

                foreach ($modelCampaign as $field => $allow) {
                    if ($allow && strpos($field, 'allow_') !== false) {
                        array_push($fieldsAllow, str_replace('allow_', '', $field));
                    }

                }

                Yii::app()->session['fieldsAllow'] = $fieldsAllow;

                $labelExtraFields     = new stdClass;
                $labelExtraFieldsType = new stdClass;
                for ($i = 1; $i <= 8; $i++) {
                    if (in_array("option_$i", $fieldsAllow)) {
                        $property                    = "option_$i";
                        $labelExtraFields->$property = $modelCampaign->{'allow_option_' . $i};
                        if (isset($modelCampaign->{'allow_option_' . $i . '_type'})) {
                            $labelExtraFieldsType->$property = $modelCampaign->{'allow_option_' . $i . '_type'};
                        }

                    }
                }

                Yii::app()->session['labelExtraFields']     = $labelExtraFields;
                Yii::app()->session['labelExtraFieldsType'] = $labelExtraFieldsType;
            }
        }
    }

    public function actionLogoff()
    {

        MagnusLog::insertLOG(8, 'User logout - User ' . Yii::app()->session['username']);

        Yii::app()->session['logged']               = false;
        Yii::app()->session['id_user']              = false;
        Yii::app()->session['id_agent']             = false;
        Yii::app()->session['name_user']            = false;
        Yii::app()->session['menu']                 = array();
        Yii::app()->session['action']               = array();
        Yii::app()->session['currency']             = false;
        Yii::app()->session['language']             = false;
        Yii::app()->session['isAdmin']              = true;
        Yii::app()->session['isOperator']           = false;
        Yii::app()->session['isTeam']               = false;
        Yii::app()->session['id_plan']              = false;
        Yii::app()->session['credit']               = false;
        Yii::app()->session['username']             = false;
        Yii::app()->session['id_group']             = false;
        Yii::app()->session['user_type']            = false;
        Yii::app()->session['licence']              = false;
        Yii::app()->session['phonebookID']          = false;
        Yii::app()->session['id_campaign']          = false;
        Yii::app()->session['licence']              = false;
        Yii::app()->session['email']                = false;
        Yii::app()->session['userCount']            = false;
        Yii::app()->session['webphone']             = false;
        Yii::app()->session['updateAll']            = false;
        Yii::app()->session['noticeSignupActually'] = false;
        Yii::app()->session['noticeSignupNext']     = false;
        Yii::app()->session['loggeds']              = false;

        Yii::app()->session->destroy();

        echo json_encode(array(
            'success' => true,
        ));
    }

    public function actionCheck()
    {
        if (Yii::app()->session['isOperator']) {
            WorkShift::check();
            WorkShift::checkTime();
        }
        if (Yii::app()->session['logged']) {

            $this->mountMenu();

            if (isset(Yii::app()->session['id_campaign']) && Yii::app()->session['id_campaign'] > 0) {

                $modelCampaign = Campaign::model()->findAll(array(
                    'select'    => 'c.id_phonebook, t.name, t.open_url',
                    'join'      => "JOIN pkg_campaign_phonebook c ON t.id = c.id_campaign",
                    'condition' => "c.id_campaign = " . Yii::app()->session['id_campaign'],
                    'limit'     => 1,
                ));
                Yii::app()->session['campaign_open_url'] = isset($modelCampaign[0]->open_url)
                ? $modelCampaign[0]->open_url : null;
                Yii::app()->session['campaign_name'] = isset($modelCampaign[0]->name)
                ? $modelCampaign[0]->name : Yii::t('yii', 'This campaign not have phonebook. Please add one');
                Yii::app()->session['phonebookID'] = isset($modelCampaign[0]->id_phonebook)
                ? $modelCampaign[0]->id_phonebook : null;

                $this->fieldsAllow();

            } else {
                unset(Yii::app()->session['pauseTime']);
            }

            $modelGroupModule = GroupModule::model()->getGroupModule(Yii::app()->session['id_group'],
                Yii::app()->session['id_campaign'], Yii::app()->session['isOperator']);

            Yii::app()->session['action']  = $this->getActions($modelGroupModule);
            Yii::app()->session['menu']    = $this->getMenu($modelGroupModule);
            Yii::app()->session['loggeds'] = User::model()->count('id_campaign > 0');
            $id_user                       = Yii::app()->session['id_user'];
            $id_agent                      = Yii::app()->session['id_agent'];
            $nameUser                      = Yii::app()->session['name_user'];
            $logged                        = Yii::app()->session['logged'];
            $menu                          = Yii::app()->session['menu'];
            $currency                      = Yii::app()->session['currency'];
            $language                      = Yii::app()->session['language'];
            $isAdmin                       = Yii::app()->session['isAdmin'];
            $isOperator                    = Yii::app()->session['isOperator'];
            $isTeam                        = Yii::app()->session['isTeam'];
            $id_plan                       = Yii::app()->session['id_plan'];
            $credit                        = Yii::app()->session['credit'];
            $username                      = Yii::app()->session['username'];
            $id_group                      = Yii::app()->session['id_group'];
            $user_type                     = Yii::app()->session['user_type'];
            $licence                       = Yii::app()->session['licence'];
            $phonebookID                   = Yii::app()->session['phonebookID'];
            $id_campaign                   = Yii::app()->session['id_campaign'];
            $campaign_name                 = Yii::app()->session['campaign_name'];
            $licence                       = Yii::app()->session['licence'];
            $email                         = Yii::app()->session['email'];
            $webphone                      = Yii::app()->session['webphone'];
            $userCount                     = Yii::app()->session['userCount'];
            $updateAll                     = Yii::app()->session['updateAll'];
            $campaign_open_url             = Yii::app()->session['campaign_open_url'];
            $loggeds                       = Yii::app()->session['loggeds'];

        } else {
            $id_user           = false;
            $id_agent          = false;
            $nameUser          = false;
            $logged            = false;
            $menu              = array();
            $currency          = false;
            $language          = false;
            $isAdmin           = false;
            $isOperator        = false;
            $isTeam            = false;
            $id_plan           = false;
            $credit            = false;
            $username          = false;
            $id_group          = false;
            $user_type         = false;
            $licence           = false;
            $phonebookID       = false;
            $id_campaign       = false;
            $licence           = false;
            $email             = false;
            $userCount         = false;
            $campaign_name     = false;
            $webphone          = false;
            $updateAll         = false;
            $campaign_open_url = false;
            $loggeds           = false;
        }

        $language = isset(Yii::app()->session['language']) ? Yii::app()->session['language'] : Yii::app()->sourceLanguage;
        $theme    = isset(Yii::app()->session['theme']) ? Yii::app()->session['theme'] : 'blue-neptune';

        echo json_encode(array(
            'id'                   => $id_user,
            'id_agent'             => $id_agent,
            'name'                 => $nameUser,
            'success'              => $logged,
            'menu'                 => $menu,
            'language'             => $language,
            'theme'                => $theme,
            'currency'             => $currency,
            'language'             => $language,
            'isAdmin'              => $isAdmin,
            'isOperator'           => $isOperator,
            'isTeam'               => $isTeam,
            'id_plan'              => $id_plan,
            'credit'               => $credit,
            'username'             => $username,
            'id_group'             => $id_group,
            'user_type'            => $user_type,
            'licence'              => $licence,
            'phonebookID'          => $phonebookID,
            'id_campaign'          => $id_campaign,
            'licence'              => $licence,
            'email'                => $email,
            'userCount'            => $userCount,
            'updateAll'            => $updateAll,
            'webphone'             => $webphone,
            'campaign_name'        => $campaign_name,
            'campaign_open_url'    => $campaign_open_url,
            'logo'                 => file_exists('resources/images/logo_custom') ? 'resources/images/logo_custom' : 'resources/images/logo',
            'pause'                => isset(Yii::app()->session['pauseTime']) ? Yii::app()->session['pauseTime'] : null,
            'noticeSignupActually' => isset(Yii::app()->session['noticeSignupActually']) ? Yii::app()->session['noticeSignupActually'] : false,
            'noticeSignupNext'     => isset(Yii::app()->session['noticeSignupNext']) ? Yii::app()->session['noticeSignupNext'] : false,
            'loggeds'              => $loggeds,

        ));
    }

    public function actionChangePassword()
    {
        $passwordChanged = false;
        $id_user         = Yii::app()->session['id_user'];
        $currentPassword = $_POST['current_password'];
        $newPassword     = $_POST['password'];
        $isOperator      = Yii::app()->session['isOperator'];
        $errors          = '';

        $moduleUser = User::model()->find("id LIKE :id_user AND password LIKE :currentPassword",
            array(
                ":id_user"         => $id_user,
                ":currentPassword" => $currentPassword,
            ));

        if (count($moduleUser) > 0) {
            try
            {
                $moduleUser->password = $_POST['password'];
                $passwordChanged      = $moduleUser->save();
            } catch (Exception $e) {
                $errors = $this->getErrorMySql($e);
            }

            $msg = $passwordChanged ? yii::t('yii', 'Password change success!') : $errors;
        } else {
            $msg = yii::t('yii', 'Current Password incorrect.');
        }

        echo json_encode(array(
            'success' => $passwordChanged,
            'msg'     => $msg,
        ));
    }

    public function actionImportLogo()
    {
        if (isset($_FILES['logo']['tmp_name']) && strlen($_FILES['logo']['tmp_name']) > 3) {

            $uploaddir  = "resources/images/";
            $typefile   = explode('.', $_FILES["logo"]["name"]);
            $uploadfile = $uploaddir . 'logo_custom.png';
            move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadfile);
        }

        echo json_encode(array(
            'success' => true,
            'msg'     => 'Refresh the system to see the new logo',
        ));
    }

    public function actionImportWallpapers()
    {
        if (isset($_FILES['logo']['tmp_name']) && strlen($_FILES['logo']['tmp_name']) > 3) {

            $uploaddir  = "resources/images/wallpapers/";
            $typefile   = explode('.', $_FILES["logo"]["name"]);
            $uploadfile = $uploaddir . 'Customization.jpg';
            move_uploaded_file($_FILES["logo"]["tmp_name"], $uploadfile);
        }

        $modelConfiguration               = Configuration::model()->find("config_key LIKE 'wallpaper'");
        $modelConfiguration->config_value = 'Customization';
        try {
            $success = $modelConfiguration->save();
            $msg     = Yii::t('yii', 'Refresh the system to see the new logo');
        } catch (Exception $e) {
            $success = false;
            $msg     = $this->getErrorMySql($e);
        }
        echo json_encode(array(
            'success' => $success,
            'msg'     => $msg,
        ));

    }

    public function actionGetAgentStatus()
    {
        if (!isset($_GET['agent']) || !isset($_GET['password'])) {
            exit;
        } else {
            $modelUser = User::model()->find('username = :key AND password = :key1', array(':key' => $_GET['agent'], ':key1' => $_GET['password']));
            if (isset($modelUser->id)) {

                if ($modelUser->id_campaign < 1) {
                    echo json_encode([
                        'result' => false,
                        'status' => 'Sem campanha',
                        'msg'    => 'Agente nao esta logado em nenhuma campanha',
                    ]);
                    exit;
                }

                $modelPhonenumber = PhoneNumber::model()->findByPk($modelUser->id_current_phonenumber);
                if (isset($modelPhonenumber->id)) {

                    $modelOpegadorStatus = OperatorStatus::model()->find('id_user = :key', array(':key' => $modelUser->id));
                    if (isset($modelOpegadorStatus->id)) {
                        $status = $modelOpegadorStatus->in_call == 0 ? 'Sem chamada' : 'Chamando';
                        if ($modelOpegadorStatus->categorizing == 1) {
                            $status = 'Categorizando';
                        }
                    } else {
                        $status = 'Sem chamada';
                    }

                    echo json_encode([
                        'result' => true,
                        'status' => $status,
                        'rows'   => $modelPhonenumber->getAttributes(),
                    ]);
                } else {
                    echo json_encode([
                        'result' => false,
                        'status' => 'sem chamadas',
                        'msg'    => 'Agente nao esta em chamada',
                    ]);
                    exit;
                }

            }
        }
    }

}
