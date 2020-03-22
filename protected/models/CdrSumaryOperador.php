<?php
/**
 * Modelo para a tabela "Cdr".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class CdrSumaryOperador extends Model
{
	protected $_module = 'cdrsumaryoperador';
	public $category;
	public $day;
	public $timeTotalCalls;
	public $totalCalls;
	public $efectivastotal;
	public $id_user2;
	public $id_campaign2;
	public $ratio;
	public $timeTotalEfectivas;
	/**
	 * Retorna a classe estatica da model.
	 * @return CcPrefix classe estatica da model.
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
		return 'pkg_logins_campaign';
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
			array('id_user, total_time, pause, id_campaign', 'numerical', 'integerOnly'=>true),
            	array('starttime, stoptime, turno', 'length', 'max'=>50),
     	);
	}

	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign'),
		);
	}

}