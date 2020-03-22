
<?php
/**
 * Acoes do modulo "Campaign".
 *
 * MagnusSolution.com <info@magnussolution.com>
 * 28/10/2012
 */

class BuyCreditController extends BaseController
{
    public $attributeOrder = 't.id';
    public $filterByUser   = false;

    public function init()
    {
        parent::init();
    }

    public function actionRead($asJson = true, $condition = null)
    {
        $wsdl = 'http://www.conexaorapida.com.br/wsFastConnect/fastConnect.php?wsdl';
        try {
            $client = new SoapClient($wsdl);
        } catch (Exception $e) {
            print_r($e);
        }

        $function = 'creditCardDirectSale';

        $arguments = array('clientCode' => '30015',
            'clientSerial'                  => 'e813b0d7e3cd82f1b880fb8c7eb828e4',
            'referenceNum'                  => 'magnus',
            'chargeTotal'                   => '15.00',
            'quota'                         => '0',
            'number'                        => '4532117091994146',
            'expYear'                       => '2016',
            'expMonth'                      => '12',
            'cvvNumber'                     => '424',
            'nameCreditCard'                => 'Adilson L Magnus',
            'flagCreditCard'                => 'visa',
            'name'                          => ' Adilson Leffa Magnus',
            'firstName'                     => 'Adilson',
            'docNumber'                     => '82627797034',
            'productCode'                   => 533);

        $options = array('location' => 'http://www.webservicex.net/ConvertTemperature.asmx');

        $result = $client->__soapCall($function, $arguments);

        echo 'Response: ';
        print_r($result);
        /*

    //30015
    //e813b0d7e3cd82f1b880fb8c7eb828e4

    //teste
    //4937
    //2az7jr5hi05ia94irrb74yjj

    $res = $client->creditCardDirectSale(array(
    'clientCode' => '30015',
    'clientSerial' => 'e813b0d7e3cd82f1b880fb8c7eb828e4',
    'referenceNum'=> 'magnus',
    'chargeTotal' => '15.00',
    'quota' => '0',
    'number' => '12341234123412341234',
    'expYear' => '2017',
    'expMonth' => '03',
    'cvvNumber' => '548',
    'nameCreditCard' => 'Adilson L Magnus',
    'flagCreditCard' => 'visa',
    'name' => ' Adilson Leffa Magnus',
    'firstName' => 'Adilson',
    'docNumber' => '82627797034',
    'productCode' => 533
    ));

    print_r($res);*/

    }

}