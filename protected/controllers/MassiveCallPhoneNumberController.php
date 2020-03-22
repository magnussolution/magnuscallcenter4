<?php
/**
 * Acoes do modulo "PhoneNumber".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class MassiveCallPhoneNumberController extends BaseController
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idMassiveCallPhonebook' => 'name');

    public $fieldsFkReport = array(
        'id_phonebook' => array(
            'table'       => 'pkg_phonebook',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
    );

    public function init()
    {
        $this->instanceModel = new MassiveCallPhoneNumber;
        $this->abstractModel = MassiveCallPhoneNumber::model();
        $this->titleReport   = Yii::t('yii', 'Massive Call Phone Number');

        parent::init();
    }
    public function importCsvSetAdditionalParams()
    {
        $values = $this->getAttributesRequest();
        return [
            ['key' => 'id_massive_call_phonebook', 'value' => $values['id_massive_call_phonebook']],
        ];
    }

    public function afterImportFromCsv($values)
    {
        if ($values['allowDuplicate'] == 1) {
            //remove duplicates in the phonebook
            $sql    = "SELECT id FROM pkg_massive_call_phonenumber WHERE id_massive_call_phonebook = " . $values['id_massive_call_phonebook'] . " GROUP BY number HAVING  COUNT(number) > 1";
            $result = Yii::app()->db->createCommand($sql)->queryAll();
            $ids    = '';
            foreach ($result as $key => $value) {
                $ids .= $value['id'] . ',';
            }

            $sql = "DELETE FROM pkg_massive_call_phonenumber WHERE id IN (" . substr($ids, 0, -1) . ")";
            try {
                Yii::app()->db->createCommand($sql)->execute();
            } catch (Exception $e) {

            }
        }
        return;
    }

}
