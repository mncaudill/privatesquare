<?php

	loadlib("privatesquare_export");

	##############################################################################

	function privatesquare_export_geojson($fh, $checkins, $more=array()){

		$features = array();

		$swlat = null;
		$swlon = null;
		$nelat = null;
		$nelon = null;

		foreach ($checkins as $row){

			# See notes in privatesquare_export_csv for why we're
			# doing this explicitly (20120227/straup)

			$_more = array(
				'inflate_weather' => 1,
			);

			privatesquare_export_massage_checkin($row, $_more);

			$lat = floatval($row['latitude']);
			$lon = floatval($row['longitude']);

			$swlat = (isset($swlat)) ? min($swlat, $lat) : $lat;
			$swlon = (isset($swlon)) ? min($swlon, $lon) : $lon;
			$nelat = (isset($nelat)) ? max($nelat, $lat) : $lat;
			$nelon = (isset($nelon)) ? max($nelon, $lon) : $lon;

			$features[] = array(
				'type' => 'Feature',
				'id' => $row['id'],
				'properties' => $row,
				'geometry' => array(
					'type' => 'Point',
					'coordinates' => array($lon, $lat),
				),
			);
		}

		$geojson = array(
			'type' => 'FeatureCollection',
			'bbox' => array($swlon, $swlat, $nelon, $nelat),
			'features' => $features,
		);

		fwrite($fh, json_encode($geojson));

		if (isset($more['donot_send'])){
			return okay();
		}

		$map = privatesquare_export_valid_formats();

		$headers = array(
			'Content-type' => $map['geojson'],
		);

		privatesquare_export_send($fh, $headers, $more);
	}

	##############################################################################
