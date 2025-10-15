<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require 'connect.php'; // ใช้ pg_connect

if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

// รับข้อมูล POST (JSON)
$input = json_decode(file_get_contents("php://input"), true);

// ฟิลด์ที่ต้องตรวจสอบ
$required = ['username', 'firstname', 'lastname', 'phone', 'province_id', 'district_id', 'subdistrict_id', 'password'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(['status' => 'error', 'message' => "ข้อมูลไม่ครบถ้วน: $field"]);
        exit;
    }
}

// agency หรือ village
$input['agency'] = $input['agency'] ?? '';
$village = '';

// เข้ารหัสรหัสผ่าน
$hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);

$province_id = intval($input['province_id']);
$district_id = intval($input['district_id']);
$subdistrict_id = intval($input['subdistrict_id']);
$created_at = date('Y-m-d H:i:s');
$role = 'admin';

// ตรวจสอบ username ซ้ำ
$sqlCheck = "SELECT COUNT(*) AS cnt FROM users WHERE username = $1";
$res = pg_query_params($con, $sqlCheck, [$input['username']]);
$row = pg_fetch_assoc($res);
if ($row['cnt'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว']);
    exit;
}

// เพิ่มข้อมูลลงฐานข้อมูล
$sqlInsert = "INSERT INTO users 
(username, firstname, lastname, phone, village, province_id, district_id, subdistrict_id, password, agency, role, created_at)
VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12)";

$params = [
    $input['username'],
    $input['firstname'],
    $input['lastname'],
    $input['phone'],
    $village,
    $province_id,
    $district_id,
    $subdistrict_id,
    $hashedPassword,
    $input['agency'],
    $role,
    $created_at
];

$res = pg_query_params($con, $sqlInsert, $params);

if ($res) {
    echo json_encode(['status' => 'success', 'message' => 'สมัครสมาชิกแอดมินสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . pg_last_error($con)]);
}

pg_close($con);
?>
