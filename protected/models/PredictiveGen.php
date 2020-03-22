<?php

class PredictiveGen extends Model
{
    public $AVG_ringing_time;

    protected $_module = 'predictivegen';
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
        return 'pkg_predictive_gen';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id_phonebook, ringing_time,amd', 'numerical', 'integerOnly' => true),
            array('uniqueID', 'length', 'max' => 30),
            array('date', 'safe'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'idPhonebook' => array(self::BELONGS_TO, 'PhoneBook', 'id_phonebook'),
        );
    }
}
