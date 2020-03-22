<?php
/**
 * Acoes do modulo "CdrSummary".
 *
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class CdrSummaryByUserController extends Controller
{
    public $attributeOrder  = 't.id_user DESC';
    public $group           = 't.id_user';
    public $select          = 't.id, id_user, sum(sessiontime) /60 AS sessiontime, count(*) as nbcall,
             sum(case when sessiontime>0 then 1 else 0 end) as success_calls, starttime,
             SUM(CASE WHEN id_category = 3 then 1 else 0 end) as categoriacion_completa,
             SUM(sessiontime) / COUNT(*) AS aloc_all_calls,
            SUM(sessiontime) / SUM(CASE WHEN sessiontime > 0  THEN 1 ELSE 0 END) AS aloc_success_calls, c.username AS idUserusername';
    

    public $join  = 'JOIN pkg_user c ON t.id_user = c.id                                
                            JOIN pkg_campaign ON t.id_campaign = pkg_campaign.id
                            JOIN pkg_phonebook ON t.id_phonebook = pkg_phonebook.id';


    public function init()
    {
        $this->instanceModel = new CdrSummaryByUser;
        $this->abstractModel = CdrSummaryByUser::model();
        $this->titleReport   = Yii::t('yii','Calls Summary by operator');

        $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null; 
        $filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter; 

        $this->filter  = !preg_match("/starttime/", $filter) ? ' AND starttime > "'.date('Y-m-d').'"' : false;

        parent::init();
    }



    public function actionRead()
    {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null; 
        $group = isset($this->group) ? $this->group : 1;
        $limit = (strlen($filter) < 2 && isset($this->limit)) ?  $this->limit : $_GET[$this->nameParamLimit];       



       parent::actionRead();

    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        foreach ($models as $key => $item)
        {          

            $attributes[$key]                              = $item->attributes;
            $attributes[$key]['idUserusername']           = $item->idUserusername;
            $attributes[$key]['sunsessiontime']            = $item->sunsessiontime;

            $attributes[$key]['aloc_all_calls']            = $item->aloc_all_calls;
            $attributes[$key]['aloc_success_calls']        = $item->aloc_success_calls;
            $attributes[$key]['nbcall']                    = $item->nbcall;
            $attributes[$key]['success_calls']             = $item->success_calls;            
            $attributes[$key]['categoriacion_completa']    = $item->categoriacion_completa;            
            
            if ($item->id_user) 
            {
                $result = UserOnline::model()->findAll(array(
                    'select' => 'SUM(total_time) as total_time',
                    'condition' => "id_user = ".$item->id_user." AND starttime >  '".date('Y-m-d')."'"
                ));

                 $attributes[$key]['total_time']                = $result[0]->total_time;
            }

            

            if(isset($_SESSION['isOperator']) && $_SESSION['isOperator'])
            {
                foreach ($this->fieldsInvisibleOperator as $field) {
                    unset($attributes[$key][$field]);
                }
            }


            foreach($itemsExtras as $relation => $fields)
            {
                $arrFields = explode(',', $fields);
                foreach($arrFields as $field)
                {
                    $attributes[$key][$relation . $field] = $item->$relation->$field;
                    if($_SESSION['isOperator']) {
                        foreach ($this->fieldsInvisibleOperator as $field) {
                            unset($attributes[$key][$field]);
                        }
                    }
                }
            }
        }

        return $attributes;
    }

}