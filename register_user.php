<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // pg_connect

// รับข้อมูลจาก POST หรือ JSON
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true);
}

$username      = trim($input['username'] ?? '');
$firstname     = trim($input['firstname'] ?? '');
$lastname      = trim($input['lastname'] ?? '');
$phone         = trim($input['phone'] ?? '');
$village       = trim($input['village'] ?? '');
$province_id   = $input['province_id'] ?? null;
$district_id   = $input['district_id'] ?? null;
$subdistrict_id= $input['subdistrict_id'] ?? null;
$agency        = trim($input['agency'] ?? '');
$password      = $input['password'] ?? '';
$role          = 'user'; // กำหนด role เริ่มต้น

// Validation เบื้องต้น
if (!$username || !$firstname || !$lastname || !$phone || !$village ||
    !$province_id || !$district_id || !$subdistrict_id || !$password) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ข้อมูลไม่ครบถ้วน',
    ]);
    exit;
}

// ตรวจสอบ username ซ้ำ
$sql_check = "SELECT COUNT(*) AS cnt FROM users WHERE LOWER(username) = LOWER($1)";
$res_check = pg_query_params($con, $sql_check, [$username]);
$row = pg_fetch_assoc($res_check);
if (intval($row['cnt']) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ชื่อผู้ใช้งานนี้ถูกใช้งานแล้ว',
    ]);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert ลง PostgreSQL
$sql = "INSERT INTO users 
    (username, firstname, lastname, phone, village, subdistrict_id, district_id, province_id, agency, password, role)
    VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)";

$res = pg_query_params($con, $sql, [
    $username,
    $firstname,
    $lastname,
    $phone,
    $village,
    intval($subdistrict_id),
    intval($district_id),
    intval($province_id),
    $agency ?: null,
    $hashed_password,
    $role
]);

if ($res) {
    echo json_encode([
        'status' => 'success',
        'message' => 'สมัครสมาชิกสำเร็จ'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . pg_last_error($con)
    ]);
}

pg_close($con);
?>
