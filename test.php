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
	//f degree to c degree, ��C=(��F-32)*5/9
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
				//f degree to c degree, ��C=(��F-32)*5/9

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
				//f degree to c degree, ��C=(��F-32)*5/9
				
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
	"and" => "��",
	"PM" => "����",
	"AM" => "����",
	"Early" => "���",
	"Late" => "���",
	"Clear" => "�ο�����",
	"Cloudy" => "��",
	"Clouds" => "��",
	"Clearing" => "����",
	"Drifting Snow" => "Ʈѩ",
	"Drizzle" => "ëë��",
	"Dust" => "�ҳ� ",
	"Fair" => "��",
	"Few Showers" => "��������",
	"Few Snow Showers" => "������ѩ",
	"Fog" => "��",
	"Haze" => "����",
	"Hail" => "����",
	"Heavy Rain" => "����",
	"Heavy Rain Icy" => "�����",
	"Heavy Snow" => "��ѩ ",
	"Heavy T-Storm" => "ǿ������",
	"Isolated T-Storms" => "�ֲ�����",
	"Light Drizzle" => "΢��",
	"Light Rain" => "С��",
	"Light Rain Shower" => "С����",
	"Light Rain Shower and Windy" => "С�������",
	"Light Rain with Thunder" => "С��������",
	"Light Snow" => "Сѩ",
	"Light Snow Fall" => "С��ѩ",
	"Light Snow Grains" => "С��ѩ",
	"Light Snow Shower" => "С��ѩ",
	"Lightening" => "�׵�",
	"Mist" => "���� ",
	"Mostly Clear" => "������",
	"Mostly Cloudy" => "�󲿶���",
	"Mostly Sunny" => "��ʱ����",
	"Partly Cloudy" => "�ֲ�����",
	"Rain" => "��",
	"Rain Shower" => "����",
	"Scattered Showers" => "��������",
	"Scattered Snow Showers" => "������ѩ",
	"Scattered Strong Storms" => "����ǿ�籩",
	"Scattered T-Storms" => "��������",
	"Showers" => "����",
	"Showers Early" => "��������",
	"Showers Late" => "��������",
	"Showers in the Vicinity" => "��Χ������",
	"Smoke" => "����",
	"Snow" => "ѩ",
	"Rain Icy Mix" => "������",
	"Snow Shower" => "��ѩ",
	"Snow Showers" => "��ѩ",
	"Snowflakes" => "ѩ��",
	"Sunny" => "����",
	"Sunn" => "����",
	"Sunny Day" => "����",
	"Thunder" => "����",
	"Thunder in the Vicinity" => "��Χ������",
	"T-Storms" => "����",
	"Wind" => "�з�",
	"Windy" => "�з�",
	"Snowy" => "��ѩ",
	"Snow to Rain" => "ѩת��",
	"Windy Rain" => "�η�����",
	"Wintry Mix" =>"��ѩ���",
	"Flurries" => "Сѩ",
	"N" => "����",
	"A" => "����",
	);
	//echo $condition_translation[$name];
	if( $condition_translation[$name] != null)
		return $condition_translation[$name];

	else
		return $name;
}
?>

