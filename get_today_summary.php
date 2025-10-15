<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require "connect.php"; // connect.php สำหรับ PostgreSQL

if (!isset($_GET['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing user_id"]);
    exit;
}

$userId = intval($_GET['user_id']);

// 1. ตรวจสอบว่าผู้ใช้เป็นผู้ใหญ่บ้าน
$sql = "SELECT village, role FROM users WHERE id = $1";
$stmt = pg_prepare($con, "check_user", $sql);
$result = pg_execute($con, "check_user", array($userId));

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}

$userData = pg_fetch_assoc($result);
if ($userData['role'] !== 'village_head') {
    echo json_encode(["status" => "error", "message" => "Not a village head"]);
    exit;
}

$village = $userData['village'];

// 2. ดึงจำนวนผู้ใช้ในหมู่บ้านเดียวกัน
$sql_users = "SELECT COUNT(*) AS total_users FROM users WHERE village=$1 AND role='user'";
$stmt_users = pg_prepare($con, "count_users", $sql_users);
$result_users = pg_execute($con, "count_users", array($village));
$total_users = intval(pg_fetch_assoc($result_users)['total_users'] ?? 0);

// 3. ดึงสรุปคำขอเผา วันนี้ + ล่วงหน้า 7 วัน
$sql_today = "
SELECT 
    SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN br.status = 'approved' THEN 1 ELSE 0 END) AS approved,
    SUM(CASE WHEN br.status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
    SUM(CASE WHEN br.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
FROM burn_requests br
JOIN users u ON br.user_id = u.id
WHERE u.village = $1
  AND br.request_date::date BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '7 days')
";
$stmt_today = pg_prepare($con, "today_summary", $sql_today);
$result_today = pg_execute($con, "today_summary", array($village));
$today_data = pg_fetch_assoc($result_today);

// 4. ดึงสรุปคำขอเผา เดือนนี้
$sql_month = "
SELECT 
    SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN br.status = 'approved' THEN 1 ELSE 0 END) AS approved,
    SUM(CASE WHEN br.status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
    SUM(CASE WHEN br.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
FROM burn_requests br
JOIN users u ON br.user_id = u.id
WHERE u.village = $1
  AND EXTRACT(MONTH FROM br.request_date) = EXTRACT(MONTH FROM CURRENT_DATE)
  AND EXTRACT(YEAR FROM br.request_date) = EXTRACT(YEAR FROM CURRENT_DATE)
";
$stmt_month = pg_prepare($con, "month_summary", $sql_month);
$result_month = pg_execute($con, "month_summary", array($village));
$month_data = pg_fetch_assoc($result_month);

// 5. ส่งผลลัพธ์กลับเป็น JSON
echo json_encode([
    "status" => "success",
    "village" => $village,
    "total_users" => $total_users,
    "today" => [
        "pending"  => intval($today_data['pending'] ?? 0),
        "approved" => intval($today_data['approved'] ?? 0),
        "rejected" => intval($today_data['rejected'] ?? 0),
        "cancelled"=> intval($today_data['cancelled'] ?? 0),
    ],
    "month" => [
        "pending"  => intval($month_data['pending'] ?? 0),
        "approved" => intval($month_data['approved'] ?? 0),
        "rejected" => intval($month_data['rejected'] ?? 0),
        "cancelled"=> intval($month_data['cancelled'] ?? 0),
    ]
], JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
