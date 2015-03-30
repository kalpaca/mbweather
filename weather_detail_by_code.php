<?php

//Author: SONG WANG 

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
	//f degree to c degree, °C=(°F-32)*5/9
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
		
		$pos = strpos($token, ':');
		if ($pos !== false)//cut unrelated info
		{
			$day = substr($token, 0, $pos);
			if($day)
				$ext_days[$i] = trim($day);
			else
				$ext_days[$i] = "N/A"; 
			//echo $token;
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
				//f degree to c degree, °C=(°F-32)*5/9

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
				//f degree to c degree, °C=(°F-32)*5/9
				
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
	"and" => "和",
	"PM" => "下午",
	"AM" => "上午",
	"Early" => "早间",
	"Late" => "晚间",
	"Clear" => "澄空无云",
	"Cloudy" => "云",
	"Clouds" => "云",
	"Clearing" => "放晴",
	"Drifting Snow" => "飘雪",
	"Drizzle" => "毛毛雨",
	"Dust" => "灰尘 ",
	"Fair" => "晴",
	"Few Showers" => "短暂阵雨",
	"Few Snow Showers" => "短暂阵雪",
	"Fog" => "雾",
	"Haze" => "薄雾",
	"Hail" => "冰雹",
	"Heavy Rain" => "大雨",
	"Heavy Rain Icy" => "大冰雨",
	"Heavy Snow" => "大雪 ",
	"Heavy T-Storm" => "强烈雷雨",
	"Isolated T-Storms" => "局部雷雨",
	"Light Drizzle" => "微雨",
	"Light Rain" => "小雨",
	"Light Rain Shower" => "小阵雨",
	"Light Rain Shower and Windy" => "小阵雨带风",
	"Light Rain with Thunder" => "小雨有雷声",
	"Light Snow" => "小雪",
	"Light Snow Fall" => "小降雪",
	"Light Snow Grains" => "小粒雪",
	"Light Snow Shower" => "小阵雪",
	"Lightening" => "雷电",
	"Mist" => "薄雾 ",
	"Mostly Clear" => "大部晴朗",
	"Mostly Cloudy" => "大部多云",
	"Mostly Sunny" => "晴时多云",
	"Partly Cloudy" => "局部多云",
	"Rain" => "雨",
	"Rain Shower" => "阵雨",
	"Scattered Showers" => "零星阵雨",
	"Scattered Snow Showers" => "零星阵雪",
	"Scattered Strong Storms" => "零星强风暴",
	"Scattered T-Storms" => "零星雷雨",
	"Showers" => "阵雨",
	"Showers Early" => "早有阵雨",
	"Showers Late" => "晚有阵雨",
	"Showers in the Vicinity" => "周围有阵雨",
	"Smoke" => "烟雾",
	"Snow" => "雪",
	"Rain Icy Mix" => "冰雨混合",
	"Snow Shower" => "阵雪",
	"Snow Showers" => "阵雪",
	"Snowflakes" => "雪花",
	"Sunny" => "阳光",
	"Sunn" => "阳光",
	"Sunny Day" => "晴天",
	"Thunder" => "雷鸣",
	"Thunder in the Vicinity" => "周围有雷雨",
	"T-Storms" => "雷雨",
	"Wind" => "有风",
	"Windy" => "有风",
	"Snowy" => "有雪",
	"Snow to Rain" => "雪转雨",
	"Windy Rain" => "刮风下雨",
	"Wintry Mix" =>"雨雪混合",
	"Flurries" => "小雪",
	"N" => "暂无",
	"A" => "数据",
	);
	//echo $condition_translation[$name];
	if( $condition_translation[$name] != null)
		return $condition_translation[$name];

	else
		return $name;
}
?>

