<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// อ่าน JSON body
$input = json_decode(file_get_contents('php://input'), true);

$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$action = isset($input['action']) ? trim($input['action']) : '';
$target_type = isset($input['target_type']) ? trim($input['target_type']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';

if ($user_id === 0 || $action === '') {
    echo json_encode([
        "status" => "error",
        "message" => "ข้อมูลไม่ครบถ้วน (user_id หรือ action หาย)"
    ]);
    exit();
}

$sql = "INSERT INTO logs (user_id, action, target_type, description, log_time) 
        VALUES ($1, $2, $3, $4, NOW())";

$result = pg_query_params($conn, $sql, [$user_id, $action, $target_type, $description]);

if ($result) {
    echo json_encode(["status" => "success", "message" => "บันทึก Log เรียบร้อย"]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "ไม่สามารถบันทึก Log ได้: " . pg_last_error($conn)
    ]);
}
?>
