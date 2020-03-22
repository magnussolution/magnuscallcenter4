<?php
/**
 * Modelo para a tabela "CcTariffgroup".
 * MagnusSolution.com <info@magnussolution.com>  
 * 24/07/2012
 */

class UserCampaign extends Model
{

	protected $_module = 'usercampaign';
	/**
	 * Retorna a classe estatica da model.
	 * @return SubModule classe estatica da model.
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
		return 'pkg_user_campaign';
	}

	/**
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey()
	{
		return array('id_user','id_campaign');
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{
		return array(
			array('id_user, id_campaign', 'required'),
			array('id_user, id_campaign', 'numerical', 'integerOnly'=>true),
		);
	}
}