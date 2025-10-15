<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // $con เป็น pg_connect

// ตรวจสอบ id
if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่มีการส่ง userId มา']);
    exit();
}

$userId = intval($_POST['id']);
$username = trim($_POST['username'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$village = trim($_POST['village'] ?? '');
$agency = trim($_POST['agency'] ?? '');
$province_name = trim($_POST['province'] ?? '');
$district_name = trim($_POST['district'] ?? '');
$subdistrict_name = trim($_POST['subdistrict'] ?? '');

// ฟังก์ชันแปลงชื่อเป็น id
function getIdByName($con, $table, $name) {
    if (empty($name)) return null;
    $res = pg_query_params($con, "SELECT id FROM $table WHERE name_th = $1 LIMIT 1", [$name]);
    if ($res && pg_num_rows($res) > 0) {
        $row = pg_fetch_assoc($res);
        return intval($row['id']); // แปลงเป็น int ตรงนี้เลย
    }
    return null;
}

// แปลงชื่อเป็น id
$province_id = getIdByName($con, 'thai_provinces', $province_name);
$district_id = getIdByName($con, 'thai_amphures', $district_name);
$subdistrict_id = getIdByName($con, 'thai_tambons', $subdistrict_name);

// ตรวจสอบค่าที่จำเป็น
if (!$province_id || !$district_id || !$subdistrict_id) {
    echo json_encode(['status' => 'error', 'message' => 'จังหวัด/อำเภอ/ตำบลไม่ถูกต้อง']);
    exit();
}

if (empty($username) || empty($lastname)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกชื่อผู้ใช้และนามสกุล']);
    exit();
}

// UPDATE ข้อมูลผู้ใช้
$sql = "UPDATE users 
        SET username=$1, lastname=$2, phone=$3, village=$4, agency=$5, province_id=$6, district_id=$7, subdistrict_id=$8 
        WHERE id=$9";

$res = pg_query_params($con, $sql, [
    $username, $lastname, $phone, $village, $agency,
    $province_id, $district_id, $subdistrict_id,
    $userId
]);

if ($res) {
    echo json_encode([
        'status' => 'success',
        'message' => 'อัปเดตข้อมูลเรียบร้อย',
        'user_id' => $userId // ส่งกลับเป็น int ให้ Flutter ใช้ได้เลย
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => pg_last_error($con)]);
}

pg_close($con);
?>
