<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require "connect.php"; // connect.php สำหรับ PostgreSQL

$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$user_id    = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($request_id <= 0 || $user_id <= 0) {
    echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบหรือไม่ถูกต้อง"]);
    exit;
}

// เตรียม query
$result = pg_prepare($con, "cancel_request", 
    "UPDATE burn_requests SET status='cancelled' WHERE id=$1 AND user_id=$2"
);

if ($result) {
    $exec = pg_execute($con, "cancel_request", array($request_id, $user_id));
    if ($exec) {
        echo json_encode(["success" => true]);
    } else {
        $err = pg_last_error($con);
        echo json_encode(["success" => false, "message" => "Failed to execute query: $err"]);
    }
} else {
    $err = pg_last_error($con);
    echo json_encode(["success" => false, "message" => "Failed to prepare query: $err"]);
}

pg_close($con);
?>
