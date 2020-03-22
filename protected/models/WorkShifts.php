<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class WorkShifts extends Model
{
	protected $_module = 'workshift';
	var $countUser;
	var $signup;
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
		return 'pkg_work_shift';
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
			array('id_user', 'numerical', 'integerOnly'=>true),
			array('turno, day, start_time, stop_time', 'length', 'max'=>100),
			array('week_day,turno', 'length', 'max'=>20),
		);
	}


	public function beforeSave()
	{
		$this->id_user = $this->id_user == '' || !is_numeric($this->id_user) ? NULL : $this->id_user;		
		return parent::beforeSave();
	}
	
}