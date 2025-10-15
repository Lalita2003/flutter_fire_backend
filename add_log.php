<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require 'connect.php'; // connect.php สำหรับ PostgreSQL

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$action = $_POST['action'] ?? '';
$target_type = $_POST['target_type'] ?? 'user';
$description = $_POST['description'] ?? '';

if ($user_id <= 0 || empty($action)) {
    echo json_encode(['status'=>'error','message'=>'ข้อมูลไม่ครบ']);
    exit;
}

// เตรียม query
$result = pg_prepare($conn, "insert_log", 
    "INSERT INTO system_logs (user_id, action, target_type, description) VALUES ($1, $2, $3, $4)"
);

if ($result) {
    $exec = pg_execute($conn, "insert_log", array($user_id, $action, $target_type, $description));
    if ($exec) {
        echo json_encode(['status'=>'success', 'message'=>'บันทึก log สำเร็จ']);
    } else {
        $err = pg_last_error($conn);
        echo json_encode(['status'=>'error', 'message'=>"Failed to execute query: $err"]);
    }
} else {
    $err = pg_last_error($conn);
    echo json_encode(['status'=>'error', 'message'=>"Failed to prepare query: $err"]);
}

pg_close($conn);
?>
