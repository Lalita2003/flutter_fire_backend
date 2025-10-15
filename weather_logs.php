<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // pg_connect

$burnRequestId = $_GET['burn_request_id'] ?? null;

if (!$burnRequestId) {
    echo json_encode([
        "status" => "error",
        "message" => "กรุณาระบุ burn_request_id"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Query เอาข้อมูล weather_logs ทั้งหมดของ burn_request_id
$sql = "
    SELECT id, burn_request_id, fetch_time, forecast_date, forecast_hour,
           temperature, humidity, wind_speed, boundary_height, pm25_model
    FROM weather_logs
    WHERE burn_request_id = $1
    ORDER BY forecast_hour ASC
";

$res = pg_query_params($con, $sql, [$burnRequestId]);

if (!$res) {
    echo json_encode([
        "status" => "error",
        "message" => pg_last_error($con)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$weatherLogs = [];
while ($row = pg_fetch_assoc($res)) {
    $weatherLogs[] = $row;
}

if (!empty($weatherLogs)) {
    echo json_encode([
        "status" => "success",
        "weather_logs" => $weatherLogs
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "ไม่พบข้อมูลสภาพอากาศ"
    ], JSON_UNESCAPED_UNICODE);
}

pg_close($con);
?>
