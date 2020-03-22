<?php

/**
 *
 */
class CSVInterpreter
{
    private $filename;
    private $errors = array();
    private $delimiter;
    private $enclosure;
    private $escape;
    private $columns;

    public function __construct($filename, $delimiter = ";", $enclosure = "\"", $escape = "/")
    {

        if ($_REQUEST['delimiter']) {
            $delimiter = $_REQUEST['delimiter'];
        }

        if (file_exists($filename)) {
            $handle = fopen($filename, 'r');
            if (($line1 = fgets($handle)) !== false) {
                $this->columns = explode($delimiter, utf8_encode(trim(strtolower($line1))));
            } else {
                $this->addError('The file cannot be read');
            }
            fclose($handle);

        } else {
            $this->addError('File not found');
        }

        if (!preg_match("/$delimiter/", $line1)) {
            if (preg_match("/,|;/", $line1)) {
                $this->addError(Yii::t('yii', 'ERROR: CSV delimiter'));
            }
        }

        $this->filename  = $filename;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape    = $escape;
    }

    public function toArray()
    {
        $result = $this->getResultArray();

        return (count($this->errors) <= 0) ? $result : false;
    }

    private function getResultArray()
    {

        return ['columns' => $this->columns,
            'filename'        => $this->filename,
            'boundaries'      => [
                'delimiter' => $this->delimiter,
                'enclosure' => $this->enclosure,
                'escape'    => $this->escape,
            ],
        ];
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
