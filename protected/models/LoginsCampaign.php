<?php
/**
 * Modelo para a tabela "Cdr".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class LoginsCampaign extends Model
{
	protected $_module = 'loginscampaign';
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
			array('id_campaign, id_user, total_time', 'numerical', 'integerOnly'=>true),
			array('id_breaks', 'numerical', 'integerOnly'=>false),
            	array('starttime,stoptime,login_type', 'length', 'max'=>20),
            	array('turno', 'length', 'max'=>1),
     	);
	}

	/**
	 * @return array regras de relacionamento.
	 */
	public function relations()
	{
		return array(
			'idBreak' => array(self::BELONGS_TO, 'Breaks', 'id_breaks'),
			'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign'),
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
		);
	}

}