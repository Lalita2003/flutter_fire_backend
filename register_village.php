<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// ตรวจสอบว่าเรียกด้วย POST เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require 'connect.php'; // ใช้ pg_connect ของ Neon

if (!$con) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

// รับข้อมูล JSON
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// ฟิลด์ที่ต้องตรวจสอบ
$required = ['username', 'firstname', 'lastname', 'phone', 'village', 'province_id', 'district_id', 'subdistrict_id', 'password'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(['status' => 'error', 'message' => "ข้อมูลไม่ครบถ้วน: $field"]);
        exit;
    }
}

// ถ้าไม่มี agency ให้เป็นค่าว่าง
$input['agency'] = $input['agency'] ?? '';

// เข้ารหัสรหัสผ่าน
$hashedPassword = password_hash($input['password'], PASSWORD_BCRYPT);

// แปลง ID เป็น integer
$province_id = intval($input['province_id']);
$district_id = intval($input['district_id']);
$subdistrict_id = intval($input['subdistrict_id']);
$created_at = date('Y-m-d H:i:s');
$role = 'village_head'; // ตั้ง role เป็น village_head

// ตรวจสอบ username ซ้ำ แบบไม่สน case
$sqlCheck = "SELECT COUNT(*) AS cnt FROM users WHERE LOWER(username) = LOWER($1)";
$resCheck = pg_query_params($con, $sqlCheck, [$input['username']]);
$row = pg_fetch_assoc($resCheck);
if (intval($row['cnt']) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว']);
    exit;
}

// เพิ่มผู้ใช้
$sqlInsert = "INSERT INTO users 
(username, firstname, lastname, phone, village, province_id, district_id, subdistrict_id, password, agency, role, created_at)
VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12)";

$params = [
    $input['username'],
    $input['firstname'],
    $input['lastname'],
    $input['phone'],
    $input['village'],
    $province_id,
    $district_id,
    $subdistrict_id,
    $hashedPassword,
    $input['agency'],
    $role,
    $created_at
];

$resInsert = pg_query_params($con, $sqlInsert, $params);

if ($resInsert) {
    echo json_encode(['status' => 'success', 'message' => 'สมัครผู้ใหญ่บ้านสำเร็จ']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . pg_last_error($con)]);
}

pg_close($con);
?>
