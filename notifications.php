<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // connect.php สำหรับ PostgreSQL

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
$is_read = isset($_POST['is_read']) ? intval($_POST['is_read']) : 0;

// ตรวจสอบค่าที่จำเป็น
if ($user_id <= 0 || empty($title) || empty($message)) {
    echo json_encode(["success"=>false,"error"=>"Missing required fields"]);
    exit;
}

// ตรวจสอบผู้ใช้
$user_check_sql = "SELECT id FROM users WHERE id=$1";
$user_check_exec = pg_query_params($con, $user_check_sql, [$user_id]);

if (!$user_check_exec || pg_num_rows($user_check_exec) === 0) {
    echo json_encode(["success"=>false,"error"=>"User ID does not exist"]);
    exit;
}

// เพิ่ม notification
$sql_insert = "INSERT INTO notifications (user_id,title,message,status,is_read,created_at)
               VALUES ($1,$2,$3,$4,$5,NOW())
               RETURNING id";
$insert_exec = pg_query_params($con, $sql_insert, [$user_id,$title,$message,$status,$is_read]);

if ($insert_exec && pg_num_rows($insert_exec) > 0) {
    $row = pg_fetch_assoc($insert_exec);
    echo json_encode(["success"=>true,"notification_id"=>intval($row['id'])]);
} else {
    echo json_encode(["success"=>false,"error"=>pg_last_error($con) ?: "Insert failed"]);
}

pg_close($con);
?>
