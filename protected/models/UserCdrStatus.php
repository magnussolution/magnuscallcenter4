<?php
/**
 * Modelo para a tabela "UserCdrStatus".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class UserCdrStatus extends Model
{
	protected $_module = 'usercdrstatus';
	public $day;
	public $status;
	/**
	 * Retorna a classe estatica da model.
	 * @return UserCdrStatus classe estatica da model.
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
		return 'pkg_cdr';
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
			array('id_user, id_campaign, id_trunk, sessiontime, id_category, terminatecauseid', 'numerical', 'integerOnly'=>true),
            array('sessionid, uniqueid, calledstation, dnid', 'length', 'max'=>50),
     	);
	}

	public function relations()
	{
		return array(
			'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
			'idCategory' => array(self::BELONGS_TO, 'Category', 'id_category')
		);
	}

}