<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php"; // connect.php สำหรับ PostgreSQL

// รับข้อมูลจาก POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($id <= 0 || empty($status)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "ข้อมูลไม่ครบถ้วน"]);
    exit;
}

// ตรวจสอบว่ามี notification ที่จะอัปเดตหรือไม่
$sqlCheck = "SELECT id FROM notifications WHERE id=$1";
$stmtCheck = pg_prepare($con, "check_notification", $sqlCheck);
$resultCheck = pg_execute($con, "check_notification", array($id));

if (!$resultCheck || pg_num_rows($resultCheck) === 0) {
    echo json_encode(["success" => false, "error" => "ไม่พบ notification"]);
    exit;
}

// อัปเดต status + รีเซ็ต is_read = 0
$sqlUpdate = "UPDATE notifications SET status=$1, is_read=0 WHERE id=$2";
$stmtUpdate = pg_prepare($con, "update_notification", $sqlUpdate);
$execUpdate = pg_execute($con, "update_notification", array($status, $id));

if ($execUpdate) {
    echo json_encode([
        "success" => true,
        "message" => "อัปเดต status สำเร็จ และรีเซ็ตเป็นยังไม่อ่าน"
    ]);
} else {
    http_response_code(500);
    $err = pg_last_error($con);
    echo json_encode(["success" => false, "error" => "อัปเดต status ล้มเหลว: $err"]);
}

pg_close($con);
?>
