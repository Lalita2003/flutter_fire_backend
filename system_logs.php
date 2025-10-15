<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // $con เป็น pg_connect

// ตรวจสอบการเชื่อมต่อ
if (!$con) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Connection failed"]);
    exit();
}

// SQL ดึงข้อมูล system_logs เรียง log_time ล่าสุด
$sql = "SELECT id, user_id, action, target_type, description, 
        TO_CHAR(log_time, 'YYYY-MM-DD HH24:MI:SS') AS log_time
        FROM system_logs
        ORDER BY log_time DESC";

$res = pg_query($con, $sql);

$logs = [];

if ($res) {
    while ($row = pg_fetch_assoc($res)) {
        $logs[] = $row;
    }
    echo json_encode($logs, JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => pg_last_error($con)]);
}

pg_close($con);
?>
