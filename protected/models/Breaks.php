<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class Breaks extends Model
{
    protected $_module = 'breaks';
    /**
     * Retorna a classe estatica da model.
     * @return Campaign classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return nome da tabela.
     */
    public function tableName()
    {
        return 'pkg_breaks';
    }

    /**
     * @return nome da(s) chave(s) primaria(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        return array(
            array('mandatory, id_team, maximum_time,status', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 50),
            array('start_time, stop_time', 'length', 'max' => 100),
        );
    }

    public function beforeSave()
    {
        $this->maximum_time = $this->mandatory == 1 ? intval((strtotime($this->stop_time) - strtotime($this->start_time)) / 60) : $this->maximum_time;

        return parent::beforeSave();
    }
}
