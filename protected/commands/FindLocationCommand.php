<?php
class FindLocationCommand extends CConsoleCommand 
{

	public function run($args)
	{		
		$sql = "SELECT id, gps, address, city, number, state, zip_code, country FROM pkg_phonenumber WHERE  id_phonebook IN (SELECT id FROM pkg_phonebook WHERE find_location = 1 AND status = 1) AND address != '' AND gps = ''";
		$configResult   = Yii::app()->db->createCommand($sql)->queryAll();
		$id=1;
		foreach ($configResult as $key => $number) {
			
			if ($number['gps'] != '') {				
				continue;
			}
			
			$Address = urlencode($number['address'] . ' '. $number['city'] . ' '. $number['state'] . ' '. $number['zip_code']. ' '. $number['country']);

			if (strlen($Address) < 10) {
				continue;
			}		
			
			$request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=".$Address."&sensor=true";
			$xml = simplexml_load_file($request_url) or die("url not loading");
			$status = $xml->status;
			if ($status=="OK") {
				$Lat = $xml->result->geometry->location->lat;
				$Lon = $xml->result->geometry->location->lng;
				$LatLng = "$Lat|$Lon";

				$sql = "UPDATE pkg_phonenumber SET gps = '$LatLng' WHERE id = ". $number['id'];
				Yii::app()->db->createCommand($sql)->execute();
				
			}else{
				$sql = "UPDATE pkg_phonenumber SET gps = 'NotFound', address = '".$number['address']." (No Found)' WHERE id = ". $number['id'];
				Yii::app()->db->createCommand($sql)->execute();
			}
			
			

		}
				
	}
}