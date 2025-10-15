<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "connect.php"; // connect.php สำหรับ pg_connect

$input = json_decode(file_get_contents("php://input"), true);

// เขียน log สำหรับ debug
file_put_contents('log.txt', date('Y-m-d H:i:s') . " - " . json_encode($input) . "\n", FILE_APPEND);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ตรวจสอบฟิลด์จำเป็น
    $required = ["user_id","area_name","area_size","location_lat","location_lng","request_date","time_slot_from","purpose","crop_type"];
    foreach($required as $f){
        if(!isset($input[$f])){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing field $f"]);
            exit();
        }
    }

    // แปลงตัวเลขให้เป็น float/int เพื่อป้องกัน TypeError ใน Dart
    $user_id = intval($input['user_id']);
    $area_name = $input['area_name'];
    $area_size = floatval($input['area_size']);
    $location_lat = floatval($input['location_lat']);
    $location_lng = floatval($input['location_lng']);
    $request_date = $input['request_date'];
    $time_slot_from = $input['time_slot_from'];
    $purpose = $input['purpose'];
    $crop_type = $input['crop_type'];
    $status = 'pending';

    $sql = "INSERT INTO burn_requests 
        (user_id, area_name, area_size, location_lat, location_lng, request_date, time_slot_from, purpose, crop_type, status)
        VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10) RETURNING id";

    $params = [
        $user_id,
        $area_name,
        $area_size,
        $location_lat,
        $location_lng,
        $request_date,
        $time_slot_from,
        $purpose,
        $crop_type,
        $status
    ];

    $res = pg_query_params($con, $sql, $params);

    if($res){
        $burnRequestId = pg_fetch_result($res,0,'id');

        // UPDATE weather_logs ที่ burn_request_id ยังเป็น NULL
        $updateSql = "UPDATE weather_logs 
                      SET burn_request_id = $1 
                      WHERE burn_request_id IS NULL 
                      AND forecast_date = $2";
        pg_query_params($con, $updateSql, [$burnRequestId, $request_date]);

        echo json_encode([
            "success" => true,
            "message" => "Burn request created successfully and weather_logs updated",
            "id" => $burnRequestId
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => pg_last_error($con)
        ], JSON_UNESCAPED_UNICODE);
    }
}

// GET: ดึงข้อมูลทั้งหมด
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $res = pg_query($con, "SELECT * FROM burn_requests ORDER BY id DESC");
    $requests = [];
    while($row = pg_fetch_assoc($res)){
        // แปลงตัวเลขจาก string เป็น float/int เพื่อให้ Flutter ใช้งานได้ตรง
        if (isset($row['area_size'])) $row['area_size'] = floatval($row['area_size']);
        if (isset($row['location_lat'])) $row['location_lat'] = floatval($row['location_lat']);
        if (isset($row['location_lng'])) $row['location_lng'] = floatval($row['location_lng']);
        if (isset($row['user_id'])) $row['user_id'] = intval($row['user_id']);
        $requests[] = $row;
    }
    echo json_encode(["success" => true, "requests" => $requests], JSON_UNESCAPED_UNICODE);
}

pg_close($con);
?>
