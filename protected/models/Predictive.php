<?php

class Predictive extends Model
{

    protected $_module = 'predictive';
    /**
     * Returns the static model of the specified AR class.
     * @return CActiveRecord the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'pkg_predictive';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id_campaign,amd', 'numerical', 'integerOnly' => true),
            array('uniqueid', 'length', 'max' => 30),
            array('number,operador', 'length', 'max' => 20),
        );
    }
}
