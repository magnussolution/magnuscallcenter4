<?php
class googleMapsController extends BaseController
{

    public $attributeOrder = 't.id';

    public function init()
    {

        parent::init();
    }
    public function actionRead($asJson = true, $condition = null)
    {
        ?>
		 <script type="text/javascript">
      	window.filters = <?php echo $_GET['filter'] ?>;
      	 </script>

		<!DOCTYPE html>
			<html lang="pt-br">
			    <head>
			        <meta charset="utf-8" />
			        <title>Google Maps</title>
			        <link rel="stylesheet" type="text/css" href="../../protected/extensions/GoogleMaps/css/estilo.css">
			    </head>

			    <body>
			    	<div id="mapa" style="height: 500px; width: 700px">
			        </div>

					<script src="../../protected/extensions/GoogleMaps/js/jquery.min.js"></script>

			        <!-- Maps API Javascript -->
			        <script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>

			        <!-- Caixa de informação -->
			        <script src="../../protected/extensions/GoogleMaps/js/infobox.js"></script>

			        <!-- Agrupamento dos marcadores -->
					<script src="../../protected/extensions/GoogleMaps/js/markerclusterer.js"></script>

			        <!-- Arquivo de inicialização do mapa -->
					<script>
					var map;
						var idInfoBoxAberto;
						var infoBox = [];
						var markers = [];

						function initialize() {
							var latlng = new google.maps.LatLng(-18.8800397, -47.05878999999999);

						    var options = {
						        zoom: 5,
								center: latlng,
						        mapTypeId: google.maps.MapTypeId.ROADMAP
						    };

						    map = new google.maps.Map(document.getElementById("mapa"), options);
						}

						initialize();

						function abrirInfoBox(id, marker) {
							if (typeof(idInfoBoxAberto) == 'number' && typeof(infoBox[idInfoBoxAberto]) == 'object') {
								infoBox[idInfoBoxAberto].close();
							}

							infoBox[id].open(map, marker);
							idInfoBoxAberto = id;
						}

						function carregarPontos() {

							$.getJSON('pontos?filter='+window.filters, function(pontos) {

								var latlngbounds = new google.maps.LatLngBounds();

								$.each(pontos, function(index, ponto) {

									var marker = new google.maps.Marker({
										position: new google.maps.LatLng(ponto.Latitude, ponto.Longitude),
										title: ponto.title,
										icon:  ponto.icon
									});

									var myOptions = {
										content: "<p>" + ponto.Descricao + "</p>",
										pixelOffset: new google.maps.Size(-150, 0)
						        	};

									infoBox[ponto.Id] = new InfoBox(myOptions);
									infoBox[ponto.Id].marker = marker;

									infoBox[ponto.Id].listener = google.maps.event.addListener(marker, 'click', function (e) {
										abrirInfoBox(ponto.Id, marker);
									});

									markers.push(marker);

									latlngbounds.extend(marker.position);

								});

								var markerCluster = new MarkerClusterer(map, markers);

								map.fitBounds(latlngbounds);

							});

						}

						carregarPontos();
						</script>
			    </body>
			</html>

		<?php
}

    public function actionPontos()
    {

        $pontos  = array();
        $filter  = json_decode($_GET['filter']);
        $filters = $this->createCondition($filter);

        $sql          = "SELECT id, gps,address, city, number, state,country, name FROM pkg_phonenumber WHERE  $filters AND gps != 'NotFound'";
        $configResult = Yii::app()->db->createCommand($sql)->queryAll();
        $id           = 1;
        foreach ($configResult as $key => $number) {

            if ($number['gps'] != '') {

                $gps = explode("|", $number['gps']);

                $pontos[] = (object) array(
                    "Id"        => $id,
                    "Latitude"  => $gps[0],
                    "Longitude" => $gps[1],
                    "Descricao" => $number['number'],
                    "icon"      => '../../protected/extensions/GoogleMaps/img/marcador.png',
                    "title"     => 'Cliente ' . $number['name'],
                );
                $id++;
                continue;

            } else {
            }

            $Address = urlencode($number['address'] . ' ' . $number['city'] . ' ' . $number['state'] . ' ' . $number['country']);

            if (strlen($Address) < 10) {
                continue;
            }

            $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $Address . "&sensor=true";
            $xml         = simplexml_load_file($request_url) or die("url not loading");
            $status      = $xml->status;
            if ($status == "OK") {
                $Lat    = $xml->result->geometry->location->lat;
                $Lon    = $xml->result->geometry->location->lng;
                $LatLng = "$Lat|$Lon";

                $sql = "UPDATE pkg_phonenumber SET gps = '$LatLng' WHERE id = " . $number['id'];
                Yii::app()->db->createCommand($sql)->execute();

                $pontos[] = (object) array(
                    "Id"        => $id,
                    "Latitude"  => "$Lat",
                    "Longitude" => "$Lon",
                    "Descricao" => $number['number'],
                    "icon"      => '../../protected/extensions/GoogleMaps/img/marcador.png',
                    "title"     => 'Cliente ' . $number['name'],
                );
                $id++;
            } else {
                $sql = "UPDATE pkg_phonenumber SET gps = 'NotFound', address = '" . $number['address'] . " (No Found)' WHERE id = " . $number['id'];
                Yii::app()->db->createCommand($sql)->execute();
            }

        }
        echo json_encode($pontos);

    }
    public function createCondition($filter)
    {
        $condition = '1';

        if (!count($filter)) {
            return $condition;
        }

        foreach ($filter as $f) {
            if (!isset($f->type)) {
                continue;
            }

            $type  = $f->type;
            $field = $f->field;
            $value = $f->value;

            $comparison = isset($f->comparison) ? $f->comparison : 'st';
            $comparison = isset($f->data->comparison) ? $f->data->comparison : $comparison;

            switch ($type) {
                case 'string':
                    switch ($comparison) {
                        case 'st':
                            $condition .= " AND $field LIKE '$value%'";
                            break;
                        case 'ed':
                            $condition .= " AND $field LIKE '%$value'";
                            break;
                        case 'ct':
                            $condition .= " AND $field LIKE '%$value%'";
                            break;
                        case 'eq':
                            $condition .= " AND $field LIKE '$value'";
                            break;
                    }
                    break;
                case 'boolean':
                    $value = (int) $value;
                    $condition .= " AND $field = $value";
                    break;
                case 'numeric':
                    switch ($comparison) {
                        case 'eq':
                            $condition .= " AND $field = $value";
                            break;
                        case 'lt':
                            $condition .= " AND $field < $value";
                            break;
                        case 'gt':
                            $condition .= " AND $field > $value";
                            break;
                    }
                    break;
                case 'datetime':
                    switch ($comparison) {
                        case 'eq':
                            $valueDateNow = explode(" ", $value);
                            $condition .= " AND $field LIKE '" . $valueDateNow[0] . "%'";
                            break;
                        case 'lt':
                            $condition .= " AND $field < '$value'";
                            break;
                        case 'gt':
                            $condition .= " AND $field > '$value'";
                            break;
                    }
                    break;
                case 'date':
                    switch ($comparison) {
                        case 'eq':
                            $valueDateNow = explode(" ", $value);
                            $condition .= " AND $field LIKE '" . $valueDateNow[0] . "%'";
                            break;
                        case 'lt':
                            $condition .= " AND $field < '$value'";
                            break;
                        case 'gt':
                            $condition .= " AND $field > '$value'";
                            break;
                    }
                    break;
                case 'list':
                    if (gettype($value[0]) !== 'integer') {
                        foreach ($value as &$v) {
                            $v = "'" . $v . "'";
                        }
                    }

                    $value = implode(',', $value);

                    if (isset($f->tableRelated)) {
                        $value = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere = $value";
                    }
                    $condition .= " AND $field IN($value)";
                    break;
                case 'notlist':
                    if (gettype($value[0]) !== 'integer') {
                        foreach ($value as &$v) {
                            $v = "'" . $v . "'";
                        }
                    }

                    $value = implode(',', $value);

                    if (isset($f->tableRelated)) {
                        $value = "SELECT DISTINCT $f->fieldSubSelect FROM $f->tableRelated WHERE $f->fieldWhere = $value";
                    }
                    $condition .= " AND $field NOT IN($value)";
                    break;
            }
        }
        return $condition;
    }
}
