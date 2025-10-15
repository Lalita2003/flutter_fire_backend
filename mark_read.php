<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // connect.php สำหรับ PostgreSQL

// รับค่า POST
$notification_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// ตรวจสอบการเชื่อมต่อ
if (!$con) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed"
    ]);
    exit;
}

// ตรวจสอบค่า
if ($notification_id <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid notification ID"
    ]);
    exit;
}

// ตรวจสอบว่า notification มีอยู่จริง
$sqlCheck = "SELECT id, is_read FROM notifications WHERE id=$1";
$stmtCheck = pg_prepare($con, "check_notification", $sqlCheck);
$resultCheck = pg_execute($con, "check_notification", array($notification_id));

if (!$resultCheck || pg_num_rows($resultCheck) === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Notification not found"
    ]);
    exit;
}

// อัปเดต is_read เป็น 1
$sqlUpdate = "UPDATE notifications SET is_read = 1 WHERE id=$1";
$stmtUpdate = pg_prepare($con, "update_notification", $sqlUpdate);
$execUpdate = pg_execute($con, "update_notification", array($notification_id));

if ($execUpdate) {
    echo json_encode([
        "success" => true,
        "message" => "Notification marked as read",
        "notification_id" => $notification_id
    ]);
} else {
    $err = pg_last_error($con);
    echo json_encode([
        "success" => false,
        "error" => $err
    ]);
}

pg_close($con);
?>
