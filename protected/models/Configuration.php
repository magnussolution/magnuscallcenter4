<?php
/**
 * Modelo para a tabela "Configuration".
 * =======================================
 * ###################################
 * CallCenter
 *
 * @package	CallCenter
 * @author	Adilson Leffa Magnus.
 * @copyright	Todos os direitos reservados.
 * ###################################
 * =======================================
 * MagnusSolution.com <info@magnussolution.com>
 * 17/08/2012
 */

class Configuration extends Model
{
	protected $_module = 'configuration';
	/**
	 * Retorna a classe estatica da model.
	 * @return Prefix classe estatica da model.
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
		return 'pkg_configuration';
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
			array('config_key', 'required'),
            array('status', 'numerical', 'integerOnly'=>true),
            array('config_title, config_key', 'length', 'max'=>100),
            array('config_value', 'length', 'max'=>200),
            array('config_description', 'length', 'max'=>500),
            array('config_group_title', 'length', 'max'=>64),
            array('config_value', 'checkConfg'),
		);
	}

	public function checkConfg($attribute,$params)
	{
		$error = false;
		//validation values
		if ($this->config_key == 'base_country')
		{
			$valuesAllow = array('BRL','ARS','USA');
			if (!in_array($this->config_value, $valuesAllow))
			{
				$error = true;
			}
		}

		if ($this->config_key == 'base_language')
		{
			$valuesAllow = array('es','en','pt_BR');
			$this->config_value = $this->config_value == 'br' ? 'pt_BR' : $this->config_value;
			if (!in_array($this->config_value, $valuesAllow))
			{
				$error = true;
			}
		}

		if ($this->config_key == 'template')
		{
			$valuesAllow = array('green','gray','blue','yellow','red','orange','purple',
				'green-neptune','gray-neptune','blue-neptune','yellow-neptune','red-neptune','orange-neptune','purple-neptune');

			if (!in_array($this->config_value, $valuesAllow))
			{
				$error = true;
			}
		}

		if ($error) {
			$this->addError($attribute,Yii::t('yii','ERROR: Invalid option'));
		}
	}
}