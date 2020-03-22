<?php
/**
 * =======================================
 * ###################################
 * MagnusCallCenter
 *
 * @package MagnusCallCenter
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2012 - 2018 MagnusCallCenter. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnussolution/magnuscallcenter/issues
 * =======================================
 * MagnusCallCenter.com <info@magnussolution.com>
 *
 */
class Loadconfig
{
    public static function getConfig()
    {
        $modelConfiguration = Configuration::model()->findAll();

        $config = array();
        foreach ($modelConfiguration as $conf) {
            $config[$conf->config_group_title][$conf->config_key] = $conf->config_value;
        }

        return $config;
    }
}
