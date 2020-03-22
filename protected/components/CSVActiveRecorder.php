<?php

/**
 *
 */
class CSVActiveRecorder
{
    private $translations;
    private $data;
    private $model;
    private $errors = array();
    private $aditionalParams;

    public function __construct(array $data, $model, $additionalParams = array())
    {

        $this->model = $model;
        if (isset($data['filename'])) {
            chmod($data['filename'], 0777);
        }

        $this->translations     = require __DIR__ . '/../../resources/locale/php/en/csv-translate.php';
        $this->data             = $data;
        $this->additionalParams = $additionalParams;
    }

    public function save()
    {

        $columns = $this->translateColumns();

        if ($this->validateColumns($columns) && count($this->errors) <= 0) {
            $this->data['columns']          = $columns;
            $this->data['additionalParams'] = $this->additionalParams;
        } else {
            return false;
        }

        $sql = $this->createSQL($this->model->tableName());

        try {
            Yii::app()->db->createCommand($sql)->execute();
            return true;
        } catch (CDBException $e) {
            $this->addError($e->getMessage());
            return false;
        }

    }

    private function createSQL($tableName)
    {

        /*
        LOAD DATA INFILE '/Users/macbookpro/Downloads/ABS.csv' INTO TABLE pkg_phonenumber CHARACTER SET UTF8  FIELDS TERMINATED BY ';' LINES TERMINATED BY '\r\n' IGNORE 1 LINES (@cpf,@nome,@banco,@agencia,@conta,@address,@Endereço_número,@cep,@bairro,@cidade,@estado,@number_home,@e_mail,@aniversário) SET cpf = @cpf,  name = @nome,  banco = @banco,  agencia = @agencia,  conta = @conta,  address = @address,  address_number = @Endereço_número,  zip_code = @cep,  neighborhood = @bairro,  city = @cidade,  state = @estado,  number_home = @number_home,  email = @e_mail,  birth_date = @aniversário,  id_phonebook = 54, id_category = 1
         */

        $sql = "LOAD DATA LOCAL INFILE '" . $this->data['filename'] . "'" .
        " INTO TABLE " . $tableName .
        " CHARACTER SET UTF8 " .
        " FIELDS TERMINATED BY '" . $this->data['boundaries']['delimiter'] . "'" .
            " LINES TERMINATED BY '\\r\\n' IGNORE 1 LINES";

        $sql .= " (" . implode(",", array_map(
            function ($v) {
                return '@' . $v;
            },
            array_keys($this->data['columns'])
        )
        ) . ")";

        $columns = $this->data['columns'];
        $sql .= " SET " . implode(" ", array_map(
            function ($v) use ($columns) {
                return $columns[$v] . " = @" . $v . ", ";
            },
            array_keys($this->data["columns"])
        )
        );
        if ($this->data["additionalParams"] > 0) {
            $sql .= " " . implode(" ", array_map(
                function ($v) {
                    return $v['key'] . " = " . $v['value'] . ',';
                },
                $this->additionalParams
            )
            );
        }

        $sql = substr(trim($sql), 0, -1);

        MagnusLog::insertLOG(3, $sql);

        return $sql;
    }

    public function translateColumns()
    {
        $csvColumns    = $this->data['columns'];
        $resultColumns = [];
        foreach ($csvColumns as $csvColumn) {
            $foundTranslation = false;
            foreach ($this->translations as $translateColumn => $translations) {
                if (in_array($csvColumn, $translations)) {
                    $resultColumns[$csvColumn] = $translateColumn;
                    $foundTranslation          = true;
                }
            }
            if (!$foundTranslation) {
                $resultColumns[$csvColumn] = $csvColumn;
            }

        }
        return $resultColumns;
    }
    private function validateColumns($columns)
    {
        $modelColumns = array_keys($this->model->getAttributes());
        if (count(array_intersect($columns, $modelColumns)) == count($columns)) {
            return true;
        } else {
            $this->addError("The columns are miscofigurated", -1);
            return false;
        }
    }

    private function addError($description)
    {
        $this->errors[] = $description;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
