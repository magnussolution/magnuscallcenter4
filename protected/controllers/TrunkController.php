<?php
/**
 * Acoes do modulo "Trunk".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 23/06/2012
 */

class TrunkController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idProvider' => 'provider_name');

    public function init()
    {
        $this->instanceModel = new Trunk;
        $this->abstractModel = Trunk::model();
        $this->titleReport   = Yii::t('yii', 'Trunk');
        parent::init();
    }

    public function beforeSave($values)
    {
        if (Yii::app()->session['isOperator']) {
            $values['id_user'] = Yii::app()->session['id_user'];
        } else if (Yii::app()->session['isTeam']) {

            if ($this->isNewRecord) {

                $modelTeamTrunk = TeamTrunk::model()->find('id_team = :key', array(':key' => Yii::app()->session['id_team']));
                if (count($modelTeamTrunk)) {
                    echo json_encode(array(
                        $this->nameSuccess => false,
                        'errors'           => 'Voce nao pode criar troncos',

                    ));
                    exit;
                }
            }

            $values['id_team'] = Yii::app()->session['id_team'];
        }

        if (isset($values['allow'])) {
            $values['allow'] = preg_replace("/,0/", "", $values['allow']);
            $values['allow'] = preg_replace("/0,/", "", $values['allow']);

        }
        return $values;
    }

    public function extraFilterCustomTeam($filter)
    {

        $modelTeamTrunk = TeamTrunk::model()->find('id_team = :key', array(':key' => Yii::app()->session['id_team']));

        if (count($modelTeamTrunk)) {
            //only allow use trunks from team
            $filter .= ' AND t.id IN (SELECT id_trunk FROM pkg_team_trunk WHERE id_team = ' . Yii::app()->session['id_team'] . ')';
        } else {
            //allow create trunks
            $filter .= ' AND t.id_team = :clfby ';
        }

        $this->paramsFilter[':clfby'] = Yii::app()->session['id_team'];

        return $filter;
    }

    public function generatePjSipFile()
    {
        $select = 'trunkcode, user, secret, disallow, allow, directmedia, context, maxuse, dtmfmode, insecure, nat, qualify, type, host,fromdomain,fromuser, register';
        $model  = Trunk::model()->findAll(
            array(
                'select'    => $select,
                'condition' => 'status =1',
            ));

        if (is_array($model) > 0) {
            AsteriskAccess::instance()->writeAsteriskFile($model, '/etc/asterisk/pjsip_magnus.conf', 'trunkcode');

        }
    }

    public function afterSave($model, $values)
    {
        $this->generatePjSipFile();
    }
    public function afterDestroy($values)
    {
        $this->generatePjSipFile();
    }

    public function setAttributesModels($attributes, $models)
    {
        $trunkRegister = AsteriskAccess::instance()->pjsipListRegistrations();

        if (!isset($trunkRegister['data'])) {
            return $attributes;
        }

        $trunkRegister = explode("\n", $trunkRegister['data']);

        for ($i = 0; $i < count($attributes) && is_array($attributes); $i++) {
            $modelTrunk                                = Trunk::model()->findByPk((int) $attributes[$i]['failover_trunk']);
            $attributes[$i]['failover_trunktrunkcode'] = count($modelTrunk)
            ? $modelTrunk->trunkcode
            : Yii::t('yii', 'undefined');
            foreach ($trunkRegister as $key => $trunk) {
                if (preg_match("/" . $attributes[$i]['host'] . ".*" . $attributes[$i]['user'] . ".*Registered/", $trunk) && $attributes[$i]['providertech'] == 'pjsip') {
                    $attributes[$i]['registered'] = 1;
                    break;
                }
            }

        }

        return $attributes;
    }
}
