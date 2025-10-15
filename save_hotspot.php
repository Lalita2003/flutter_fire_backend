<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require "connect.php"; // connect.php สำหรับ PostgreSQL

$burn_request_id = isset($_POST['burn_request_id']) ? intval($_POST['burn_request_id']) : 0;
$hotspot_time = $_POST['hotspot_time'] ?? '';
$lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
$lng = isset($_POST['lng']) ? floatval($_POST['lng']) : 0;
$confidence = isset($_POST['confidence']) ? floatval($_POST['confidence']) : 0.0;

if (!$burn_request_id || !$hotspot_time || !$lat || !$lng || !$confidence) {
    echo json_encode(['status'=>'error','message'=>'Missing required fields']);
    exit;
}

// เตรียม query
$result = pg_prepare($con, "insert_hotspot",
    "INSERT INTO satellite_hotspots 
     (burn_request_id, hotspot_time, lat, lng, confidence, created_at)
     VALUES ($1, $2, $3, $4, $5, NOW())"
);

if ($result) {
    $exec = pg_execute($con, "insert_hotspot", array($burn_request_id, $hotspot_time, $lat, $lng, $confidence));
    if ($exec) {
        echo json_encode(['status'=>'success']);
    } else {
        $err = pg_last_error($con);
        echo json_encode(['status'=>'error','message'=>"Failed to execute query: $err"]);
    }
} else {
    $err = pg_last_error($con);
    echo json_encode(['status'=>'error','message'=>"Failed to prepare query: $err"]);
}

pg_close($con);
?>
