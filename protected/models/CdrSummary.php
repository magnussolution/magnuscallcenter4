<?php
/**
 * Modelo para a tabela "CcCall".
 * MagnusSolution.com <info@magnussolution.com> 
 * 17/08/2012
 */

class CdrSummary extends Model
{
	protected $_module = 'cdrsummary';
	public $day;
	public $sunsessiontime;
	public $nbcall;
	public $success_calls;
	public $aloc_success_calls;
	public $aloc_all_calls;
	public $sumsessiontime;
	public $sumsuccess_calls;
	public $sumaloc_success_calls;
	public $sumaloc_all_calls;
	public $sumnbcall;
	public $idUserusername;
	public $idTrunktrunkcode;
	public $categoriacion_completa;
	public $sumcategoriacion_completa;


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
            array('sessiontime, nbcall, success_calls, aloc_success_call, aloc_all_calls, categoriacion_completa, sumcategoriacion_completa, sumaloc_all_calls, sumaloc_success_calls', 'length', 'max'=>50),
     	);
	}

}