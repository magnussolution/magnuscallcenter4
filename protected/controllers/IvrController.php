<?php
/**
 * Acoes do modulo "Ivr".
 *
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 * 19/09/2012
 */

class IvrController extends BaseController
{
    public $attributeOrder = 't.id';
    private $uploaddir;
    public function init()
    {
        $this->uploaddir     = $this->magnusFilesDirectory . 'sounds/';
        $this->instanceModel = new Ivr;
        $this->abstractModel = Ivr::model();
        $this->titleReport   = Yii::t('yii', 'Ivr');
        parent::init();
    }

    public function beforeSave($values)
    {
        for ($i = 0; $i <= 10; $i++) {

            if (isset($values['type_' . $i])) {

                if ($values['type_' . $i] == 'repeat') {
                    $values['option_' . $i] = 'repeat';
                } elseif ($values['type_' . $i] == 'undefined' || $values['type_' . $i] == '') {
                    $values['option_' . $i] = '';
                } elseif (preg_match("/group|number|custom|hangup/", $values['type_' . $i])) {

                    $values['option_' . $i] = $values['type_' . $i] . '|' . $values['extension_' . $i];
                } else {
                    $values['option_' . $i] = $values['type_' . $i] . '|' . $values['id_' . $values['type_' . $i] . '_' . $i];
                }

            }

            if (isset($values['type_out_' . $i])) {

                if ($values['type_out_' . $i] == 'repeat') {
                    $values['option_out_' . $i] = 'repeat';
                } elseif ($values['type_out_' . $i] == 'undefined' || $values['type_out_' . $i] == '') {
                    $values['option_out_' . $i] = '';
                } elseif (preg_match("/group|number|custom|hangup/", $values['type_out_' . $i])) {
                    $values['option_out_' . $i] = $values['type_out_' . $i] . '|' . $values['extension_out_' . $i];
                } else {
                    $values['option_out_' . $i] = $values['type_out_' . $i] . '|' . $values['id_' . $values['type_out_' . $i] . '_out_' . $i];
                }

            }

        }

        return $values;
    }

    public function afterSave($model, $values)
    {
        if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1) {
            if (file_exists($this->uploaddir . 'idIvrDidWork_' . $model->id . '.wav')) {
                unlink($this->uploaddir . 'idIvrDidWork_' . $model->id . '.wav');
            }
            $typefile   = explode('.', $_FILES["workaudio"]["name"]);
            $uploadfile = $this->uploaddir . 'idIvrDidWork_' . $model->id . '.' . $typefile[1];
            move_uploaded_file($_FILES["workaudio"]["tmp_name"], $uploadfile);
        }

        if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1) {
            if (file_exists($this->uploaddir . 'idIvrDidNoWork_' . $model->id . '.wav')) {
                unlink($this->uploaddir . 'idIvrDidNoWork_' . $model->id . '.wav');
            }
            $typefile   = explode('.', $_FILES["noworkaudio"]["name"]);
            $uploadfile = $this->uploaddir . 'idIvrDidNoWork_' . $model->id . '.' . $typefile[1];
            move_uploaded_file($_FILES["noworkaudio"]["tmp_name"], $uploadfile);
        }
    }

    public function setAttributesModels($attributes, $models)
    {

        for ($i = 0; $i < count($attributes) && is_array($attributes); $i++) {

            foreach ($attributes[$i] as $key => $value) {

                if (preg_match("/^option_out_/", $key)) {

                    $itemOption = explode("|", $value);
                    $itemKey    = explode("_", $key);
                    if (!isset($attributes[$i]['type_out_' . end($itemKey)])) {
                        $attributes[$i]['type_out_' . end($itemKey)] = $itemOption[0];
                    }

                    if (isset($itemOption[1])) {
                        $attributes[$i]['id_' . $itemOption[0] . '_out_' . end($itemKey)] = end($itemOption);
                        if (is_numeric($itemOption[1])) {
                            $model = $itemOption[0]::model()->findByPk(end($itemOption));

                            $attributes[$i]['id_' . $itemOption[0] . '_out_' . end($itemKey) . '_name'] = isset($model->name) ? $model->name : '';
                        } else {
                            $attributes[$i]['id_' . $itemOption[0] . '_out_' . end($itemKey) . '_name'] = '';
                        }
                    } else if (isset($itemOption[1]) && preg_match("/number|group|custom|hangup/", $itemOption[0])) {
                        $attributes[$i]['extension_out_' . end($itemKey)] = $itemOption[1];
                    }

                } else if (preg_match("/^option_/", $key)) {

                    $itemOption = explode("|", $value);
                    $itemKey    = explode("_", $key);
                    if (!isset($attributes[$i]['type_' . end($itemKey)])) {
                        $attributes[$i]['type_' . end($itemKey)] = $itemOption[0];
                    }

                    if (isset($itemOption[1])) {
                        $attributes[$i]['id_' . $itemOption[0] . '_' . end($itemKey)] = end($itemOption);
                        if (is_numeric($itemOption[1])) {
                            $model = $itemOption[0]::model()->findByPk(end($itemOption));

                            $attributes[$i]['id_' . $itemOption[0] . '_' . end($itemKey) . '_name'] = isset($model->name) ? $model->name : '';
                        } else {
                            $attributes[$i]['id_' . $itemOption[0] . '_' . end($itemKey) . '_name'] = '';
                        }
                    } else if (isset($itemOption[1]) && preg_match("/number|group|custom|hangup/", $itemOption[0])) {
                        $attributes[$i]['extension_' . end($itemKey)] = $itemOption[1];
                    }
                }
            }
        }

        return $attributes;
    }
}
