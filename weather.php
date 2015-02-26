<?php
function realdegrees($in) {
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

$data = file_get_contents("D:\\vader\\downld02.txt");
$lfv_data = file_get_contents("http://www.lfv.se/MetInfo.asp?TextFile=llf.essa-s.txt&Subtitle=Omr%e5de%A0B%A0-%A0S%F6dra%A0delen&T=Omr%e5de%A0B%A0-%A0Mellersta%A0Sverige&Frequency=60");
$lfv_data = strip_tags($lfv_data);

preg_match_all('/I hela omr.det: ([0-9]{3})\/([0-9]+)([-+][0-9]+)/', $lfv_data, $lfv_matches); 

$data = preg_replace('/[ ]+/', ' ', $data);
$data = explode("\n", $data);
for($i = 3; $i < count($data); $i++) {
  $row = explode(" ", $data[$i]);
  if(count($row) < 10)
    continue;

  if($row[2] == '---')
    continue;
  $time = strtotime("20".$row[0]." ".$row[1]);
  $wind_mean = $row[7];
  $wind_dir = realdegrees($row[8]);
  $wind_max = $row[10];
  $temp = round($row[2]);
  
  $result[] = array("time" => $time, "temperature" => $temp,
    "wind" => array("mean" => $wind_mean, "max" => $wind_max,
                    "direction" => $wind_dir));
}

$lfv = null;
if (count($lfv_matches[2]) !== 0) {
  $lfv = array(
    "3000" => array("speed" => round($lfv_matches[2][2] * 0.514), 
                    "direction" => (int)$lfv_matches[1][2], 
                    "temperature" => (int)$lfv_matches[3][2]),
    "1500" => array("speed" => round($lfv_matches[2][1] * 0.514), 
                    "direction" => (int)$lfv_matches[1][1], 
                    "temperature" => (int)$lfv_matches[3][1]),
    "600" => array("speed" => round($lfv_matches[2][0] * 0.514), 
                    "direction" => (int)$lfv_matches[1][0], 
                    "temperature" => (int)$lfv_matches[3][0]));
}

$offset = 60 * 5;
header("Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT");
header("Cache-Control: max-age=$offset, must-revalidate"); 
echo json_encode(array("station" => $result, "lfv" => $lfv));
?>