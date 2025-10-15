<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require "connect.php"; // connect.php สำหรับ PostgreSQL

// ตรวจสอบการเชื่อมต่อ
if (!$con) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed"
    ]);
    exit;
}

// ดึงผู้ใช้ทั้งหมด พร้อมรหัสตำบล
$sql = "SELECT id, village, subdistrict_id FROM users WHERE role='user'";
$result = pg_query($con, $sql);

$users = [];
if ($result && pg_num_rows($result) > 0) {
    while($row = pg_fetch_assoc($result)) {
        $users[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "users" => $users
]);

pg_close($con);
?>
