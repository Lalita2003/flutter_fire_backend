<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require "connect.php"; // connect.php สำหรับ pg_connect

// ตรวจสอบ connection
if (!$con) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed"
    ]);
    exit();
}

// ดึงข้อมูลผู้ใช้งาน พร้อม join กับตารางภูมิศาสตร์
$sql = "
SELECT 
    u.id,
    u.username,
    u.firstname,
    u.lastname,
    u.phone,
    u.village,
    u.agency,
    u.role,
    t.name_th AS subdistrict_name,
    a.name_th AS district_name,
    p.name_th AS province_name,
    u.created_at
FROM users u
LEFT JOIN thai_tambons t ON u.subdistrict_id = t.id
LEFT JOIN thai_amphures a ON u.district_id = a.id
LEFT JOIN thai_provinces p ON u.province_id = p.id
ORDER BY u.firstname ASC
";

$result = pg_query($con, $sql);
$users = [];

while ($row = pg_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode([
    "success" => true,
    "users" => $users
], JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
