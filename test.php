<?php

//Author: SONG WANG 
weatherDetailByCode(CAXX0547);
function weatherDetailByCode($city_code) {

	//date_default_timezone_set("Amecica/Winnipeg");
	//date_default_timezone_set(PRC);
	$cache_file_name = './cache' . $city_code . '.xml';
	$rss_url = 'http://rss.weather.com/weather/rss/local/' . $city_code . '?cm_ven=LWO&cm_cat=rss&par=LWO_rss';

	//look for cache
	if (file_exists($cache_file_name)) {
		$time = time();

		//compare timestamp 5 minutes
		if ($time - filemtime($cache_file_name) < 300) {
			$rss = simplexml_load_file($cache_file_name);
		} else {
			$rss = simplexml_load_file($rss_url);

			$rss -> asXML($cache_file_name);
		}
	} else {
		$rss = simplexml_load_file($rss_url);

		$rss -> asXML($cache_file_name);
	}
	//got title, got date
	$orig_title = $rss -> channel -> title;
	$orig_pubDate = $rss -> channel -> pubDate;

	//got city name in rss
	$pos = strpos($orig_title, "--");
	$city = substr($orig_title, $pos + 2);

	//echo $city, debug

	//format time into what we'd like to see
	$millis = strtotime($orig_pubDate);
	$orig_pubDate = date('Y-m-d H:i', $millis);

	$item = $rss -> channel -> item[0];

	$value = $item -> description;

	$pos = strpos($value, '&deg');
	//cut img
	$pos2 = strpos($value, '>');
	//change size
	$pos3 = strpos($value, '31/');
	
	$pos4 = strpos($value , 'gif');
	
	$pos5 = strpos($value, ',');
	
	$img_size = '72';
	
	$img_type = 'png';
	
	$img_dir_p1 = substr($value, 0, $pos3);
	
	$img_dir_p2 = substr($value, $pos3+2, $pos4-$pos3-2);
	
	$img_dir_p3 = substr($value, $pos4+3, $pos2+1 - $pos4-3);
	
	if($img_dir_p2 != '')
		$curr_img = $img_dir_p1."".$img_size."".$img_dir_p2."".$img_type."".$img_dir_p3;
	else
		$curr_img = '';

	$curr_cond = substr($value, $pos2 + 1, $pos5 - $pos2 -1);
	//temprature in f degree
	$curr_temp = substr($value, $pos - 3, 2);
	//f degree to c degree, ¡ãC=(¡ãF-32)*5/9
	$curr_temp = ($curr_temp - 32) * 5 / 9;
	if($curr_temp)		$curr_temp = sprintf("%4.1f", $curr_temp);	else		$curr_temp = "N/A";
	// format

	//EXTEND weather
	$ext_days = array("N/A","N/A","N/A");
	$ext_low_temp = array("N/A","N/A","N/A");
	$ext_high_temp = array("N/A","N/A","N/A");
	$ext_cond = array("N/A","N/A","N/A");

	$item = $rss -> channel -> item[6];

	$value = $item -> description;
	$value = str_replace("---", "?", $value);
	$token = strtok($value, "?");
	
	$i=0;
	while ($token != false) {
		echo "token ".$token."<br>";
		
		$pos = strpos($token, ':');
		if ($pos !== false)//cut unrelated info
		{
			$day = substr($token, 0, $pos);
			if($day)
				$ext_days[$i] = trim($day);
			else
				$ext_days[$i] = "N/A"; 
				
			echo "".$i."".$ext_days[i];
			$pos2 = strpos($token, ' & ');
			// '&' and 'space' together
			if ($pos2 !== false)//with condition info
			{
				$ext_cond[$i] = substr($token, $pos + 2, $pos2 - $pos - 2);

			} else {
				$ext_cond[$i] = 'N/A';
			}

			$pos3 = strpos($token, 'Low');
			if ($pos3 != false)//cut unrelated info
			{
				//temprature in f degree
				$temp = substr($token, $pos3 + 4, 2);
				//f degree to c degree, ¡ãC=(¡ãF-32)*5/9

				$temp = ($temp - 32) * 5 / 9;
				
				if($temp)					
					$ext_low_temp[$i] = sprintf("%4.1f", $temp);				
				else					
					$ext_low_temp[$i] = "N/A";
			} else {
				$ext_low_temp[$i] = "----";
			}
			$pos3 = strpos($token, 'High');
			if ($pos3 != false) {
				//temprature in f degree
				$temp = substr($token, $pos3 + 5, 2);
				//f degree to c degree, ¡ãC=(¡ãF-32)*5/9
				
				$temp = ($temp - 32) * 5 / 9;
										
				if($temp)
					$ext_high_temp[$i] = sprintf("%4.1f", $temp);				
				else					
					$ext_high_temp[$i] = "N/A";

			} else {
				$ext_high_temp[$i] = "----";
			}
			
		}
		$token = strtok("?");
		$i++;
	}//while
	
	if($ext_days[0]=='Tonight' && $ext_cond[0]=="")
		$ext_cond[0] = $curr_cond;
		
	//$curr_cond = translationEarlyLateParser(trim($curr_cond));
	//$ext_cond[0] = translationEarlyLateParser(trim($ext_cond[0]));
	//$ext_cond[1] = translationEarlyLateParser(trim($ext_cond[1]));
	//$ext_cond[2] = translationEarlyLateParser(trim($ext_cond[2]));


	$message = array('curr_img' => $curr_img, 'ext_days0' => $ext_days[0], 'ext_days1' => $ext_days[1], 'ext_days2' => $ext_days[2], 'curr_cond' => $curr_cond, 'ext_cond0' => $ext_cond[0], 'ext_cond1' => $ext_cond[1], 'ext_cond2' => $ext_cond[2], 'curr_temp' => $curr_temp, 'ext_low_temp0' => $ext_low_temp[0], 'ext_low_temp1' => $ext_low_temp[1], 'ext_low_temp2' => $ext_low_temp[2], 'ext_high_temp0' => $ext_high_temp[0], 'ext_high_temp1' => $ext_high_temp[1], 'ext_high_temp2' => $ext_high_temp[2]);
	echo json_encode($message) ; 
}

function translationSlashParser($value)
{	
	$final = "";
	$pos1 = strpos($value, '/');
	if($pos1 !== false)
	{
		$p1 = substr($value, 0, $pos1);
		$p2 = substr($value, $pos1+1);	
		$r1 = translationAMPMParser(trim($p1));
		$r2 = translationAMPMParser(trim($p2));
		$final = $r1.'/'.$r2;
	}
	else
		$final = translationAMPMParser(trim($value));

	return $final;
}

function translationAMPMParser($value)
{	
	$final = "";
	
	$pos1 = strpos($value, 'AM ');
	$pos2 = strpos($value, 'PM ');
	if($pos1 !== false || $pos2 !== false)
	{
		$p1 = substr($value, 0, 2);
		$p2 = substr($value, 3);
		$r1 = translationAndParser(trim($p1));
		$r2 = translationAndParser(trim($p2));
		$final = $r1.''.$r2;
	}
	else
		$final = translationAndParser($value);
	return $final;	
}

function translationAndParser($value)
{
	$final = "";
	
	$pos1 = strpos($value, 'and');

	if($pos1 !== false)
	{
		$p1 = substr($value, 0, $pos1-1);
		$p2 = substr($value, $pos1+3);
		$r1 = translation(trim($p1));
		$r2 = translation(trim($p2));
		$r3 = translation('and');
		$final = $r1.''.$r3.''.$r2;
	}
	else
		$final = translation($value);
	return $final;	

}
function translationEarlyLateParser($value)
{	
	$final = "";

	$pos1 = strpos($value, 'Early');
	$pos2 = strpos($value, 'Late');
	if($pos1 !== false && $pos2 === false)
	{
		$p1 = substr($value, 0, $pos1-1);
		$p2 = substr($value, $pos1);
		$r1 = translation(trim($p2));
		$r2 = translationSlashParser(trim($p1));
		$final = ($r1.''.$r2);
	}
	else if($pos2 !== false && $pos1 === false)
	{
		$p1 = substr($value, 0, $pos2-1);
		$p2 = substr($value, $pos2);
		$r1 = translation(trim($p2));
		$r2 = translationSlashParser(trim($p1));
		$final = ($r1.''.$r2);
	}
	else if($pos2 !== false && $pos1 !== false)
	{
		$p1 = substr($value, 0, $pos1+4+1);
		$r1 = translationEarlyLateParser($p1);
		$p2 = substr($value, $pos1+6);
		$r2 = translationEarlyLateParser($p2);
		$final = ($r1.''.$r2);
	}
	else
		$final = translationSlashParser(trim($value));
	return $final;
}
function translation($name)
{
	$condition_translation = array(
	"and" => "ºÍ",
	"PM" => "ÏÂÎç",
	"AM" => "ÉÏÎç",
	"Early" => "Ôç¼ä",
	"Late" => "Íí¼ä",
	"Clear" => "³Î¿ÕÎÞÔÆ",
	"Cloudy" => "ÔÆ",
	"Clouds" => "ÔÆ",
	"Clearing" => "·ÅÇç",
	"Drifting Snow" => "Æ®Ñ©",
	"Drizzle" => "Ã«Ã«Óê",
	"Dust" => "»Ò³¾ ",
	"Fair" => "Çç",
	"Few Showers" => "¶ÌÔÝÕóÓê",
	"Few Snow Showers" => "¶ÌÔÝÕóÑ©",
	"Fog" => "Îí",
	"Haze" => "±¡Îí",
	"Hail" => "±ù±¢",
	"Heavy Rain" => "´óÓê",
	"Heavy Rain Icy" => "´ó±ùÓê",
	"Heavy Snow" => "´óÑ© ",
	"Heavy T-Storm" => "Ç¿ÁÒÀ×Óê",
	"Isolated T-Storms" => "¾Ö²¿À×Óê",
	"Light Drizzle" => "Î¢Óê",
	"Light Rain" => "Ð¡Óê",
	"Light Rain Shower" => "Ð¡ÕóÓê",
	"Light Rain Shower and Windy" => "Ð¡ÕóÓê´ø·ç",
	"Light Rain with Thunder" => "Ð¡ÓêÓÐÀ×Éù",
	"Light Snow" => "Ð¡Ñ©",
	"Light Snow Fall" => "Ð¡½µÑ©",
	"Light Snow Grains" => "Ð¡Á£Ñ©",
	"Light Snow Shower" => "Ð¡ÕóÑ©",
	"Lightening" => "À×µç",
	"Mist" => "±¡Îí ",
	"Mostly Clear" => "´ó²¿ÇçÀÊ",
	"Mostly Cloudy" => "´ó²¿¶àÔÆ",
	"Mostly Sunny" => "ÇçÊ±¶àÔÆ",
	"Partly Cloudy" => "¾Ö²¿¶àÔÆ",
	"Rain" => "Óê",
	"Rain Shower" => "ÕóÓê",
	"Scattered Showers" => "ÁãÐÇÕóÓê",
	"Scattered Snow Showers" => "ÁãÐÇÕóÑ©",
	"Scattered Strong Storms" => "ÁãÐÇÇ¿·ç±©",
	"Scattered T-Storms" => "ÁãÐÇÀ×Óê",
	"Showers" => "ÕóÓê",
	"Showers Early" => "ÔçÓÐÕóÓê",
	"Showers Late" => "ÍíÓÐÕóÓê",
	"Showers in the Vicinity" => "ÖÜÎ§ÓÐÕóÓê",
	"Smoke" => "ÑÌÎí",
	"Snow" => "Ñ©",
	"Rain Icy Mix" => "±ùÓê»ìºÏ",
	"Snow Shower" => "ÕóÑ©",
	"Snow Showers" => "ÕóÑ©",
	"Snowflakes" => "Ñ©»¨",
	"Sunny" => "Ñô¹â",
	"Sunn" => "Ñô¹â",
	"Sunny Day" => "ÇçÌì",
	"Thunder" => "À×Ãù",
	"Thunder in the Vicinity" => "ÖÜÎ§ÓÐÀ×Óê",
	"T-Storms" => "À×Óê",
	"Wind" => "ÓÐ·ç",
	"Windy" => "ÓÐ·ç",
	"Snowy" => "ÓÐÑ©",
	"Snow to Rain" => "Ñ©×ªÓê",
	"Windy Rain" => "¹Î·çÏÂÓê",
	"Wintry Mix" =>"ÓêÑ©»ìºÏ",
	"Flurries" => "Ð¡Ñ©",
	"N" => "ÔÝÎÞ",
	"A" => "Êý¾Ý",
	);
	//echo $condition_translation[$name];
	if( $condition_translation[$name] != null)
		return $condition_translation[$name];

	else
		return $name;
}
?>

