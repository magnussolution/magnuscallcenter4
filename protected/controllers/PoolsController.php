<?php
/**
 * Acoes do modulo "Cdr".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class PoolsController extends BaseController
{
    public $attributeOrder = 'id DESC';

    public function init()
    {
        $this->instanceModel = new Pools;
        $this->abstractModel = Pools::model();
        $this->titleReport   = Yii::t('yii', 'Pools');
        parent::init();
    }

    public function actionRead($asJson = true, $condition = null)
    {

        if (Yii::app()->session['isOperator'] == true) {
            $this->showToUser();
            return;
        }

        parent::actionRead();
    }
    public function getAttributesModels($models, $itemsExtras = array())
    {
        $attributes = false;
        $namePk     = $this->abstractModel->primaryKey();
        foreach ($models as $key => $item) {
            $attributes[$key] = $item->attributes;

            for ($i = 0; $i < 10; $i++) {

                $id_pool = $item->{'id_polls_' . $i};

                if (isset($id_pool) && strlen($id_pool) > 0) {

                    $sql                                          = "SELECT question FROM pkg_pools WHERE id = " . $id_pool;
                    $result                                       = Yii::app()->db->createCommand($sql)->queryAll();
                    $attributes[$key]['id_pools_' . $i . '_name'] = isset($result[0]['question']) ? $result[0]['question'] : '';
                }

            }

            if (isset($_SESSION['isOperator']) && $_SESSION['isOperator']) {
                foreach ($this->fieldsInvisibleOperator as $field) {
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

        return $attributes;
    }

    public function showToUser()
    {

        $modelPools = Pools::model()->find(1);

        echo '<center style="font-size: 30px"><table border=3 width="70%"><td><br>';
        echo '' . $modelPools->question . '';
        echo '<br>';
        ?>
        <?php if ($modelPools->type == 'checkbox'): ?>


            <?php if (strlen($modelPools->answer_0)): ?>
                <input type="checkbox" name="<?php echo $modelPools->id_polls_0 ?>" value="Bike"><?php echo $modelPools->answer_0 ?><br>
            <?php endif?>
            <?php if (strlen($modelPools->answer_1)): ?>
                <input type="checkbox" name="<?php echo $modelPools->id_polls_1 ?>" value="Bike"><?php echo $modelPools->answer_1 ?><br>
            <?php endif?>


        <?php endif;?>

        <?php echo '</center></tr></table>' ?>
        <?php
}
}
