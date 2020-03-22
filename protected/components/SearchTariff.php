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

class SearchTariff
{
    public function find($phonenumber, $id_campaign, $agi = null)
    {

        if (is_null($agi)) {
            $agi = $this;
        }

        if ($phonenumber == 0) {
            $sql = "SELECT t.id, t.id_phonebook, t.number, t.name, t.creationdate, t.status, t.info, t.city,
                pkg_phonebook.id_trunk, trunkprefix, providertech, providerip, removeprefix, failover_trunk, trunkcode, pkg_campaign.id as id_campaign_number
            FROM  pkg_phonenumber t
            INNER JOIN pkg_phonebook ON t.id_phonebook = pkg_phonebook.id
            INNER JOIN pkg_campaign_phonebook ON pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id
            INNER JOIN pkg_campaign ON pkg_campaign_phonebook.id_campaign = pkg_campaign.id
            INNER JOIN pkg_trunk ON pkg_phonebook.id_trunk = pkg_trunk.id
            LIMIT 1";
        } else {

            $sql = "SELECT t.id, t.id_phonebook, t.number, t.name, t.creationdate, t.status, t.info, t.city,
                pkg_phonebook.id_trunk, trunkprefix, providertech, providerip, removeprefix, failover_trunk, trunkcode, pkg_campaign.id as id_campaign_number
            FROM  pkg_phonenumber t
            INNER JOIN pkg_phonebook ON t.id_phonebook = pkg_phonebook.id
            INNER JOIN pkg_campaign_phonebook ON pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id
            INNER JOIN pkg_campaign ON pkg_campaign_phonebook.id_campaign = pkg_campaign.id
            INNER JOIN pkg_trunk ON pkg_phonebook.id_trunk = pkg_trunk.id
            WHERE  pkg_campaign.id IN ($id_campaign)
            AND
            (
                t.number = SUBSTRING('$phonenumber',1,length(t.number)) OR
                t.mobile = '$phonenumber'  OR
                t.mobile_2 = '$phonenumber'  OR
                t.number_home = '$phonenumber'  OR
                t.number_office = '$phonenumber'
            ) ORDER BY LENGTH(t.number) DESC";
        }

        $agi->verbose($sql, 25);

        $result = Yii::app()->db->createCommand($sql)->queryAll();
        $agi->verbose(print_r($result, true), 25);

        if (!is_array($result) || count($result) == 0) {
            $agi->verbose('No found number ' . $phonenumber . ' in the campaign ' . $id_campaign, 3);
            return false;
        }
        $old_phonenumber = $phonenumber;
        $phonenumber     = Portabilidade::getDestination($phonenumber, $result[0]['id_phonebook']);
        if ($phonenumber != $old_phonenumber) {
            $agi->verbose('Usa portabilidade');
            //55341 5551982464731
            $rn1 = substr($phonenumber, 0, 5);
            $sql = "SELECT * FROM pkg_trunk WHERE id IN (SELECT id_trunk FROM pkg_codigos_trunks WHERE id_codigo IN (SELECT id FROM pkg_codigos WHERE company = (SELECT company FROM pkg_codigos WHERE prefix = '$rn1')) ) ORDER BY RAND()";
            $agi->verbose($sql, 25);
            $resultPortabilidade = Yii::app()->db->createCommand($sql)->queryAll();

            if (count($resultPortabilidade) > 0) {

                $result[0]['id_trunk']       = $resultPortabilidade[0]['id'];
                $result[0]['trunkprefix']    = $resultPortabilidade[0]['trunkprefix'];
                $result[0]['providertech']   = $resultPortabilidade[0]['providertech'];
                $result[0]['providerip']     = $resultPortabilidade[0]['providerip'];
                $result[0]['removeprefix']   = $resultPortabilidade[0]['removeprefix'];
                $result[0]['failover_trunk'] = isset($resultPortabilidade[1]) && !is_numeric($resultPortabilidade[0]['failover_trunk']) ?
                $resultPortabilidade[1]['id'] : $resultPortabilidade[0]['failover_trunk'];

                $result[0]['portabilidadeTrunks'] = $resultPortabilidade;
            } else {
                $agi->verbose('Portabilidade ativa, mas sem tronco para ' . $rn1, 3);
            }

        }

        $agi->verbose(print_r($result[0], true), 25);

        return $result;
    }

    public function verbose($message, $level = 3)
    {
        if ($level >= 3) {
            echo $message . "<br>";
        }

    }
}
