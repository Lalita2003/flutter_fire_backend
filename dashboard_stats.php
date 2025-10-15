<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include "connect.php"; // ไฟล์ connect.php ของ PostgreSQL

$totalUsers = 0;
$totalRequests = 0;

// ดึงจำนวนผู้ใช้
$resUsers = pg_query($con, "SELECT COUNT(*) AS total_users FROM users");
if ($resUsers && $row = pg_fetch_assoc($resUsers)) {
    $totalUsers = intval($row["total_users"]);
}

// ดึงจำนวนคำขอเผา
$resRequests = pg_query($con, "SELECT COUNT(*) AS total_requests FROM burn_requests");
if ($resRequests && $row = pg_fetch_assoc($resRequests)) {
    $totalRequests = intval($row["total_requests"]);
}

// รวม response ให้ Flutter อ่านง่าย
echo json_encode([
    "success" => true,
    "total_users" => $totalUsers,
    "total_requests" => $totalRequests
]);

pg_close($con);
?>
