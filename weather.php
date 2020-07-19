<?php

require_once 'config.php';

function realdegrees($in) {
  $wd_deg = null;
  switch ($in) {
    case "N":   { $wd_deg = 0; break; }
    case "NNE": { $wd_deg = 22; break; }
    case "NE":  { $wd_deg = 45; break; }
    case "ENE": { $wd_deg = 67 ; break; }
    case "E":   { $wd_deg = 90; break; }
    case "ESE": { $wd_deg = 112; break; }
    case "SE":  { $wd_deg = 135; break; }
    case "SSE": { $wd_deg = 157; break; }
    case "S":   { $wd_deg = 180; break; }
    case "SSW": { $wd_deg = 202; break; }
    case "SW":  { $wd_deg = 225; break; }
    case "WSW": { $wd_deg = 247; break; }
    case "W":   { $wd_deg = 270; break; }
    case "WNW": { $wd_deg = 292; break; }
    case "NW":  { $wd_deg = 315; break; }
    case "NNW": { $wd_deg = 337; break; }
  }
  return $wd_deg;
}

$json = file_get_contents("https://api.holfuy.com/archive/?pw=" . HOLFUY_PWD . "&s=761&su=m/s&mback=60");
$lfv_data = file_get_contents("./cron/lfv-weather.html"); // Is fetched every 60 minutes by a scheduled task running cron/lfv.py

$lfv_data = strstr($lfv_data, "Delomr&#229;de 4</h1>");
$lfv_data = strip_tags($lfv_data);

preg_match_all('/([0-9]{2})-([0-9]{2})UTC: ([0-9]+)\/([0-9]+)kt ([-+][0-9]+)/', $lfv_data, $lfv_matches);

$data = json_decode($json, true);

foreach(array_reverse($data["measurements"]) as $item) { 
  $time = strtotime($item['dateTime'] . ' ' . 'Europe/Stockholm');
  $wind_mean = $item['wind']['speed'];
  $wind_dir = $item['wind']['direction'];
  $wind_max = $item['wind']['gust'];
  $temp = round($item['temperature']);
  
  $result[] = array("time" => $time, "temperature" => $temp,
    "wind" => array("mean" => $wind_mean, "max" => $wind_max,
                    "direction" => $wind_dir));
}

$lfv = null;
if (count($lfv_matches[2]) !== 0) {
  $lfv_result = array();
  for ($i = 0; $i < count($lfv_matches[0]); $i++) {
    if (intval(gmdate('H')) >= $lfv_matches[1][$i] && intval(gmdate('H')) < intval($lfv_matches[2][$i])) {
      $lfv_result[] = array($lfv_matches[4][$i], $lfv_matches[3][$i], $lfv_matches[5][$i]);
    }
  }

  $lfv = array(
    "3000" => array("speed" => round($lfv_result[2][0] * 0.514), 
                    "direction" => (int)$lfv_result[2][1], 
                    "temperature" => (int)$lfv_result[2][2]),
    "1500" => array("speed" => round($lfv_result[1][0] * 0.514), 
                    "direction" => (int)$lfv_result[1][1], 
                    "temperature" => (int)$lfv_result[1][2]),
    "600" => array("speed" => round($lfv_result[0][0] * 0.514), 
                    "direction" => (int)$lfv_result[0][1], 
                    "temperature" => (int)$lfv_result[0][2]));
}

$offset = 60;
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
header("Cache-Control: max-age=$offset, must-revalidate"); 
echo json_encode(array("station" => $result, "lfv" => $lfv));
?>
