<?php
/**
 * Acoes do modulo "CdrSummary".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class CdrSummaryController extends BaseController
{
    public $attributeOrder = 'day DESC';
    public $limit          = 7;
    public $group          = 'day';
    public $select         = 't.id,
            DATE(starttime) AS day,
            sum(sessiontime) /60 AS sessiontime,
            count(*) as nbcall,
            SUM(case when sessiontime>0 then 1 else 0 end) as success_calls, starttime,
            SUM(CASE WHEN id_category = 11 then 1 else 0 end) as categoriacion_completa,
            SUM(sessiontime) / COUNT(*) AS aloc_all_calls,
            SUM(sessiontime) / SUM(CASE WHEN sessiontime > 0  THEN 1 ELSE 0 END) AS aloc_success_calls
            ';
    public $join = ' JOIN pkg_category c ON t.id_category = c.id
                JOIN pkg_campaign ON t.id_campaign = pkg_campaign.id
                JOIN pkg_phonebook ON t.id_phonebook = pkg_phonebook.id';

    public function init()
    {
        $this->instanceModel = new CdrSummary;
        $this->abstractModel = CdrSummary::model();
        $this->titleReport   = Yii::t('yii', 'Calls Summary');
        parent::init();
    }

    public function extraFilterCustomTeam($filter)
    {

        $sql = "SELECT id FROM pkg_campaign WHERE id_team = " . Yii::app()->session['id_team'];

        $filter .= ' AND t.id_campaign IN ( ' . $sql . ')';

        return $filter;
    }

    public function actionCsv()
    {
        # recebe os parametros para o filtro
        $filter   = isset($_POST['filter']) ? $_POST['filter'] : null;
        $filterIn = isset($_POST['filterIn']) ? $_POST['filterIn'] : null;

        if ($filter && $filterIn) {
            $filter = array_merge($filter, $filterIn);
        } else if ($filterIn) {
            $filter = $filterIn;
        }

        $filter = strlen($filter) > 2 ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

        $this->filter = $filter = $this->extraFilter($filter);

        $limit = strlen($filter) > 2 && preg_match("/starttime/", $filter) ? 30 : $this->limit;

        //nao permite mais de 31 registros
        $limit                       = $limit > 31 ? $limit                       = 31 : $limit;
        $_GET[$this->nameParamLimit] = $limit;

        $sort    = $this->attributeOrder;
        $columns = 'day,sunsessiontime,nbcall,aloc_all_calls,success_calls,categoriacion_completa,aloc_success_calls';

        $select = 'DATE(starttime) AS day,
        sum(sessiontime) /60 AS sunsessiontime,
        count(*) as nbcall,
            SUM(case when sessiontime>0 then 1 else 0 end) as success_calls,
            SUM(CASE WHEN id_category = 11 then 1 else 0 end) as categoriacion_completa,
            SUM(sessiontime) / COUNT(*) AS aloc_all_calls,
            SUM(sessiontime) / SUM(CASE WHEN sessiontime > 0  THEN 1 ELSE 0 END) AS aloc_success_calls';

        $records = $this->abstractModel->findAll(array(
            'select'    => $select,
            'join'      => $this->join,
            'condition' => $filter,
            'order'     => $sort,
            'limit'     => $limit,
            'group'     => $this->group,
        ));
        $records = $this->getAttributesModels($records);

        $pathCsv = $this->magnusFilesDirectory . $this->nameFileReport . '.csv';
        if (!is_dir($this->magnusFilesDirectory)) {
            mkdir($this->magnusFilesDirectory, 777, true);
        }

        $fileOpen = fopen($pathCsv, 'w');

        foreach ($records as $fields) {
            $fieldsCsv = array();

            foreach ($fields as $key => $value) {

                if (array_search($key, explode(',', $columns)) !== false) {

                    if ($key == 'sunsessiontime' || $key == 'aloc_all_calls' || $key == 'aloc_success_calls') {
                        $seconds = $value;
                        $hours   = floor($seconds / 3600);
                        $seconds -= $hours * 3600;
                        $minutes = floor($seconds / 60);
                        $seconds -= $minutes * 60;
                        $value = "$hours:$minutes:" . number_format($seconds);
                    }

                    if ($key == 'day') {
                        $value = explode(" ", $value);
                        $value = $value[0];
                    }

                    array_push($fieldsCsv, $value);
                }

            }

            fputcsv($fileOpen, $fieldsCsv);
        }

        fclose($fileOpen);
        header('Content-type: application/csv');
        header('Content-Disposition: inline; filename="' . $pathCsv . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        ob_clean();
        flush();
        if (readfile($pathCsv)) {
            unlink($pathCsv);
        }
    }

    public function actionRead($asJson = true, $condition = null)
    {
        # recebe os parametros para o filtro
        $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
        $filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

        $limit = strlen($filter) > 2 && preg_match("/starttime/", $filter) ? $_GET[$this->nameParamLimit] : $this->limit;

        //nao permite mais de 31 registros
        $limit                       = $limit > 31 ? $limit                       = 31 : $limit;
        $_GET[$this->nameParamLimit] = $limit;

        parent::actionRead($asJson = true, $condition = null);

    }

    public function setAttributesModels($attributes, $models)
    {

        for ($i = 0; $i < count($attributes) && is_array($attributes); $i++) {
            $attributes[$i]['day']                    = $models[$i]->day;
            $attributes[$i]['sunsessiontime']         = $models[$i]->sunsessiontime;
            $attributes[$i]['aloc_all_calls']         = $models[$i]->aloc_all_calls;
            $attributes[$i]['aloc_success_calls']     = $models[$i]->aloc_success_calls;
            $attributes[$i]['nbcall']                 = $models[$i]->nbcall;
            $attributes[$i]['success_calls']          = $models[$i]->success_calls;
            $attributes[$i]['categoriacion_completa'] = $models[$i]->categoriacion_completa;
        }
        return $attributes;
    }

}
