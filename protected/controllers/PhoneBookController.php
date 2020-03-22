<?php
/**
 * Acoes do modulo "PhoneBook".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class PhoneBookController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idTrunk' => 'trunkcode');

    public $join;

    public $fieldsInvisibleAgent = array(
        'id_trunk',
        'idTrunktrunkcode',
    );

    public function init()
    {
        $this->instanceModel = new PhoneBook;
        $this->abstractModel = PhoneBook::model();
        $this->titleReport   = Yii::t('yii', 'Phone Book');

        parent::init();
    }

    public function extraFilterCustom($filter)
    {
        $filter .= ' AND id > 0';

        $filter .= !preg_match("/status/", $filter) ? ' AND status = 1' : false;

        if (Yii::app()->session['isOperator']) {
            $filter = $this->extraFilterCustomOperator($filter);
        } else if (Yii::app()->session['isTeam']) {
            $filter = $this->extraFilterCustomTeam($filter);
        }

        return $filter;
    }

    public function actionRead($asJson = true, $condition = null)
    {
        $filter       = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
        $filter       = $this->createCondition(json_decode($filter));
        $this->filter = !preg_match("/status/", $filter) ? ' AND status = 1' : '';
        parent::actionRead($asJson = true, $condition = null);
    }

    public function extraFilterCustomOperator($filter)
    {
        $this->join = 'JOIN pkg_campaign_phonebook uc ON t.id = uc.id_phonebook';

        $filter .= ' AND uc.id_campaign = ' . Yii::app()->session['id_campaign'];

        return $filter;
    }

}

/*
SELECT pkg_phonebook.name, pkg_phonebook.description
FROM  pkg_phonebook
INNER JOIN pkg_campaign_phonebook ON pkg_campaign_phonebook.id_phonebook = pkg_phonebook.id
INNER JOIN pkg_campaign ON pkg_campaign_phonebook.id_campaign = pkg_campaign.id
WHERE  pkg_campaign.id IN (2, 4)*/
