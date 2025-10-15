<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // pg_connect

$input = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่าเป็นการส่ง weather_logs
    if (!isset($input['weather_logs']) || !is_array($input['weather_logs'])) {
        echo json_encode([
            "status" => "error",
            "message" => "กรุณาส่ง weather_logs เป็น array"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $weatherLogs = $input['weather_logs'];
    $inserted = 0;
    $errors = [];

    foreach ($weatherLogs as $log) {
        $sql = "
            INSERT INTO weather_logs
            (burn_request_id, fetch_time, forecast_date, forecast_hour, temperature, humidity, wind_speed, boundary_height, pm25_model, created_at)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, CURRENT_TIMESTAMP)
            RETURNING id
        ";

        // เฉพาะตอนนี้ให้ burn_request_id = NULL
        $params = [
            null,
            $log['fetch_time'] ?? null,
            $log['forecast_date'] ?? null,
            $log['forecast_hour'] ?? null,
            $log['temperature'] ?? 0,
            $log['humidity'] ?? 0,
            $log['wind_speed'] ?? 0,
            $log['boundary_height'] ?? 0,
            $log['pm25_model'] ?? 0
        ];

        $res = pg_query_params($con, $sql, $params);
        if ($res) {
            $inserted++;
        } else {
            $errors[] = pg_last_error($con);
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Data saved",
        "result" => [
            "inserted" => $inserted,
            "errors" => $errors
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// ---------- Update burn_request_id หลังสร้าง burn_request ----------
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $burnRequestId = $input['burn_request_id'] ?? null;
    if (!$burnRequestId) {
        echo json_encode([
            "status" => "error",
            "message" => "กรุณาส่ง burn_request_id"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // อัปเดตเฉพาะ weather_logs ที่ burn_request_id ยังเป็น NULL
    $sql = "
        UPDATE weather_logs
        SET burn_request_id = $1
        WHERE burn_request_id IS NULL
    ";

    $res = pg_query_params($con, $sql, [$burnRequestId]);

    if ($res) {
        echo json_encode([
            "status" => "success",
            "message" => "weather_logs updated with burn_request_id",
            "updated_rows" => pg_affected_rows($res)
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => pg_last_error($con)
        ], JSON_UNESCAPED_UNICODE);
    }
}

pg_close($con);
?>
