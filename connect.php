<?php
// ข้อมูลเชื่อมต่อ PostgreSQL จาก Neon
$db_host = "ep-still-tree-a147kofb-pooler.ap-southeast-1.aws.neon.tech";
$db_port = "5432"; // default PostgreSQL port
$db_name = "neondb";
$db_user = "neondb_owner";
$db_pass = "npg_AHf9TCMjU0on";

// สร้าง connection string
$conn_string = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass sslmode=require";

// เชื่อมต่อ PostgreSQL
$con = pg_connect($conn_string);

// ตรวจสอบการเชื่อมต่อ
if (!$con) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'Connection failed'
    ]);
    exit();
}

// ตั้ง header JSON UTF-8
header('Content-Type: application/json; charset=utf-8');
?>
