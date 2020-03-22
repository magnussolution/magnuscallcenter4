<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class CategoryController extends BaseController
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new Category;
        $this->abstractModel = Category::model();

        parent::init();
    }

    public function extraFilterCustomTeam($filter)
    {

        return $filter;
    }

    public function extraFilterCustom($filter)
    {
        $filter = !preg_match("/status/", $filter) ? ' status = 1 AND id > 0' : 'id > 0';

        if (Yii::app()->session['isOperator']) {
            $filter = $this->extraFilterCustomOperator($filter);
        } else if (Yii::app()->session['isTeam']) {
            $filter = $this->extraFilterCustomTeam($filter);
        }

        return $filter;
    }

    public function beforeSave($values)
    {
        if (isset($values['use_in_efetiva']) && $values['use_in_efetiva'] == 1) {
            $modelCaterogy = $this->abstractModel->find('use_in_efetiva = 1');
            if (count($modelCaterogy) > 0) {
                echo json_encode(array(
                    $this->nameSuccess => false,
                    $this->nameRoot    => array(),
                    'errors'           => 'You already have category to success call',
                ));
                exit;
            }
        }

        return $values;
    }

}
