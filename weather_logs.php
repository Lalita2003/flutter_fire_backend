<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // connect.php สำหรับ PostgreSQL (pg_connect)

// ดึง burn_request_id จาก query string
$burnRequestId = isset($_GET['burn_request_id']) ? intval($_GET['burn_request_id']) : null;

if (!$burnRequestId) {
    echo json_encode([
        "status" => "error",
        "message" => "กรุณาระบุ burn_request_id"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// SQL query
$sql = "
    SELECT id, burn_request_id, fetch_time, forecast_date, forecast_hour,
           temperature, humidity, wind_speed, boundary_height, pm25_model
    FROM weather_logs
    WHERE burn_request_id = $1
    ORDER BY forecast_hour ASC
";

// เตรียม query
$stmt = pg_prepare($con, "get_weather_logs", $sql);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to prepare query",
        "error" => pg_last_error($con)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Execute query
$result = pg_execute($con, "get_weather_logs", array($burnRequestId));

$weatherLogs = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        // แปลงค่า timestamp/time/date เป็น string ถ้ามี
        $row['fetch_time'] = $row['fetch_time'] ?? null;
        $row['forecast_date'] = $row['forecast_date'] ?? null;
        $row['forecast_hour'] = $row['forecast_hour'] ?? null;
        $weatherLogs[] = $row;
    }
}

// ส่งผลลัพธ์ JSON
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

// ปิดการเชื่อมต่อ
pg_close($con);
?>
