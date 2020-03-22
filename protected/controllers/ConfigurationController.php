<?php
/**
 * Acoes do modulo "Configuration".
 *
 * =======================================
 * ###################################
 * CallCenter
 *
 * @package    CallCenter
 * @author    Adilson Leffa Magnus.
 * @copyright    Todos os direitos reservados.
 * ###################################
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class ConfigurationController extends BaseController
{
    public $attributeOrder = 'config_group_title DESC';
    public $defaultFilter  = 'status =1';
    public $filterByUser   = false;

    public function init()
    {

        $this->instanceModel = new Configuration;
        $this->abstractModel = Configuration::model();
        $this->titleReport   = Yii::t('yii', 'Config');
        parent::init();
    }
    public function actionLayout()
    {

        $model         = Configuration::model()->findByAttributes(array('config_key' => 'layout'));
        $model->status = $_POST['status'];
        if ($_POST['status'] == 0) {
            $model->config_value = 0;
        }
        $model->save();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg     => '',
        ));
    }

    public function actionTheme()
    {
        $model               = Configuration::model()->findByAttributes(array('config_key' => $_POST['field']));
        $model->config_value = $_POST['value'];
        $model->save();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg     => '',
        ));
    }
}
