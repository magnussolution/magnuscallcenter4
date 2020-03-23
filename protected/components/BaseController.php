<?php
/**
 * Default Controll.
 *
 * MagnusCallCenter <info@magnussolution.com>
 * 11/05/2017
 */
class BaseController extends CController
{
    public $success = true;
    public $msg;
    public $select = '*';
    public $join;
    public $nameRoot               = 'rows';
    public $nameCount              = 'count';
    public $nameSuccess            = 'success';
    public $nameMsg                = 'msg';
    public $nameMsgErrors          = 'errors';
    public $nameParamStart         = 'start';
    public $nameParamLimit         = 'limit';
    public $nameParamSort          = 'sort';
    public $nameParamDir           = 'dir';
    public $msgSuccess             = 'Operation successful.';
    public $msgSuccessLot          = 'Records updated successfully';
    public $msgRecordNotFound      = 'Record not found.';
    public $msgRecordAlreadyExists = 'Record already exists.';
    public $defaultFilter          = '1';
    public $fieldsFkReport;
    public $fieldsCurrencyReport;
    public $fieldsPercentReport;
    public $rendererReport;
    public $abstractModel;
    public $instanceModel;
    public $abstractModelRelated;
    public $nameModelRelated;
    public $nameFkRelated;
    public $nameOtherFkRelated;
    public $extraFieldsRelated = array();
    public $titleReport;
    public $subTitleReport;
    public $attributes     = array();
    public $extraValues    = array();
    public $mapErrorsMySql = array(
        1451 => 'Record to be deleted is related to another. Technical information: ',
        1452 => 'Record to be related not found: <br>Technical Information:',
        0    => 'Technical Information: ',
    );
    public $isNewRecord;
    public $isUpdateAll;
    public $filter;
    public $limit;
    public $group = 1;
    public $msgError;
    public $filterByUser        = true;
    public $defaultFilterByUser = 'id_user';
    public $is_ratecard_view;
    public $fieldCard;
    public $fieldsInvisibleOperator = array();
    public $fieldsInvisibleAgent    = array();
    public $nameSum                 = 'sum';
    public $recordsSum              = array();
    public $saveAttributes          = false;
    public $magnusFilesDirectory    = '/usr/local/src/magnus/';
    public $nameFileReport          = 'export';
    public $order;
    public $start;
    public $sort;
    public $modelName;
    public $actionName;
    public $controllerName;
    public $homeUrl;
    public $defaultSortDir = null;
    public $fixedWhere     = null;
    public $paramsFilter;
    public $nofilterPerAdminGroup = array();
    public $relationFilter        = array();
    public $fieldsInvisibleClient = array();
    public $config;
    public function init()
    {
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/resources/init.css');

        SqlInject::sanitize($_REQUEST);
        $this->config = LoadConfig::getConfig();

        $this->controllerName = Yii::app()->controller->id;

        if (isset($_POST['ws'])) {
            $this->actionLogin();
        }

        if (!Yii::app()->session['id_user']) {
            if (!$this->authorizedNoSession()) {
                exit('');
            }

        }

        $this->modelName  = isset($this->abstractModel) ? get_class($this->abstractModel) : '';
        $this->homeUrl    = Yii::app()->getHomeUrl();
        $this->actionName = $this->getCurrentAction();

        if ($this->getOverrideModel()) {
            $model = explode("/", $this->controllerName);
            $model = ucfirst(isset($model[1]) ? $model[1] : $model[0] . 'OR');
            Yii::import('application.models.overrides.' . $model);
            $this->instanceModel = new $model;
            $this->abstractModel = $model->model($model);
        }

        if ($this->getOverride()) {
            $this->paramsToSession();
            $this->redirect(array('overrides/' . $this->controllerName . 'OR/' . $this->actionName));
        }

        $this->getSessionParams();

        $this->subTitleReport         = Yii::t('yii', 'report');
        $this->msgSuccess             = Yii::t('yii', 'Operation was successful.');
        $this->msgSuccessLot          = Yii::t('yii', 'Records updated with success.');
        $this->msgRecordNotFound      = Yii::t('yii', 'Record not found.');
        $this->msgRecordAlreadyExists = Yii::t('yii', 'Record already exists.');
        $this->msgError               = Yii::t('yii', 'Disallowed action');
        $this->mapErrorsMySql         = array(
            1451 => Yii::t('yii', 'Record to be deleted is related to another. Technical information: '),
            1452 => Yii::t('yii', 'Record to be listed there. Technical information: '),
            0    => Yii::t('yii', 'Technical information: '),
        );
        $startSession = strlen(session_id()) < 1 ? session_start() : null;

        if (!isset(Yii::app()->session['language'])) {

            Yii::app()->session['language'] = $this->config['global']['base_language'];
            Yii::app()->language            = Yii::app()->sourceLanguage            = isset(Yii::app()->session['language'])
            ? Yii::app()->session['language']
            : Yii::app()->language;
        }

        parent::init();
    }

    public function authorizedNoSession()
    {
        $allow = array(
            'site',
            'authentication',
        );
        return in_array($this->controllerName, $allow);
    }
    private function getOverride()
    {
        if (!file_exists('protected/config/overrides.php')) {
            return false;
        }

        include_once 'protected/config/overrides.php';
        return isset($GLOBALS['overrides']['controllers'][$this->controllerName])
        && in_array($this->actionName, $GLOBALS['overrides']['controllers'][$this->controllerName]);
    }

    private function getOverrideModel()
    {
        if (!file_exists('protected/config/overrides.php')) {
            return false;
        }

        include_once 'protected/config/overrides.php';
        $uri = explode("/", Yii::app()->getRequest()->getPathInfo());

        $module_name = preg_match("/overrides/", Yii::app()->getRequest()->getPathInfo())
        ? substr($uri[1], 0, -2) : $uri[0];
        return in_array(ucfirst($module_name), $GLOBALS['overrides']['models']);
    }

    private function paramsToSession()
    {
        Yii::app()->session['paramsGet']  = isset($_GET) ? json_encode($_GET) : null;
        Yii::app()->session['paramsPost'] = isset($_POST) ? json_encode($_POST) : null;

    }

    private function getCurrentAction()
    {
        $uri = explode("/", Yii::app()->getRequest()->getPathInfo());
        return isset($uri[1]) ? $uri[1] : null;
    }

    private function getSessionParams()
    {
        if (isset(Yii::app()->session['paramsGet']) && Yii::app()->session['paramsGet'] != null) {
            $_GET = (array) json_decode(Yii::app()->session['paramsGet']);
        }

        if (isset(Yii::app()->session['paramsPost']) && Yii::app()->session['paramsPost'] != null) {
            $_POST = (array) json_decode(Yii::app()->session['paramsPost']);
        }

        Yii::app()->session['paramsGet'] = Yii::app()->session['paramsPost'] = null;
    }

    public function setStart($value)
    {
        $this->start = isset($value[$this->nameParamStart]) ? $value[$this->nameParamStart] : -1;
    }

    public function setGroup($value)
    {
        if (isset($_GET['group'])) {
            $this->group                = json_decode($_GET['group']);
            $_GET[$this->nameParamSort] = $this->group->property;

            $_GET[$this->nameParamDir] = $this->group->direction;
        }
    }

    public function setLimit($value)
    {
        $limit       = isset($value[$this->nameParamLimit]) ? $value[$this->nameParamLimit] : -1;
        $this->limit = (strlen($this->filter) < 2 && isset($this->limit)) ? $this->limit : $limit;
    }

    public function setSort()
    {
        $this->sort = isset($_GET[$this->nameParamSort]) ? $_GET[$this->nameParamSort] : $this->attributeOrder;
    }

    public function setOrder()
    {
        $dir         = isset($_GET[$this->nameParamDir]) ? ' ' . $_GET[$this->nameParamDir] : null;
        $this->order = !$dir || (strstr($this->sort, ',') !== false)
        ? $this->sort
        : ($this->sort ? $this->sort . ' ' . $dir : null);

        return $this->replaceOrder();
    }

    public function setfilter($value)
    {
        # recebe os parametros para o filtro
        $filter   = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;
        $filterIn = isset($_GET['filterIn']) ? json_decode($_GET['filterIn']) : null;

        if ($filter && $filterIn) {
            $filter = array_merge($filter, $filterIn);
        } else if ($filterIn) {
            $filter = $filterIn;
        }

        $filter       = $filter ? $this->createCondition($filter) : $this->defaultFilter;
        $this->filter = $this->fixedWhere ? $filter . ' ' . $this->fixedWhere : $filter;
        $this->filter = $this->extraFilter($filter);
    }

    public function readModel()
    {
        if (isset($_GET['log2'])) {

            echo '$this->filter = ';
            print_r($this->filter);
            echo "<br><br>";
            echo '$this->paramsFilter = ';
            print_r($this->paramsFilter);
            echo "<br><br>";
            echo '$this->relationFilter = ';
            print_r($this->relationFilter);
            echo "<br><br>";
            print_r($this->limit);
            echo "<br><br>";
            print_r($this->start);
            echo "<br><br>";
            exit;
        }

        return new CDbCriteria(array(
            'select'    => $this->select,
            'join'      => $this->join,
            'condition' => $this->filter,
            'params'    => $this->paramsFilter,
            'with'      => $this->relationFilter,
            'order'     => $this->order,
            'limit'     => $this->limit,
            'offset'    => $this->start,
            'group'     => $this->group,
        ));

    }

    public function readCountRecord()
    {
        if (strlen($this->group) < 2) {
            $count = $this->abstractModel->count(array(
                'join'      => $this->join,
                'condition' => $this->filter,
                'with'      => $this->relationFilter,
                'params'    => $this->paramsFilter,
            ));
        } else {
            $recordCont = $this->abstractModel->findAll(array(
                'select'    => $this->select,
                'join'      => $this->join,
                'condition' => $this->filter,
                'with'      => $this->relationFilter,
                'params'    => $this->paramsFilter,
                'order'     => $this->order,
                'group'     => $this->group,
            ));
            $count = count($recordCont);
        }

        return $count;
    }
    /**
     * Lista os registros da model
     */
    public function actionRead($asJson = true, $condition = null)
    {
        //$this->checkActionAccess(array(), $this->instanceModel->getModule(),'canRead');

        $this->beforeRead($_GET);

        $this->setLimit($_GET);

        $this->setStart($_GET);

        $this->setGroup($_GET);

        $this->setSort();

        $this->setOrder();

        if (!$condition) {
            $this->setfilter($_GET);
        } else {
            $this->filter = $condition;
        }

        $this->applyFilterToLimitedAdmin();
        $this->showAdminLog();

        $records = $this->abstractModel->findAll($this->readModel());

        $countRecords = $this->readCountRecord();

        $recordsSum = $this->recordsExtraSum($records);

        $this->afterRead($records);

        $return[$this->nameRoot]  = $records;
        $return[$this->nameCount] = $countRecords;

        if (!$asJson) {
            $return                   = array();
            $return[$this->nameRoot]  = $this->getAttributesModels($records, $this->extraValues);
            $return[$this->nameCount] = $countRecords;
            $return[$this->nameSum]   = $this->getAttributesModels($recordsSum);
            return $return;
        } else {
            echo json_encode(array(
                $this->nameRoot  => $this->getAttributesModels($records, $this->extraValues),
                $this->nameCount => $countRecords,
                $this->nameSum   => $this->getAttributesModels($recordsSum),
            ));
        }
    }

    private function showAdminLog()
    {
        if (Yii::app()->session['isAdmin'] == true && isset($_GET['log'])) {
            echo '<pre>';
            print_r($this->paramsFilter);

            echo $sql = "SELECT $this->select FROM  " . $this->abstractModel->tableName() . " t $this->join WHERE $this->filter GROUP BY $this->group LIMIT $this->limit";
            try {
                $command = Yii::app()->db->createCommand($sql);
                if (count($this->paramsFilter)) {
                    foreach ($this->paramsFilter as $key => $value) {
                        $command->bindValue($key, $value, PDO::PARAM_STR);
                    }

                }

                $teste = $command->queryAll();
                print_r($teste);
            } catch (Exception $e) {

                print_r($e);
            }
        }
    }

    public function beforeRead($values)
    {
        return;
    }

    public function afterRead($records)
    {
        return;
    }
    public function hidenInvisibleField($values)
    {
        if (isset(Yii::app()->session['isTeam']) && Yii::app()->session['isTeam']) {
            foreach ($this->fieldsInvisibleOperator as $field) {
                unset($values[$field]);
            }
        }
    }

    public function checkActionAccess($values = '', $module, $action)
    {
        if ($action == 'canUpdate' || $action == 'canCreate') {
            if (isset($values['id']) && !AccessManager::getInstance($module)->$action() && $values['id'] != 0) {
                header('HTTP/1.0 401 Unauthorized');
                die("Access denied to $action in module: $module");
            }
        } elseif ($action == 'canRead') {
            if (!AccessManager::getInstance($module)->canRead()) {
                header('HTTP/1.0 401 Unauthorized');
                die("Access denied to $action in module:" . $module);
            }
        }
    }

    public function applyFilterToLimitedAdmin()
    {
        if (!in_array($this->controllerName, $this->nofilterPerAdminGroup)
            && Yii::app()->session['user_type'] == 1 && Yii::app()->session['adminLimitUsers'] == true) {

            if ($this->controllerName != 'user' && !preg_match("/pkg_user/", $this->join)) {
                $this->join .= ' JOIN pkg_user b ON t.id_user = b.id';
                $defaultFilterByUser = 'b.' . $this->defaultFilterByUser;
            } else {
                $defaultFilterByUser = $this->defaultFilterByUser;
            }

            $filterByGroup = preg_replace("/id_user/", 'id_group', $defaultFilterByUser);
            $this->filter .= " AND $filterByGroup IN (SELECT gug.id_group FROM pkg_group_user_group gug WHERE gug.id_group_user = :idgA0 )";
            $this->paramsFilter['idgA0'] = Yii::app()->session['id_group'];
        }

    }

    /**
     * Cria/Atualiza um registro da model
     */
    public function actionSave()
    {
        $values = $this->getAttributesRequest();

        $namePk = $this->abstractModel->primaryKey();

        $module = $this->instanceModel->getModule();

        $this->isNewRecord = !isset($values[$namePk]) || (is_array($values[$namePk]) || $values[$namePk] > 0)
        ? false : true;

        $this->isUpdateAll = !$this->isNewRecord
        && isset($values[$namePk])
        && is_array($values[$namePk])
        || (isset($_POST['filter']) && strlen($_POST['filter']) > 0)
        ? true : false;

        if (!$this->isUpdateAll) {
            $values = $this->beforeSave($values);
        }

        $this->checkActionAccess($values, $module, $this->isNewRecord == true ? 'canUpdate' : 'canCreate');

        $this->hidenInvisibleField($values);

        $subRecords = isset($values[$this->nameOtherFkRelated]) ? $values[$this->nameOtherFkRelated] : false;

        unset($values[$this->nameOtherFkRelated]);

        //updateAll
        if (isset($values[$namePk]) && is_array($values[$namePk])) {
            $ids = array();
            $ids = $values[$namePk];
        } elseif (isset($_POST['filter']) && strlen($_POST['filter']) > 0) {
            $ids             = array();
            $ids             = $this->updateGetIdsFromFilter($namePk, $_POST['filter']);
            $values[$namePk] = $ids;
        }

        if (isset($ids)) {
            $this->saveUpdateAll($ids, $values, $module, $namePk, $subRecords);
            return;
        }
        //end updateAll

        $id    = $values[$namePk];
        $model = $id ? $this->loadModel($id, $this->abstractModel) : $this->instanceModel;

        $model->attributes = $values;

        try {
            $this->success = $model->save();
            $errors        = $model->getErrors();

            if (!count($errors)) {
                $id = $id ? $id : $model->$namePk;
                if ($subRecords !== false) {
                    $this->saveRelated($id, $subRecords);
                }
                $this->saveGetNewRecord($namePk, $id);
            }

        } catch (Exception $e) {
            $this->success = false;
            $errors        = $this->getErrorMySql($e);
        }

        if ($this->success) {
            //insert in log table
            MagnusLog::insertLOG($id && $id > 0 ? 2 : 4, 'Module: ' . $module . '  ' . json_encode($values));
        } else {
            $this->nameMsg = $this->nameMsgErrors;
        }

        $this->msg = $this->success ? $this->msgSuccess : $errors;

        if (!$this->isUpdateAll) {
            $this->afterSave($model, $values);
        }

        # retorna o resultado da execucao
        echo json_encode(array(
            $this->nameSuccess => $this->success,
            $this->nameRoot    => $this->attributes,
            $this->nameMsg     => $this->msg,
        ));
    }

    public function beforeSave($values)
    {
        if (Yii::app()->session['isOperator']) {
            $values['id_user'] = Yii::app()->session['id_user'];
        } else if (Yii::app()->session['isTeam']) {
            $values['id_team'] = Yii::app()->session['id_team'];
        }
        return $values;
    }

    public function beforeUpdateAll($values, $ids)
    {
        return $values;
    }

    public function afterSave($model, $values)
    {
        return;
    }

    public function afterUpdateAll($strIds)
    {
        return;
    }
    public function saveUpdateAll($ids, $values, $module, $namePk, $subRecords)
    {

        if (Yii::app()->session['isTeam']) {
            $info = 'No admin user trying UPDATEALL';
            MagnusLog::insertLOG(6, $info);
            exit;
        }
        $values = $this->beforeUpdateAll($values, $ids);

        try {
            unset($values[$namePk]);
            unset($values['password']);
            $setters = array();
            foreach ($values as $fieldName => $value) {
                if (isset($value['isPercent']) && is_bool($value['isPercent'])) {
                    $v            = $value['value'];
                    $percent      = $v / 100;
                    $valuePercent = $value['isPercent'] ? "($fieldName * $percent)" : $v;

                    if ($value['isAdd']) {
                        $valueUpdate = new CDbExpression("$fieldName + $valuePercent");
                    } else if ($value['isRemove']) {
                        $valueUpdate = new CDbExpression("$fieldName - $valuePercent");
                    } else {
                        $valueUpdate = new CDbExpression($valuePercent);
                    }
                } else {

                    if ($fieldName == 'allow' && is_array($value)) {
                        $value = implode(',', $value);
                    }

                    $valueUpdate = (gettype($value) == 'integer') ? $value : "$value";
                }

                $setters[$fieldName] = $valueUpdate;
            }

            $this->abstractModel->setScenario('update');
            $this->abstractModel->setIsNewRecord(false);

            $criteria = new CDbCriteria();
            $criteria->addInCondition($namePk, $ids);
            try {
                $this->success = true;
                $this->abstractModel->updateAll($setters, $criteria);
            } catch (Exception $e) {
                print_r($e);
                exit;
            }

            $this->msg = $this->msgSuccessLot;
            $info      = addslashes("Module: $this->modelName SET " . print_r($setters, true) . " WHERE $namePk IN(" . print_r($ids, true) . ")");
            MagnusLog::insertLOG(6, $info);

            $this->afterUpdateAll($ids);

        } catch (Exception $e) {
            $this->success = false;
            $this->msg     = $this->getErrorMySql($e);
        }

        # retorna o resultado da execucao
        echo json_encode(array(
            $this->nameSuccess => $this->success,
            $this->nameMsg     => $this->msg,
        ));

        if (array_key_exists('subRecords', $values)) {
            $this->saveRelated($values);
        }
    }

    public function updateGetIdsFromFilter($namePk, $filter)
    {
        $ids    = array();
        $filter = $this->createCondition(json_decode($_POST['filter']));

        $this->filter = $filter = $this->extraFilter($filter);

        //integra o filtro lookup no updateall
        if (isset($_POST['defaultFilter'])) {

            $defaultFilter = $_POST['defaultFilter'];
            $defaultFilter = $this->createCondition(json_decode($defaultFilter));
            $this->filter .= ' AND ' . $defaultFilter;
        }

        $records = $this->abstractModel->findAll(array(
            'join'      => $this->join,
            'condition' => $this->filter,
            'with'      => $this->relationFilter,
            'params'    => $this->paramsFilter,
        ));

        foreach ($records as $record) {
            array_push($ids, $record[$namePk]);
        }

        return $ids;
    }

    public function saveGetNewRecord($namePk, $id, $filter = '')
    {
        $newRecord = $this->abstractModel->findAll(array(
            'select'    => $this->select,
            'join'      => $this->join,
            'condition' => "t.$namePk = $id $filter",
            'with'      => $this->relationFilter,
        ));
        $this->attributes = $this->getAttributesModels($newRecord, $this->extraValues);
    }

    public function subscribeColunms($columns = '')
    {
        return $columns;
    }

    public function reportModel()
    {
        return new CDbCriteria(array(
            'select'    => $this->select,
            'join'      => $this->join,
            'condition' => $this->filter,
            'with'      => $this->relationFilter,
            'params'    => $this->paramsFilter,
            'order'     => $this->order,
        ));
    }

    public function beforeReport($columns)
    {
        return $columns;
    }

    public function actionReport()
    {

        if (!AccessManager::getInstance($this->instanceModel->getModule())->canRead()) {
            header('HTTP/1.0 401 Unauthorized');
            die("Access denied to read in module:" . $this->instanceModel->getModule());
        }

        ini_set("memory_limit", "1024M");

        $orientation = $_GET['orientation'];

        $columns = json_decode($_GET['columns'], true);

        $columns = $this->repaceColumns($columns);

        $columns = $this->removeColumns($columns);

        $columns = $this->subscribeColunms($columns);

        //Yii::log(print_r($columns,true), 'info');

        $this->setfilter($_GET);

        $fieldGroup = json_decode($_GET['group']);
        $sort       = json_decode($_GET['sort']);

        $arraySort = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
        $dirGroup  = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
        $firstSort = $fieldGroup ? $fieldGroup . ' ' . $dirGroup . ',' : null;
        $sort      = $sort ? $firstSort . implode(',', $sort) : null;

        $this->sort = $this->replaceOrder();

        $this->select = $this->getColumnsFromReport($columns, $fieldGroup);

        $columns = $this->beforeReport($columns);

        $records = $this->abstractModel->findAll($this->reportModel());

        $report                 = new Report();
        $report->orientation    = $orientation;
        $report->title          = $this->titleReport;
        $report->subTitle       = $this->subTitleReport;
        $report->columns        = $columns;
        $report->columnsTable   = $this->getColumnsTable();
        $report->fieldsCurrency = $this->fieldsCurrencyReport;
        $report->fieldsPercent  = $this->fieldsPercentReport;
        $report->fieldsFk       = $this->fieldsFkReport;
        $report->renderer       = $this->rendererReport;
        $report->fieldGroup     = $fieldGroup;
        $report->records        = $this->abstractModel->tableName() == 'pkg_user' ? $records : $this->getAttributesModels($records);
        $report->generate();
    }

    public function actionDestroyReport()
    {
        unlink($this->magnusFilesDirectory . 'report.pdf');
    }

    public function actionCsv()
    {
        ini_set("memory_limit", "1024M");
        if (!AccessManager::getInstance($this->instanceModel->getModule())->canRead()) {
            header('HTTP/1.0 401 Unauthorized');
            die("Access denied to read in module:" . $this->instanceModel->getModule());
        }

        if (!isset(Yii::app()->session['id_user'])) {
            $info = 'User try export CSV without login';
            MagnusLog::insertLOG(7, $info);
            exit;
        } else {
            $info = 'User try export CSV ' . $this->abstractModel->tableName();
            MagnusLog::insertLOG(7, $info);
        }

        $columns = json_decode($_GET['columns'], true);

        $columns = $this->repaceColumns($columns);

        $columns = $this->removeColumns($columns);

        $filter     = isset($_GET['filter']) ? $this->createCondition(json_decode($_GET['filter'])) : null;
        $fieldGroup = json_decode($_GET['group']);
        $sort       = json_decode($_GET['sort']);

        $arraySort    = ($sort && $fieldGroup) ? explode(' ', implode(' ', $sort)) : null;
        $dirGroup     = $arraySort ? $arraySort[array_search($fieldGroup, $arraySort) + 1] : null;
        $firstSort    = $fieldGroup ? $fieldGroup . ' ' . $dirGroup . ',' : null;
        $sort         = $sort ? $firstSort . implode(',', $sort) : null;
        $sort         = $this->replaceOrder();
        $this->filter = $filter = $this->extraFilter($filter);

        $this->magnusFilesDirectory = '/var/www/tmpmagnus/';

        $this->nameFileReport = $this->modelName . '_' . time();
        $pathCsv              = $this->magnusFilesDirectory . $this->nameFileReport . '.csv';

        $sql = "SELECT " . $this->getColumnsFromReport($columns) . " INTO OUTFILE '" . $this->magnusFilesDirectory . $this->nameFileReport . ".csv'
        FIELDS TERMINATED BY '\;' LINES TERMINATED BY '\n'
        FROM " . $this->abstractModel->tableName() . " t $this->join WHERE $this->filter";

        $command = Yii::app()->db->createCommand($sql);
        if (count($this->paramsFilter)) {
            foreach ($this->paramsFilter as $key => $param) {
                $command->bindValue($key, $param, PDO::PARAM_STR);
            }

        }

        $command->execute();

        header('Content-type: application/csv');
        header('Content-Disposition: inline; filename="' . $this->nameFileReport . '_' . $this->nameFileReport . '.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        ob_clean();
        flush();
        if (readfile($pathCsv)) {
            @unlink($pathCsv);
        }
    }

    /**
     * Exclui um registro da model
     */
    public function actionDestroy()
    {
        if (!AccessManager::getInstance($this->instanceModel->getModule())->canDelete()) {
            header('HTTP/1.0 401 Unauthorized');
            die("Access denied to delete in module:" . $this->instanceModel->getModule());
        }
        ini_set("memory_limit", "1024M");
        # recebe os parametros da exclusao
        $values       = $this->getAttributesRequest();
        $namePk       = $this->abstractModel->primaryKey();
        $arrayPkAlias = explode('.', $this->abstractModel->primaryKey());
        $ids          = array();

        $values = $this->beforeDestroy($values);
        if ((isset($_POST['filter']) && strlen($_POST['filter']) > 0)) {
            $filter = isset($_POST['filter']) ? $_POST['filter'] : null;
            $filter = $filter ? $this->createCondition(json_decode($filter)) : $this->defaultFilter;

            $this->filter = $filter = $this->extraFilter($filter);

            $criteria = new CDbCriteria(array(
                'join'      => $this->join,
                'condition' => $this->filter,
                'with'      => $this->relationFilter,
                'params'    => $this->paramsFilter,
            ));

            # retorna o resultado da execucao
            try {
                $this->success = $this->abstractModel->deleteAll($criteria);
                $errors        = true;

                $info = 'Module ' . $this->instanceModel->getModule() . '  ' . json_encode($values);
                MagnusLog::insertLOG(3, $info);

            } catch (Exception $e) {
                $this->success = false;
                $errors        = $this->getErrorMySql($e);
            }

            $this->msg = $this->success ? $this->msgSuccess : $errors;

            if ($this->success) {
                $nameMsg = $this->nameMsg;
            } else {
                $nameMsg = $this->nameMsgErrors;
            }

            $this->afterDestroy($values);

            # retorna o resultado da execucao
            echo json_encode(array(
                $this->nameSuccess => $this->success,
                $nameMsg           => $this->msg,
            ));
            exit;
        } else {
            # Se existe a chave 0, indica que existe um array interno (mais de 1 registro selecionado)
            if (array_key_exists(0, $values)) {
                # percorre o array para excluir o(s) registro(s)
                foreach ($values as $value) {
                    array_push($ids, $value[$namePk]);
                }
            } else {
                array_push($ids, $values[$namePk]);
            }
        }

        if ($this->controllerName == 'user') {
            foreach ($ids as $valueid) {
                if ($valueid == 1) {
                    $this->success = false;
                    $this->msg     = Yii::t('yii', 'Not allowed delete this user');
                }
            }
        }

        if ($this->nameModelRelated) {
            $this->destroyRelated($values);
        }

        if (!$this->success) {
            # retorna o resultado da execucao da ação anterior
            echo json_encode(array(
                $this->nameSuccess   => $this->success,
                $this->nameMsgErrors => $this->msg,
            ));

            return;
        }

        try {
            $criteria = new CDbCriteria();
            $criteria->addInCondition($namePk, $ids);
            $this->success = $this->abstractModel->deleteAll($criteria);
        } catch (Exception $e) {
            $this->success = false;
            $errors        = $this->getErrorMySql($e);
        }

        $this->msg = $this->success ? $this->msgSuccess : $errors;

        if ($this->success) {
            $nameMsg = $this->nameMsg;

            $info = 'Module ' . $this->instanceModel->getModule() . '  ' . json_encode($values);
            MagnusLog::insertLOG(3, $info);

        } else {
            $nameMsg = $this->nameMsgErrors;
        }

        $this->afterDestroy($values);

        # retorna o resultado da execucao
        echo json_encode(array(
            $this->nameSuccess => $this->success,
            $nameMsg           => $this->msg,
        ));
    }

    public function beforeDestroy($values)
    {
        return $values;
    }

    public function afterDestroy($values)
    {
        return;
    }

    /**
     * Retorna o modelo de dados baseado na chave primaria dada na variavel id.
     * @param integer a identificacao do modelo a ser carregado
     * @param object model a ser consultado
     * @return model encontrado
     */
    public function loadModel($id, $model)
    {
        if (is_array($id)) {
            $condition = null;
            foreach ($id as $field => $value) {
                $condition .= "$field = $value AND ";
            }

            $condition   = substr($condition, 0, -5);
            $resultModel = $model->findAll(array(
                'condition' => $condition,
            ));

            $resultModel = array_key_exists(0, $resultModel) ? $resultModel[0] : null;
        } else {
            $resultModel = $model->findByPk((int) $id);
            if ($resultModel === null) {
                return $this->msgRecordNotFound;
            }
        }

        return $resultModel;
    }

    public function setAttributesModels($attributes, $models)
    {
        /*
        for ($i=0; $i < count($attributes) && is_array($attributes); $i++)
        {
        if($attributes[$i]['queue_paused'] == 1 && $attributes[$i]['categorizing'] == 0){
        $attributes[$i]['timeInPause'] = 1;
        }
        }
         */
        return $attributes;
    }

    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        $namePk     = $this->abstractModel->primaryKey();
        foreach ($models as $key => $item) {
            $attributes[$key] = $item->attributes;

            if (isset(Yii::app()->session['isTeam']) && Yii::app()->session['isTeam']) {
                foreach ($this->fieldsInvisibleClient as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if (isset(Yii::app()->session['isOperator']) && Yii::app()->session['isOperator']) {
                foreach ($this->fieldsInvisibleAgent as $field) {
                    unset($attributes[$key][$field]);
                }
            }

            if (!is_array($namePk) && $this->nameOtherFkRelated && get_class($this->abstractModel) === get_class($item)) {
                if (count($this->extraFieldsRelated)) {
                    $resultSubRecords = $this->abstractModelRelated->findAll(array(
                        'select'    => implode(',', $this->extraFieldsRelated),
                        'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk],
                    ));

                    $subRecords = array();

                    if (count($this->extraValuesOtherRelated)) {
                        $attributesSubRecords = array();

                        foreach ($resultSubRecords as $itemModelSubRecords) {
                            $attributesSubRecords = $itemModelSubRecords->attributes;

                            foreach ($this->extraValuesOtherRelated as $relationSubRecord => $fieldsSubRecord) {
                                $arrFieldsSubRecord = explode(',', $fieldsSubRecord);
                                foreach ($arrFieldsSubRecord as $fieldSubRecord) {
                                    $attributesSubRecords[$relationSubRecord . $fieldSubRecord] = $itemModelSubRecords->$relationSubRecord ? $itemModelSubRecords->$relationSubRecord->$fieldSubRecord : null;
                                }
                            }

                            array_push($subRecords, $attributesSubRecords);
                        }
                    } else {
                        foreach ($resultSubRecords as $modelSubRecords) {
                            array_push($subRecords, $modelSubRecords->attributes);
                        }
                    }
                } else {
                    $resultSubRecords = $this->abstractModelRelated->findAll(array(
                        'select'    => $this->nameOtherFkRelated,
                        'condition' => $this->nameFkRelated . ' = ' . $attributes[$key][$namePk],
                    ));

                    $subRecords = array();
                    foreach ($resultSubRecords as $keyModelSubRecords => $modelSubRecords) {
                        array_push($subRecords, (int) $modelSubRecords->attributes[$this->nameOtherFkRelated]);
                    }
                }

                $attributes[$key][$this->nameOtherFkRelated] = $subRecords;
            }

            foreach ($itemsExtras as $relation => $fields) {
                $arrFields = explode(',', $fields);
                foreach ($arrFields as $field) {
                    $attributes[$key][$relation . $field] = $item->$relation ? $item->$relation->$field : null;
                }
            }
        }
        $attributes = $this->setAttributesModels($attributes, $models);
        return $attributes;
    }

    /**
     * Obtem os atributos enviados na requisicao
     * Verifica se a requisicao e via json ou via POST
     * @return array dos atributos enviados na requisicao
     */
    public function getAttributesRequest()
    {
        $arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
        return $arrPost;
    }

    /**
     * Obtem os erros vindos do SQL
     */
    public function getErrorMySql($e)
    {

        if (isset($e->errorInfo)) {
            $codeErro = array_key_exists($e->errorInfo[1], $this->mapErrorsMySql) ? $e->errorInfo[1] : 0;
        } else {
            return $e->getMessage();
        }

        if ($codeErro == 1451) {
            $error = explode("pkg", $e->getMessage());
            $table = explode("CONSTRAINT", $error[1]);

            $table = preg_replace("/(\_|\`,| )/i", "", $table[0]);

            switch ($table) {
                case "refill":
                    $erro = 'Refill';
                    break;
                case "sip":
                    $erro = 'Sipbuddies';
                    break;
                case "sipura":
                    $erro = 'Sipuras';
                    break;
                case "callerid":
                    $erro = 'Callerid';
                    break;
                case "did":
                    $erro = 'Did';
                    break;
                case "campaign":
                    $erro = 'Campaign';
                    break;
                case "campaign_phonebook":
                    $erro = 'Campaign';
                    break;
                case "phonenumber":
                    $erro = 'Phone Number';
                    break;
                case "refill_provider":
                    $erro = 'Refill Provider';
                    break;
                case "trunk":
                    $erro = 'Trunk';
                    break;
                case "rate":
                    $erro = 'Ratecard';
                    break;
                case "user":
                    $erro = 'username';
                    break;

                default:
                    $erro = $table;
                    break;
            }

            return $this->mapErrorsMySql[$codeErro] . "<br> " . Yii::t('yii', 'Please, first delete all related records in the module ') . Yii::t('yii', $erro) . "<br><br><a target = '_blank' href='http://en.wikipedia.org/wiki/Foreign_key'>http://en.wikipedia.org/wiki/Foreign_key</a>";
        } else {
            return $this->mapErrorsMySql[$codeErro] . $e->getMessage();
        }

    }

    public function createCondition($filter)
    {
        $condition = '1';

        if (!is_array($filter)) {
            return $condition;
        }

        foreach ($filter as $key => $f) {
            $isSubSelect = false;

            if (!isset($f->type)) {
                continue;
            }

            $type  = $f->type;
            $field = $f->field;

            if ($this->actionName != 'destroy' && !preg_match("/^id[A-Z]/", $field)) {

                if (is_array($field)) {
                    foreach ($field as $key => $fieldOr) {
                        $field[$key] = strpos($fieldOr, '.') === false ? 't.' . $fieldOr : $fieldOr;
                    }
                } else {
                    $field = strpos($field, '#') === 0 ? str_replace('#', '', $field) : (strpos($field, '.') === false ? 't.' . $field : $field);
                }
            }

            $value     = isset($f->value) ? $f->value : new CDbExpression('NULL');
            $paramName = "p$key";

            if (isset($f->data->comparison)) {
                $comparison = $f->data->comparison;
            } else if (isset($f->comparison)) {
                $comparison = $f->comparison;
            } else {
                $comparison = null;
            }

            switch ($type) {
                case 'string':
                    $field = isset($f->caseSensitive) && $f->caseSensitive && !is_array($field) ? "BINARY $field" : $field;

                    switch ($comparison) {
                        case 'st':
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter = array(
                                    strtok($field, '.') => array(
                                        'condition' => "$field LIKE :$paramName",
                                    ),
                                );
                            } else {
                                $condition .= " AND $field LIKE :$paramName";
                            }

                            $this->paramsFilter[$paramName] = "$value%";

                            break;
                        case 'ed':
                            $this->paramsFilter[$paramName] = "%$value";
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter = array(
                                    strtok($field, '.') => array(
                                        'condition' => "$field LIKE :$paramName",
                                    ),
                                );
                            } else {
                                $condition .= " AND $field LIKE :$paramName";
                            }

                            break;
                        case 'ct':
                            if (is_array($field)) {
                                $conditionsOr = array();

                                foreach ($field as $keyOr => $fieldOr) {
                                    $this->paramsFilter["pOr$keyOr"] = "%$value%";
                                    $fieldOr                         = isset($f->caseSensitive) && $f->caseSensitive ? "BINARY $fieldOr" : $fieldOr;
                                    array_push($conditionsOr, "$fieldOr LIKE :pOr$keyOr");
                                }

                                $conditionsOr = implode(' OR ', $conditionsOr);
                                $condition .= " AND ($conditionsOr)";
                            } else {
                                $this->paramsFilter[$paramName] = "%$value%";
                                if (preg_match("/^id[A-Z].*\./", $field)) {
                                    $this->relationFilter = array(
                                        strtok($field, '.') => array(
                                            'condition' => "$field LIKE :$paramName",
                                        ),
                                    );
                                } else {
                                    $condition .= " AND $field LIKE :$paramName";
                                }

                            }
                            break;
                        case 'eq':
                            $this->paramsFilter[$paramName] = $value;
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter = array(
                                    strtok($field, '.') => array(
                                        'condition' => "$field = :$paramName",
                                    ),
                                );
                            } else {
                                $condition .= " AND $field = :$paramName";
                            }

                            break;
                        case 'df':
                            $this->paramsFilter[$paramName] = $value;
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter = array(
                                    strtok($field, '.') => array(
                                        'condition' => "$field != :$paramName",
                                    ),
                                );
                            } else {
                                $condition .= " AND $field != :$paramName";
                            }

                            break;
                    }

                    break;
                case 'boolean':
                    $this->paramsFilter[$paramName] = (int) $value;
                    $condition .= " AND $field = :$paramName";
                    break;
                case 'numeric':
                    $this->paramsFilter[$paramName] = $value;
                    switch ($comparison) {
                        case 'eq':
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter[strtok($field, '.')] = array(
                                    'condition' => "$field = :$paramName",
                                );
                            } else {
                                $condition .= " AND $field = :$paramName";
                            }

                            break;
                        case 'lt':
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter[strtok($field, '.')] = array(
                                    'condition' => "$field < :$paramName",
                                );
                            } else {
                                $condition .= " AND $field < :$paramName";
                            }

                            break;
                        case 'gt':
                            if (preg_match("/^id[A-Z].*\./", $field)) {
                                $this->relationFilter[strtok($field, '.')] = array(
                                    'condition' => "$field > :$paramName",
                                );
                            } else {
                                $condition .= " AND $field > :$paramName";
                            }

                            break;
                    }

                case 'date':
                    switch ($comparison) {
                        case 'eq':
                            $this->paramsFilter[$paramName] = strtok($value, ' ') . "%";
                            $condition .= " AND $field LIKE :$paramName";
                            break;
                        case 'lt':
                            $this->paramsFilter[$paramName] = $value;
                            $condition .= " AND $field < :$paramName";
                            break;
                        case 'gt':
                            $this->paramsFilter[$paramName] = $value;
                            $condition .= " AND $field > :$paramName";
                            break;
                    }
                    break;
                case 'list':
                    $value = is_array($value) ? $value : array($value);

                    if (!isset($f->tableRelated)) {
                        $paramsIn = array();

                        foreach ($value as $keyIn => $v) {
                            $this->paramsFilter["pIn$key$keyIn"] = $v;
                            array_push($paramsIn, ":pIn$key$keyIn");
                        }

                        $paramsIn = implode(',', $paramsIn);
                        $condition .= " AND $field IN($paramsIn)";
                    } else {
                        $value             = $value[0];
                        $operatorSubSelect = isset($f->operatorSubSelect) ? $f->operatorSubSelect : '=';
                        $subSelect         = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere $operatorSubSelect $value";
                        $condition .= " AND $field IN($subSelect)";
                    }
                    break;
                case 'notlist':
                    $value = is_array($value) ? $value : array($value);

                    if (!isset($f->tableRelated)) {
                        $paramsNotIn = array();

                        if (count($value)) {
                            foreach ($value as $keyNotIn => $v) {
                                $this->paramsFilter["pNotIn$keyNotIn"] = $v;
                                array_push($paramsNotIn, ":pNotIn$keyNotIn");
                            }

                            $paramsNotIn = implode(',', $paramsNotIn);
                            $condition .= " AND $field NOT IN($paramsNotIn)";
                        }
                    } else {
                        $value                          = $value[0];
                        $operatorSubSelect              = isset($f->operatorSubSelect) ? $f->operatorSubSelect : '=';
                        $this->paramsFilter[$paramName] = $value;
                        $subSelect                      = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere $operatorSubSelect :$paramName";
                        $condition .= " AND $field NOT IN($subSelect)";
                    }
                    break;
            }
        }

        return $condition;
    }

    public function saveRelated($id, $subRecords)
    {
        if (!$this->isNewRecord) {
            try {
                $this->abstractModelRelated->deleteAllByAttributes(array(
                    $this->nameFkRelated => $id,
                ));
            } catch (Exception $e) {
                $this->success = false;
                $this->msg     = $this->getErrorMySql($e);
            }
        }

        if ($this->success && is_array($subRecords)) {
            foreach ($subRecords as $item) {
                $nameFkRelated        = $this->nameFkRelated;
                $nameOtherFkRelated   = $this->nameOtherFkRelated;
                $instanceModelRelated = new $this->nameModelRelated;

                $instanceModelRelated->$nameFkRelated = $id;

                if (count($this->extraFieldsRelated)) {
                    foreach ($this->extraFieldsRelated as $field) {
                        $instanceModelRelated->$field = $item[$field];
                    }

                    $valueOtherFkRelated = $item[$nameOtherFkRelated];
                } else {
                    $valueOtherFkRelated = $item;
                }

                $instanceModelRelated->$nameOtherFkRelated = $valueOtherFkRelated;

                try {
                    $this->success = $instanceModelRelated->save();
                } catch (Exception $e) {
                    $this->success = false;
                    $this->msg     = $this->getErrorMySql($e);
                }

                if (!$this->success) {
                    break;
                }
            }
        }

        if (!$this->success) {
            echo json_encode(array(
                $this->nameSuccess   => $this->success,
                $this->nameMsgErrors => $this->msg,
            ));

            exit;
        }
    }

    public function destroyRelated($values)
    {
        $namePk = $this->abstractModel->primaryKey();
        if (array_key_exists(0, $values)) {
            foreach ($values as $value) {
                $id = $value[$namePk];

                try {
                    $this->abstractModelRelated->deleteAllByAttributes(array(
                        $this->nameFkRelated => $id,
                    ));
                } catch (Exception $e) {
                    $this->success = false;
                    $this->msg     = $this->getErrorMySql($e);
                }

                if (!$this->success) {
                    break;
                }
            }
        } else {
            $id = $values[$namePk];

            try {
                $this->abstractModelRelated->deleteAllByAttributes(array(
                    $this->nameFkRelated => $id,
                ));
            } catch (Exception $e) {
                $this->success = false;
                $this->msg     = $this->getErrorMySql($e);
            }
        }
    }

    public function getColumnsTable()
    {
        $command = Yii::app()->db->createCommand('SHOW COLUMNS FROM ' . $this->abstractModel->tableName());
        return $command->queryAll();
    }

    public function getColumnsFromReport($columns, $fieldGroup = null)
    {
        $arrayColumns = array();

        foreach ($columns as $column) {
            $fieldName = $column['dataIndex'];
            if (is_array($this->fieldsFkReport) && array_key_exists($fieldName, $this->fieldsFkReport)) {
                $fk          = $this->fieldsFkReport[$fieldName];
                $table       = $fk['table'];
                $pk          = $fk['pk'];
                $fieldReport = $fk['fieldReport'];
                if (($fieldName == 'id' && $fieldReport == 'destination') || ($fieldName == 'idPrefixprefix' && $fieldReport == 'destination')) {
                    //altera as colunas para poder pegar o destino das tarifas
                    $subSelect = "(SELECT $fieldReport FROM $table WHERE $table.$pk = t.id_prefix) $fieldName";
                } else {
                    $subSelect = "(SELECT $fieldReport FROM $table WHERE $table.$pk = t.$fieldName) $fieldName";
                }

                if ($fieldName === $fieldGroup) {
                    array_unshift($arrayColumns, $subSelect);
                } else {
                    array_push($arrayColumns, $subSelect);
                }
            } else {
                if ($fieldName === $fieldGroup) {
                    array_unshift($arrayColumns, $fieldName);
                } else {
                    array_push($arrayColumns, $fieldName);
                }
            }
        }

        $arrayColumns = $this->columnsReplace($arrayColumns);

        $columns = implode(',', $arrayColumns);

        return $columns;
    }

    public function columnsReplace($arrayColumns)
    {
        $patterns = array(
            '/credit/',
            '/description/',
            '/,id_user/',
            '/^id_user/',
            '/^name/',
        );
        $arrayReplace = array(
            't.credit',
            't.description',
            ',t.id_user',
            't.id_user',
            't.name',
        );

        $arrayColumns = preg_replace($patterns, $arrayReplace, $arrayColumns);
        return $arrayColumns;
    }

    public function replaceOrder()
    {
        if (preg_match('/idPrefixdestination/', $this->order)) {
            if (!preg_match("/JOIN pkg_prefix/", $this->join)) {
                $this->join .= ' LEFT JOIN pkg_prefix b ON t.id_prefix = b.id';
            }

        }
        //ajustar para ordenar corretamente no modulo rates
        $this->order = preg_replace("/idPrefixprefix/", 't.id_prefix', $this->order);
        $this->order = preg_replace("/idPrefixdestination/", 'b.destination', $this->order);
        $this->order = preg_replace("/idPhonebookname/", 'b.name', $this->order);
        $this->order = preg_replace("/idUserusername/", 't.id_user', $this->order);
        $this->order = preg_replace("/idDiddid/", 't.id_did', $this->order);

    }

    public function extraFilter($filter)
    {
        if ($this->defaultFilter != 1) {
            $filter = $filter . ' AND ' . $this->defaultFilter;
        }

        return $this->extraFilterCustom($filter);
    }

    public function extraFilterCustom($filter)
    {
        if (Yii::app()->session['isAdmin']) {
            $filter = $this->extraFilterCustomAdmin($filter);
        } elseif (Yii::app()->session['isOperator']) {
            $filter = $this->extraFilterCustomOperator($filter);
        } else if (Yii::app()->session['isTeam']) {
            $filter = $this->extraFilterCustomTeam($filter);
        }
        return $filter;
    }
    public function extraFilterCustomAdmin($filter)
    {
        return $filter;
    }

    public function extraFilterCustomTeam($filter)
    {

        //se for cliente filtrar pelo pkg_user.id
        $filter .= ' AND t.id_team = :clfby';
        $this->paramsFilter[':clfby'] = Yii::app()->session['id_team'];

        return $filter;
    }

    public function extraFilterCustomOperator($filter)
    {
        return $filter;
    }

    public function recordsExtraSum($records)
    {
        return array();
    }

    public function repaceColumns($columns)
    {
        for ($i = 0; $i < count($columns); $i++) {

            if ($columns[$i]['dataIndex'] == 'idUserusername') {
                $columns[$i]['dataIndex'] = 'id_user';
            } else if ($columns[$i]['dataIndex'] == 'idPrefixdestination') {
                $columns[$i]['dataIndex'] = 'id';
            } else if ($columns[$i]['dataIndex'] == 'idPrefixprefix') {
                $columns[$i]['dataIndex'] = 'id_prefix';
            } else if ($columns[$i]['dataIndex'] == 'idPhonebookt.name') {
                $columns[$i]['dataIndex'] = 'id_phonebook';
            } else if ($columns[$i]['dataIndex'] == 'idDiddid') {
                $columns[$i]['dataIndex'] = 'id_did';
            }

        }

        return $columns;
    }

    public function removeColumns($columns)
    {
        return $columns;
    }

    public function importCsvSetAdditionalParams()
    {
        return array();
    }

    public function actionImportFromCsv()
    {
        $module = $this->instanceModel->getModule();

        if (!AccessManager::getInstance($module)->canCreate()) {
            header('HTTP/1.0 401 Unauthorized');
            die("Access denied to save in module: $module");
        }

        if (!Yii::app()->session['id_user']) {
            exit();
        }

        $values = $this->getAttributesRequest();

        $interpreter = new CSVInterpreter($_FILES['file']['tmp_name']);
        $array       = $interpreter->toArray();

        $additionalParams = $this->importCsvSetAdditionalParams();
        $errors           = array();
        if ($array) {
            $recorder = new CSVActiveRecorder($array, $this->instanceModel, $additionalParams);
            if ($recorder->save());
            $errors = $recorder->getErrors();

        } else {
            $errors = $interpreter->getErrors();
        }

        $this->nameMsg = count($errors) > 0 ? 'errors' : $this->nameMsg;
        echo json_encode(array(
            $this->nameSuccess => count($errors) > 0 ? false : true,
            $this->nameMsg     => count($errors) > 0 ? implode(',', $errors) : $this->msgSuccess,
        ));

        $this->afterImportFromCsv($values);

    }

    public function afterImportFromCsv($values)
    {
        return;
    }

    public function upload($fieldName, $folder, $fileName = null)
    {
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $file     = CUploadedFile::getInstanceByName($fieldName);
        $fileName = $fileName ? $fileName . '.' . $file->extensionName : $file->getName();
        $path     = $folder . $fileName;
        $success  = file_put_contents($path, base64_decode(file_get_contents($file->getTempName())));

        return $success ? $path : false;
    }

    public function remoteLogin($username, $password)
    {
        //check remote login
        $filterUser = '((s.username COLLATE utf8_bin = :key OR s.email COLLATE utf8_bin LIKE :key) AND UPPER(MD5(s.password)) = :key1)';
        $filterSip  = '(t.name COLLATE utf8_bin = :key AND UPPER(MD5(t.secret)) = :key1 )';
        $modelSip   = Sip::model()->find(
            array(
                'condition' => $filterUser . ' OR ' . $filterSip,
                'join'      => 'LEFT JOIN pkg_user s ON t.id_user = s.id',
                'params'    => array(
                    ':key'  => $username,
                    ':key1' => strtoupper($password),
                ),
            ));
        if (count($modelSip)) {

            $idUserType                          = $modelSip->idUser->idGroup->idUserType->id;
            Yii::app()->session['isAdmin']       = $idUserType == 1 ? true : false;
            Yii::app()->session['isOperator']    = $idUserType == 2 ? true : false;
            Yii::app()->session['isTeam']        = $idUserType == 3 ? true : false;
            Yii::app()->session['isClientAgent'] = isset($modelSip->idUser->id_user) && $modelSip->idUser->id_user > 1 ? true : false;
            Yii::app()->session['id_plan']       = $modelSip->idUser->id_plan;
            Yii::app()->session['credit']        = isset($modelSip->idUser->credit) ? $modelSip->idUser->credit : 0;
            Yii::app()->session['username']      = $modelSip->idUser->username;
            Yii::app()->session['logged']        = true;
            Yii::app()->session['id_user']       = $modelSip->idUser->id;
            Yii::app()->session['id_agent']      = is_null($modelSip->idUser->id_user) ? 1 : $modelSip->idUser->id_user;
            Yii::app()->session['name_user']     = $modelSip->idUser->firstname . ' ' . $modelSip->idUser->lastname;
            Yii::app()->session['id_group']      = $modelSip->idUser->id_group;
            Yii::app()->session['user_type']     = $idUserType;
            Yii::app()->session['language']      = $modelSip->idUser->language;
            Yii::app()->session['currency']      = $this->config['global']['base_currency'];
            Yii::app()->session['id_team']       = $modelUser->id_team;
        }
        return $modelSip;
    }
}
