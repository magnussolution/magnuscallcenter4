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

class InactiveToActiveCommand extends ConsoleCommand
{

    public function run($args)
    {
        $modelCampaign = Campaign::model()->findAll(array(
            'condition' => 'status = 1',
        ));

        foreach ($modelCampaign as $key => $campaign) {
            //get all campaign phonebook
            $modelCampaignPhonebook = CampaignPhonebook::model()->findAll('id_campaign = :key', array(':key' => $campaign->id));
            $ids_phone_books        = array();
            foreach ($modelCampaignPhonebook as $key => $phonebook) {
                $ids_phone_books[] = $phonebook->id_phonebook;
            }
            print_r($ids_phone_books);
            $criteria = new CDbCriteria();
            $criteria->addInCondition('id_phonebook', $ids_phone_books);
            $criteria->addCondition('(status = 0 OR id_category = 0) AND try < 4');

            PhoneNumber::model()->updateAll(array('status' => 1, 'id_category' => 1), $criteria);
        }
    }
}
