<?php
/**
 * Acoes do modulo "UserCdrStatus".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class UserCdrStatusController extends BaseController
{
    public $attributeOrder = 'starttime DESC';
    public $extraValues    = array('idUser' => 'username', 'idCategory' => 'name');
    public $join           = 'INNER JOIN pkg_phonenumber ON calledstation = number
        JOIN pkg_category ON t.id_category = pkg_category.id';
    public $group  = 'id_user';
    public $select = 't.id, DATE(starttime) AS day, id_user, count(status) AS status';
    public function init()
    {
        $this->primaryKey    = UserCdrStatus::model()->primaryKey();
        $this->instanceModel = new UserCdrStatus;
        $this->abstractModel = UserCdrStatus::model();
        $this->titleReport   = Yii::t('yii', 'Cdr User');

        /*Aplica filtro padrao por data e causa de temrinao*/
        $filter         = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
        $filter         = $this->createCondition(json_decode($filter));
        $whereStarttime = !preg_match("/starttime/", $filter) ? ' AND starttime > "' . date('Y-m-d') . '"' : false;
        $whereStatus    = !preg_match("/status/", $filter) ? ' AND status = 11' : false;
        $this->filter   = $whereStarttime . $whereStatus;

        parent::init();
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item) {
            $attributes[$key]           = $item->attributes;
            $attributes[$key]['status'] = $item->status;
            $attributes[$key]['day']    = $item->day;

            if (isset($_SESSION['isOperator']) && $_SESSION['isOperator']) {
                foreach ($this->fieldsInvisibleOperator as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if ($_SESSION['isOperator']) {
                        foreach ($this->fieldsInvisibleOperator as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }

    public function filterReplace($filter)
    {
        return $filter;
    }
}
