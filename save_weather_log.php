<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "connect.php"; // connect.php สำหรับ PostgreSQL

if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["weather_logs"]) || !is_array($data["weather_logs"])) {
    echo json_encode(["status" => "error", "message" => "No data provided"]);
    exit();
}

$logs = $data["weather_logs"];
$response = ["inserted" => 0, "errors" => []];

// เตรียม statement
$sql = "
INSERT INTO weather_logs 
(burn_request_id, fetch_time, forecast_date, forecast_hour, temperature, humidity, wind_speed, boundary_height, pm25_model, created_at)
VALUES (NULL, $1, $2, $3, $4, $5, $6, $7, $8, NOW())
";
$stmt = pg_prepare($con, "insert_weather_log", $sql);

foreach ($logs as $log) {
    $fetch_time      = $log["fetch_time"] ?? date("Y-m-d H:i:s");
    $forecast_date   = $log["forecast_date"] ?? "";
    $forecast_hour   = $log["forecast_hour"] ?? "";
    $temperature     = floatval($log["temperature"] ?? 0);
    $humidity        = floatval($log["humidity"] ?? 0);
    $wind_speed      = floatval($log["wind_speed"] ?? 0);
    $boundary_height = floatval($log["boundary_height"] ?? 0);
    $pm25_model      = floatval($log["pm25_model"] ?? 0);

    $exec = pg_execute($con, "insert_weather_log", [
        $fetch_time,
        $forecast_date,
        $forecast_hour,
        $temperature,
        $humidity,
        $wind_speed,
        $boundary_height,
        $pm25_model
    ]);

    if ($exec) {
        $response["inserted"]++;
    } else {
        $response["errors"][] = pg_last_error($con);
    }
}

pg_close($con);

echo json_encode([
    "status"  => "success",
    "message" => "Data saved",
    "result"  => $response
], JSON_UNESCAPED_UNICODE);
?>
