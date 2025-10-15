<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php"; // connect.php สำหรับ PostgreSQL

if (!$con) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit;
}

// รับข้อมูลจาก POST
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$status = $_POST['status'] ?? null;
$approved_by = isset($_POST['approved_by']) ? intval($_POST['approved_by']) : null;
$approval_note = $_POST['approval_note'] ?? null;

if (!$id || !$status || !$approved_by) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ข้อมูลไม่ครบถ้วน"]);
    exit;
}

// ตรวจสอบว่าคำขอมีสถานะ pending
$sqlCheck = "SELECT status FROM burn_requests WHERE id = $1 LIMIT 1";
pg_prepare($con, "check_pending", $sqlCheck);
$resultCheck = pg_execute($con, "check_pending", [$id]);

if (!$resultCheck || pg_num_rows($resultCheck) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "คำขอไม่พบ"]);
    exit;
}

$row = pg_fetch_assoc($resultCheck);
if ($row['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "คำขอไม่สามารถอัปเดตได้ เนื่องจากไม่อยู่ในสถานะ pending"]);
    exit;
}

// อัปเดตสถานะคำขอ
$sqlUpdate = "
UPDATE burn_requests
SET status = $1,
    approved_by = $2,
    approval_note = $3,
    approved_at = NOW(),
    updated_at = NOW()
WHERE id = $4
";
pg_prepare($con, "update_burn_request", $sqlUpdate);
$execUpdate = pg_execute($con, "update_burn_request", [$status, $approved_by, $approval_note, $id]);

if ($execUpdate) {
    echo json_encode(["status" => "success", "message" => "อัปเดตสถานะสำเร็จ"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "อัปเดตสถานะล้มเหลว: " . pg_last_error($con)]);
}

pg_close($con);
?>
