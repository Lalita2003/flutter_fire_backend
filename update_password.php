<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // $con เป็น pg_connect

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (!$id || !$current_password || !$new_password) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit();
}

// ดึงรหัสผ่านปัจจุบันจากฐานข้อมูล
$res = pg_query_params($con, "SELECT password FROM users WHERE id = $1", [$id]);
if (!$res || pg_num_rows($res) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบผู้ใช้']);
    exit();
}

$user = pg_fetch_assoc($res);
$hashed_password = $user['password'];

// ตรวจสอบรหัสผ่านปัจจุบัน
if (!password_verify($current_password, $hashed_password)) {
    echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
    exit();
}

// อัปเดตรหัสผ่านใหม่ (hash ก่อนเก็บ)
$new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$update = pg_query_params($con, "UPDATE users SET password = $1 WHERE id = $2", [$new_hashed_password, $id]);
if ($update) {
    echo json_encode(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านเรียบร้อย']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการอัปเดต']);
}

pg_close($con);
?>
