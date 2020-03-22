<?php
/**
 * Modelo para a tabela "Campaign".
 * MagnusSolution.com <info@magnussolution.com> 
 * 28/10/2012
 */

class Billing extends Model
{
	protected $_module = 'billing';
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
		return 'pkg_billing';
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
			array('id_user_online, id_user,id_campaign', 'numerical', 'integerOnly'=>true),
			array('total_price, date, turno, efetivas, ratio, total_time, ratio_total, incremento', 'length', 'max'=>15),
			array('description', 'length', 'max'=>200)
		);
	}

	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign')
		);
	}	
	
}