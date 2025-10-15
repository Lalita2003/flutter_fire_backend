<?php
require "connect.php"; // connect.php สำหรับ PostgreSQL

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

// รับค่าจาก Flutter
$user_id = $_POST['user_id'] ?? '';
$role = $_POST['role'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($user_id) || empty($role) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// แปลง user_id เป็น int เพื่อป้องกัน TypeError
$user_id = (int)$user_id;

// บันทึก log การ Logout
$action = 'logout';
$result = pg_prepare($con, "insert_log", 
    "INSERT INTO system_logs (user_id, action, target_type, description) VALUES ($1, $2, $3, $4)"
);

if ($result) {
    $exec = pg_execute($con, "insert_log", array($user_id, $action, $role, $description));
    if ($exec) {
        echo json_encode(['status' => 'success', 'message' => 'Logout log saved successfully']);
    } else {
        $error = pg_last_error($con);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save logout log', 'error' => $error]);
    }
} else {
    $error = pg_last_error($con);
    echo json_encode(['status' => 'error', 'message' => 'Failed to prepare query', 'error' => $error]);
}

pg_close($con);
?>
