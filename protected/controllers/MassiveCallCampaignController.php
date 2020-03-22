<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class MassiveCallCampaignController extends BaseController
{
    public $attributeOrder     = 't.id';
    public $extraValues        = array('idCampaign' => 'name');
    public $nameModelRelated   = 'MassiveCallCampaignPhonebook';
    public $nameFkRelated      = 'id_massive_call_campaign';
    public $nameOtherFkRelated = 'id_massive_call_phonebook';

    public function init()
    {
        $this->instanceModel        = new MassiveCallCampaign;
        $this->abstractModel        = MassiveCallCampaign::model();
        $this->abstractModelRelated = MassiveCallCampaignPhonebook::model();
        $this->titleReport          = Yii::t('yii', 'Campaign');

        parent::init();
    }

    public function beforeSave($values)
    {

        if (isset($_FILES["audio"]) && strlen($_FILES["audio"]["name"]) > 1) {
            $typefile        = explode('.', $_FILES["audio"]["name"]);
            $values['audio'] = "idMassiveCallCampaign_" . $values['id'] . '.' . $typefile[1];
        }

        if (isset($_FILES["audio_2"]) && strlen($_FILES["audio_2"]["name"]) > 1) {
            $typefile          = explode('.', $_FILES["audio_2"]["name"]);
            $values['audio_2'] = "idMassiveCallCampaign_" . $values['id'] . '_2.' . $typefile[1];
        }

        return $values;
    }

    public function afterSave($model, $values)
    {
        $uploaddir = $this->magnusFilesDirectory . 'sounds/';

        if (isset($_FILES["audio"]) && strlen($_FILES["audio"]["name"]) > 1) {
            if (file_exists($uploaddir . 'idMassiveCallCampaign_' . $model->id . '.wav')) {
                unlink($uploaddir . 'idMassiveCallCampaign_' . $model->id . '.wav');
            }
            $typefile   = explode('.', $_FILES["audio"]["name"]);
            $uploadfile = $uploaddir . 'idMassiveCallCampaign_' . $model->id . '.' . $typefile[1];
            move_uploaded_file($_FILES["audio"]["tmp_name"], $uploadfile);
        }
        if (isset($_FILES["audio_2"]) && strlen($_FILES["audio_2"]["name"]) > 1) {
            if (file_exists($uploaddir . 'idMassiveCallCampaign_' . $model->id . '_2.wav')) {
                unlink($uploaddir . 'idMassiveCallCampaign_' . $model->id . '_2.wav');
            }
            $typefile   = explode('.', $_FILES["audio_2"]["name"]);
            $uploadfile = $uploaddir . 'idMassiveCallCampaign_' . $model->id . '_2.' . $typefile[1];
            move_uploaded_file($_FILES["audio_2"]["tmp_name"], $uploadfile);
        }
    }

    public function getAttributesRequest()
    {
        $arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;

        /*permite salvar quando tem audio e extrafield*/
        $id_phonebook = array();
        foreach ($arrPost as $key => $value) {
            if ($key == 'id_massive_call_phonebook_array') {
                if (isset($_POST['id_massive_call_phonebook_array']) && strlen($value) > 0) {
                    $arrPost['id_massive_call_phonebook'] = explode(",", $_POST['id_massive_call_phonebook_array']);
                }

            }
        };

        return $arrPost;
    }
}
