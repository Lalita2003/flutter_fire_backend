<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // pg_connect

if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents("php://input"), true);

// ตรวจสอบฟิลด์ required
$required = ['username', 'firstname', 'lastname', 'phone', 'province_id', 'district_id', 'subdistrict_id', 'password'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(['status' => 'error', 'message' => "ข้อมูลไม่ครบถ้วน: $field"]);
        exit;
    }
}

// agency ถ้าไม่มี ให้เป็นค่าว่าง
$input['agency'] = $input['agency'] ?? '';
$village = ""; // admin ไม่มี village

// เข้ารหัสรหัสผ่าน
$hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);

// กำหนด role เป็น admin
$role = 'admin';

// ตรวจสอบ username ซ้ำ (ไม่ case-sensitive)
$sqlCheck = "SELECT COUNT(*) AS cnt FROM users WHERE LOWER(username) = LOWER($1)";
$resCheck = pg_query_params($con, $sqlCheck, [$input['username']]);
$row = pg_fetch_assoc($resCheck);
if (intval($row['cnt']) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว']);
    exit;
}

// เพิ่มผู้ใช้ประเภท admin
$sqlInsert = "INSERT INTO users 
(username, firstname, lastname, phone, village, province_id, district_id, subdistrict_id, password, agency, role, created_at)
VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,NOW())";

$params = [
    $input['username'],
    $input['firstname'],
    $input['lastname'],
    $input['phone'],
    $village,
    intval($input['province_id']),
    intval($input['district_id']),
    intval($input['subdistrict_id']),
    $hashedPassword,
    $input['agency'],
    $role
];

$resInsert = pg_query_params($con, $sqlInsert, $params);

if ($resInsert) {
    echo json_encode(['status' => 'success', 'message' => 'สมัครสมาชิกแอดมินสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . pg_last_error($con)]);
}

pg_close($con);
?>
