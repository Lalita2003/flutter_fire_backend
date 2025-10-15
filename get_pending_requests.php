<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php"; // connect.php สำหรับ PostgreSQL

if (!$con) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit;
}

// รับ villageHeadId ผ่าน GET
if (!isset($_GET['villageHeadId'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ต้องระบุ villageHeadId"]);
    exit;
}

$villageHeadId = intval($_GET['villageHeadId']);

// ดึงหมู่บ้านของผู้ใหญ่บ้าน
$sqlVillage = "SELECT village, role FROM users WHERE id = $1 LIMIT 1";
pg_prepare($con, "get_village", $sqlVillage);
$resultVillage = pg_execute($con, "get_village", [$villageHeadId]);

if (!$resultVillage || pg_num_rows($resultVillage) == 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใหญ่บ้าน"]);
    exit;
}

$userData = pg_fetch_assoc($resultVillage);
if ($userData['role'] !== 'village_head') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "ผู้ใช้ไม่ใช่ผู้ใหญ่บ้าน"]);
    exit;
}

$village = $userData['village'];

// ดึงคำขอที่ยังรอดำเนินการ และผู้ขออยู่ในหมู่บ้านเดียวกัน
$sql = "
SELECT 
    br.id,
    br.user_id,
    u.username,
    br.area_name,
    br.area_size,
    br.location_lat,
    br.location_lng,
    br.request_date,
    br.time_slot_from,
    br.time_slot_to,
    br.purpose,
    br.crop_type,
    br.status
FROM burn_requests AS br
INNER JOIN users AS u ON br.user_id = u.id
WHERE br.status = 'pending'
  AND u.village = $1
  AND u.role = 'user'
ORDER BY br.created_at DESC
";

pg_prepare($con, "get_pending_requests", $sql);
$result = pg_execute($con, "get_pending_requests", [$village]);

if (!$result) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาดในการดึงข้อมูล"]);
    exit;
}

$requests = [];
while ($row = pg_fetch_assoc($result)) {
    $requests[] = $row;
}

echo json_encode($requests, JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
