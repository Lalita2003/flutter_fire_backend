<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require "connect.php"; // connect.php สำหรับ PostgreSQL

date_default_timezone_set('Asia/Bangkok');

// รับ user_id
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id == 0) {
    echo json_encode(['status'=>'error','message'=>'Missing user_id']);
    exit;
}

// ตรวจสอบเจ้าหน้าที่
$checkOfficer = pg_prepare($con, "check_officer", 
    "SELECT subdistrict_id, role FROM users WHERE id=$1 AND role='officer'"
);
$execOfficer = pg_execute($con, "check_officer", array($user_id));

if (!$execOfficer || pg_num_rows($execOfficer) == 0) {
    echo json_encode(['status'=>'error','message'=>'Officer not found']);
    exit;
}

$officer = pg_fetch_assoc($execOfficer);
$subdistrict_id = intval($officer['subdistrict_id']);

// -----------------------------
// 1. สรุป burn_requests สำหรับ TODAY
// -----------------------------
$sqlToday = "
SELECT 
    COUNT(CASE WHEN br.status='approved' AND (br.inspection_result IS NULL OR TRIM(br.inspection_result)='') THEN 1 END) AS checked,
    COUNT(CASE WHEN TRIM(br.inspection_result)='confirmed' AND DATE(br.inspection_datetime) = CURRENT_DATE THEN 1 END) AS confirmed,
    COUNT(CASE WHEN TRIM(br.inspection_result)='not_found' AND DATE(br.inspection_datetime) = CURRENT_DATE THEN 1 END) AS not_found,
    COUNT(CASE WHEN TRIM(br.inspection_result)='violation' AND DATE(br.inspection_datetime) = CURRENT_DATE THEN 1 END) AS violation
FROM burn_requests br
JOIN users u ON br.user_id = u.id
WHERE u.subdistrict_id = $1
";
$stmtToday = pg_prepare($con, "today_summary", $sqlToday);
$execToday = pg_execute($con, "today_summary", array($subdistrict_id));
$todayData = pg_fetch_assoc($execToday);

// -----------------------------
// 2. สรุป burn_requests รายเดือน
// -----------------------------
$sqlMonth = "
SELECT 
    COUNT(CASE WHEN br.status='approved' AND (br.inspection_result IS NULL OR TRIM(br.inspection_result)='') THEN 1 END) AS checked,
    COUNT(CASE WHEN TRIM(br.inspection_result)='confirmed' AND EXTRACT(MONTH FROM br.inspection_datetime) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM br.inspection_datetime) = EXTRACT(YEAR FROM CURRENT_DATE) THEN 1 END) AS confirmed,
    COUNT(CASE WHEN TRIM(br.inspection_result)='not_found' AND EXTRACT(MONTH FROM br.inspection_datetime) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM br.inspection_datetime) = EXTRACT(YEAR FROM CURRENT_DATE) THEN 1 END) AS not_found,
    COUNT(CASE WHEN TRIM(br.inspection_result)='violation' AND EXTRACT(MONTH FROM br.inspection_datetime) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM br.inspection_datetime) = EXTRACT(YEAR FROM CURRENT_DATE) THEN 1 END) AS violation
FROM burn_requests br
JOIN users u ON br.user_id = u.id
WHERE u.subdistrict_id = $1
";
$stmtMonth = pg_prepare($con, "month_summary", $sqlMonth);
$execMonth = pg_execute($con, "month_summary", array($subdistrict_id));
$monthData = pg_fetch_assoc($execMonth);

// -----------------------------
// 3. ส่งผลลัพธ์ JSON
// -----------------------------
echo json_encode([
    'status' => 'success',
    'subdistrict_id' => $subdistrict_id,
    'today' => [
        'checked' => intval($todayData['checked'] ?? 0),
        'confirmed' => intval($todayData['confirmed'] ?? 0),
        'not_found' => intval($todayData['not_found'] ?? 0),
        'violation' => intval($todayData['violation'] ?? 0),
    ],
    'month' => [
        'checked' => intval($monthData['checked'] ?? 0),
        'confirmed' => intval($monthData['confirmed'] ?? 0),
        'not_found' => intval($monthData['not_found'] ?? 0),
        'violation' => intval($monthData['violation'] ?? 0),
    ]
], JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
