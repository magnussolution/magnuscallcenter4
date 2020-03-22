<?php

class CallOnline extends CActiveRecord
{
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
        return 'pkg_call_online';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('id_user,id_campaign', 'numerical', 'integerOnly' => true),
            array('canal, tronco', 'length', 'max' => 50),
            array('ndiscado, status, duration', 'length', 'max' => 16),
            array('codec, reinvite', 'length', 'max' => 5),
        );
    }
    public function relations()
    {
        return array(
            'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }
}
