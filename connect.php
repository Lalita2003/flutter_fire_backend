<?php
ini_set('display_errors', 0);
error_reporting(0);

// อ่านค่าจาก Environment Variables
$db_host = getenv('DB_HOST');      // เช่น ep-still-tree-a147kofb-pooler.ap-southeast-1.aws.neon.tech
$db_port = getenv('DB_PORT');      // 5432
$db_name = getenv('DB_NAME');      // neondb
$db_user = getenv('DB_USER');      // neondb_owner
$db_pass = getenv('DB_PASS');      // npg_AHf9TCMjU0on

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
