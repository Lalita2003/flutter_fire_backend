<?php
// ✅ อนุญาตให้เรียกข้ามโดเมน (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Handle preflight (CORS) request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'connect.php'; // ✅ ต้องใช้ pg_connect

// ✅ Debug log — ดูได้จาก Error Log ของ Server
error_log("📥 LOG POST DATA: " . print_r($_POST, true));

// ✅ รับข้อมูลจาก Flutter
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$target_type = isset($_POST['target_type']) ? trim($_POST['target_type']) : 'user';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if ($user_id <= 0 || $action === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'ข้อมูลไม่ครบถ้วน (user_id หรือ action หาย)'
    ]);
    exit;
}

// ✅ ตรวจสอบการเชื่อมต่อ DB
if (!$con) {
    echo json_encode([
        'status' => 'error',
        'message' => 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . pg_last_error()
    ]);
    exit;
}

// ✅ เตรียมคำสั่ง SQL
$sql = "INSERT INTO system_logs (user_id, action, target_type, description) 
        VALUES ($1, $2, $3, $4)";

$prepare = pg_prepare($con, "insert_log", $sql);

if (!$prepare) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare query ไม่สำเร็จ: ' . pg_last_error($con)
    ]);
    pg_close($con);
    exit;
}

$exec = pg_execute($con, "insert_log", [$user_id, $action, $target_type, $description]);

if ($exec) {
    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึก log สำเร็จ'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Execute query ไม่สำเร็จ: ' . pg_last_error($con)
    ]);
}

pg_close($con);
?>
