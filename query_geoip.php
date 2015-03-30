<?php
//Author: SONG WANG for Longan Media
if($_SERVER['HTTP_ORIGIN'] == "http://weather.manitobacn.com")
{
	header('Access-Control-Allow-Origin: http://weather.manitobacn.com');	
}
include "/home1/parururu/public_html/weather/geoip/geoipcity.inc";
include "/home1/parururu/public_html/weather/geoip/geoipregionvars.php";
include "/home1/parururu/public_html/weather/geoip/timezone.php";

ip2detail();


function ip2detail() {

	$gi = geoip_open("../weather/geoip/GeoLiteCity.dat", GEOIP_STANDARD);

	$record = geoip_record_by_addr($gi, $_SERVER['REMOTE_ADDR']);
	
	$curr_city = strtoupper($record -> city);
	$curr_country = $record -> country_code;
	//echo $record->latitude . "\n";
	//echo $record->longitude . "\n";
	geoip_close($gi);
	//show city name after match for debug';
	//echo "<p>";
	//$record->region . " " . $GEOIP_REGION_NAME[$record->country_code][$record->region] . "\n";
	//$record->postal_code . "\n";
	
	$timezone = get_time_zone($record->country_code, $record->region);
	$message = array('ip_city' => $curr_city, 'ip_country' => $curr_country, 'ip_lat' => $record->latitude,  'ip_lng' => $record->longitude, 'timezone'=>$timezone, 'ip_region'=>$record->region);
	//echo 'Welcome, from ' . $record -> city .', '.$record -> country_name.' ';
	
	//if(strcmp($curr_country,'Canada')!=0)
	//{
		//$curr_city = 'OTTAWA';//upper case
		//echo "Sorry, currently we only support Canadian cities.";
	//}
	echo json_encode($message); 
}

?>
