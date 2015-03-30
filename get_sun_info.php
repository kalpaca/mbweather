<?php
$lat = $_GET['lat'];
$lng = $_GET['lng'];
$user_timezone = $_GET['timezone'];

$user_timezone = 'America/Winnipeg';
getSunInfo($lat, $lng, $user_timezone);
//calculateSunRiseSet(49.899, -97.137, 'America/Winnipeg');
function getSunInfo($lat, $lng, $user_timezone) {
	date_default_timezone_set('GMT');
	$gmt_time = date('Y-m-d H:i:s');
	date_default_timezone_set($user_timezone);
	$user_time = date('Y-m-d H:i:s');

	$gmt_offset = intval(date('Z')) / 3600;
	$zenith = 90+50/60;
	$sunrise = date_sunrise(time(), SUNFUNCS_RET_STRING, $lat, $lng,$zenith, $gmt_offset);

	$sunset = date_sunset(time(), SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$atime = time()+24*60*60;
	$sunrise2 = date_sunrise($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$sunset2 = date_sunset($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$atime = time()+48*60*60;
	$sunrise3 = date_sunrise($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$sunset3 = date_sunset($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$atime = time()+72*60*60;
	$sunrise4 = date_sunrise($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	$sunset4 = date_sunset($atime, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, $gmt_offset);
	if(intval(date("H"))>11)
	{
		$message = array('sunrise0' => $sunrise, 'sunset0' => $sunset,
		'sunrise1' => $sunrise, 'sunset1' => $sunset,
		'sunrise2' => $sunrise2, 'sunset2' => $sunset2,
		'sunrise3' => $sunrise3, 'sunset3' => $sunset3);
	}
	else
	{
		$message = array('sunrise0' => $sunrise, 'sunset0' => $sunset,
		'sunrise1' => $sunrise2, 'sunset1' => $sunset2,
		'sunrise2' => $sunrise3, 'sunset2' => $sunset3,
		'sunrise3' => $sunrise4, 'sunset3' => $sunset4);
	}
	echo json_encode($message);
}
?>