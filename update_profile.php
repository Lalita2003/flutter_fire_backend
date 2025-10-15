<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // $con เป็น pg_connect

if (!isset($_POST['id'], $_POST['current_password'], $_POST['new_password'])) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ']);
    exit();
}

$userId = intval($_POST['id']); // มั่นใจว่าเป็น int
$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];

// ตรวจสอบรหัสผ่านปัจจุบัน
$res = pg_query_params($con, "SELECT password FROM users WHERE id=$1", [$userId]);
if (!$res || pg_num_rows($res) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบผู้ใช้']);
    exit();
}

$row = pg_fetch_assoc($res);
if ($row['password'] !== $currentPassword) { // ถ้าใช้ hash ต้องปรับตาม hash
    echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
    exit();
}

// UPDATE รหัสผ่านใหม่
$res = pg_query_params($con, "UPDATE users SET password=$1 WHERE id=$2", [$newPassword, $userId]);

if ($res) {
    echo json_encode([
        'status' => 'success',
        'message' => 'เปลี่ยนรหัสผ่านเรียบร้อย',
        'user_id' => $userId // ส่งกลับเป็น int ให้ Flutter ใช้
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => pg_last_error($con)]);
}

pg_close($con);
?>
