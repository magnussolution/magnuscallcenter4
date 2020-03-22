<?php
/**
 * Modelo para a tabela "Diddestination".
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 * 24/09/2012
 */

class Diddestination extends Model
{
    protected $_module = 'diddestination';

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
        return 'pkg_did_destination';
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

            array('id_ivr, id_user, id_campaign, activated, voip_call', 'numerical', 'integerOnly' => true),
            array('destination, did', 'length', 'max' => 120),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idUser'     => array(self::BELONGS_TO, 'User', 'id_user'),
            'idIvr'      => array(self::BELONGS_TO, 'Ivr', 'id_ivr'),
            'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign'),
        );
    }

    public function beforeSave()
    {
        $this->voip_call = isset($this->voip_call) ? $this->voip_call : 1;

        return parent::beforeSave();
    }
}
