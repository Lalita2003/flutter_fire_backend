<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require 'connect.php'; // เชื่อมต่อ DB

$input = json_decode(file_get_contents("php://input"), true);

$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$action = isset($input['action']) ? trim($input['action']) : '';
$target_type = isset($input['target_type']) ? trim($input['target_type']) : 'user';
$description = isset($input['description']) ? trim($input['description']) : '';

if ($user_id > 0 && $action != '') {
    $stmt = $con->prepare("INSERT INTO system_logs (user_id, action, target_type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $target_type, $description);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
}
?>
