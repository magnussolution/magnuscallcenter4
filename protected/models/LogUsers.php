<?php
/**
 * Modelo para a tabela "Call".
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
 * 19/09/2012
 */

class LogUsers extends Model
{
    protected $_module = 'logusers';
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
        return 'pkg_log';
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
            array('id_user', 'required'),
            array('id_user, id_log_actions', 'numerical', 'integerOnly' => true),
            array('date', 'length', 'max' => 100),
            array('ip', 'length', 'max' => 16),
            array('description', 'safe'),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idUser'       => array(self::BELONGS_TO, 'User', 'id_user'),
            'idLogActions' => array(self::BELONGS_TO, 'LogActions', 'id_log_actions'),
        );
    }
}
