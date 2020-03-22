<?php
/**
 * Modelo para a tabela "CampaignPhonebook".
 * MagnusSolution.com <info@magnussolution.com>  
 * 29/10/2012
 */

class MassiveCallCampaignPhonebook extends Model
{
	protected $_module = 'campaignphonebook';
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
		return 'pkg_massive_call_campaign_phonebook';
	}

	/**
	 * @return nome da(s) chave(s) primaria(s).
	 */
	public function primaryKey()
	{
		return array('id_massive_call_campaign','id_massive_call_phonebook');
	}

	/**
	 * @return array validacao dos campos da model.
	 */
	public function rules()
	{
		return array(
			array('id_massive_call_campaign, id_massive_call_phonebook', 'required'),
			array('id_massive_call_campaign, id_massive_call_phonebook', 'numerical', 'integerOnly'=>true),
		);
	}
}