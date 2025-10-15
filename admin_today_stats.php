<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // $con เป็น pg_connect

$today = date('Y-m-d');

// ผู้ใช้งานใหม่วันนี้
$res1 = pg_query_params($con, "SELECT COUNT(*) AS count FROM users WHERE created_at::date = $1", [$today]);
$new_users = intval(pg_fetch_result($res1, 0, 'count'));

// คำขอเผาใหม่วันนี้
$res2 = pg_query_params($con, "SELECT COUNT(*) AS count FROM burn_requests WHERE created_at::date = $1", [$today]);
$new_requests = intval(pg_fetch_result($res2, 0, 'count'));

// ผู้ใช้งานเข้าสู่ระบบวันนี้
$res3 = pg_query_params($con, "SELECT COUNT(*) AS count FROM system_logs WHERE action='login' AND log_time::date = $1", [$today]);
$logins = intval(pg_fetch_result($res3, 0, 'count'));

echo json_encode([
    'success' => true,
    'new_users' => $new_users,
    'new_requests' => $new_requests,
    'logins' => $logins
]);

pg_close($con);
?>
