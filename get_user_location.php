<?php
// get_user_location.php
header("Content-Type: application/json");

require "connect.php"; // connect.php สำหรับ PostgreSQL

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user_id']);
    exit;
}

// Query ข้อมูลผู้ใช้ + จังหวัด อำเภอ ตำบล
$sql = "
SELECT 
    u.id, u.username, u.email,
    p.name_th AS province_name,
    d.name_th AS district_name,
    s.name_th AS subdistrict_name
FROM users u
LEFT JOIN thai_provinces p ON u.province_id = p.id
LEFT JOIN thai_amphures d ON u.district_id = d.id
LEFT JOIN thai_tambons s ON u.subdistrict_id = s.id
WHERE u.id = $1
";

pg_prepare($con, "get_user_location", $sql);
$result = pg_execute($con, "get_user_location", [$user_id]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

$data = pg_fetch_assoc($result);

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
