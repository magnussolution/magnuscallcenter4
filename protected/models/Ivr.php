<?php
/**
 * Modelo para a tabela "Ivr".
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

class Ivr extends Model
{
    protected $_module = 'ivr';
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
        return 'pkg_ivr';
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
            array('TimeOfDay_monFri, TimeOfDay_sat, TimeOfDay_sun', 'length', 'max' => 100),
            array('name, option_0, option_1, option_2, option_3, option_4, option_5, option_6, option_7, option_8, option_9, option_10', 'length', 'max' => 50),
            array('option_out_0, option_out_1, option_out_2, option_out_3, option_out_4, option_out_5, option_out_6, option_out_7, option_out_8, option_out_9, option_out_10', 'length', 'max' => 50),
        );
    }

}
