<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// รับค่า POST
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$target_type = isset($_POST['target_type']) ? trim($_POST['target_type']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($user_id <= 0 || $action === '') {
    echo json_encode([
        "status" => "error",
        "message" => "ข้อมูลไม่ครบถ้วน (user_id หรือ action หาย)"
    ]);
    exit();
}

// ถ้า target_type ว่าง ให้ใช้ role จาก POST หรือ 'user' เป็น default
if ($target_type === '') $target_type = isset($_POST['role']) ? $_POST['role'] : 'user';

// Insert log
$sql = "INSERT INTO logs (user_id, action, target_type, description, log_time) 
        VALUES ($1, $2, $3, $4, NOW())";

$result = pg_query_params($conn, $sql, [$user_id, $action, $target_type, $description]);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "บันทึก Log เรียบร้อย"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "ไม่สามารถบันทึก Log ได้: " . pg_last_error($conn)
    ]);
}

pg_close($conn);
?>
