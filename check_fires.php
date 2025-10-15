<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$request_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$radius = isset($_GET['radius']) ? floatval($_GET['radius']) : 2.0;

$MAP_KEY = "e6b58aed9c8bbf0d47dfbe2f155a9b6a";

$cache_dir = __DIR__ . '/cache';
if (!is_dir($cache_dir)) mkdir($cache_dir, 0755, true);
$cache_file = "$cache_dir/fires_{$lat}_{$lng}_{$request_date}.json";
$cache_time = 600;

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    echo file_get_contents($cache_file);
    exit;
}

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2)*sin($dLat/2) + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)*sin($dLon/2);
    $c = 2*atan2(sqrt($a), sqrt(1-$a));
    return $R*$c;
}

$fires = [];
$delta = 0.03;
$min_lat = $lat - $delta;
$max_lat = $lat + $delta;
$min_lon = $lng - $delta;
$max_lon = $lng + $delta;

for ($i=-3; $i<=3; $i++) {
    $date = date('Y-m-d', strtotime("$request_date $i days"));
    $url = "https://firms.modaps.eosdis.nasa.gov/api/area/csv/$MAP_KEY/VIIRS_SNPP_NRT/$min_lon,$min_lat,$max_lon,$max_lat/1/$date";
    
    if (($handle = @fopen($url,"r")) !== FALSE) {
        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== FALSE) {
            if (!$row || count($row) != count($header)) continue;
            $data = array_combine($header,$row);
            if (!$data) continue;

            if (is_numeric($data['confidence'])) $data['confidence'] = floatval($data['confidence']);
            else switch(strtoupper($data['confidence'])){
                case 'L': $data['confidence']=50; break;
                case 'N': $data['confidence']=75; break;
                case 'H': $data['confidence']=90; break;
                default: $data['confidence']=0; break;
            }
            if ($data['confidence'] < 50) continue;

            $fireLat = floatval($data['latitude']);
            $fireLon = floatval($data['longitude']);
            $distance = haversineDistance($lat,$lng,$fireLat,$fireLon);
            if ($distance > $radius) continue;

            $data['distance_km'] = round($distance,3);
            $fires[] = $data;
        }
        fclose($handle);
    }
}

$result = [
    'is_burning'=>count($fires)>0,
    'fires'=>$fires
];

file_put_contents($cache_file, json_encode($result));
echo json_encode($result);
?>
