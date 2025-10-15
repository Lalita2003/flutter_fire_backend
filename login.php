<?php
require "connect.php"; // connect.php ที่ใช้ pg_connect

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// รับค่าจาก Flutter
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล (prepared statement)
$result = pg_query_params(
    $con,
    "SELECT id, username, password, role, firstname, lastname FROM users WHERE username=$1",
    [$username]
);

if (pg_num_rows($result) == 1) {
    $user = pg_fetch_assoc($result);

    // ตรวจสอบรหัสผ่าน
    if (password_verify($password, $user['password'])) {

        // กำหนด description ตาม role และ username
        $description = $user['username'] . " เข้าสู่ระบบสำเร็จ";

        // บันทึก log ลงตาราง system_logs
        pg_query_params(
            $con,
            "INSERT INTO system_logs (user_id, action, target_type, description) VALUES ($1, $2, $3, $4)",
            [$user['id'], 'login', $user['role'], $description]
        );

        // ส่งข้อมูลกลับไป Flutter
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
