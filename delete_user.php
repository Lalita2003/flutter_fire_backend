<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
include "connect.php"; // เรียกไฟล์เชื่อมต่อฐานข้อมูล PostgreSQL

// รับ id จาก POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id > 0) {
    // เตรียม statement
    $result = pg_prepare($con, "delete_user", "DELETE FROM users WHERE id = $1");
    
    if ($result) {
        $exec = pg_execute($con, "delete_user", array($id));
        if ($exec) {
            echo json_encode(["success" => true, "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete user"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Failed to prepare query"]);
    }

} else {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
}

pg_close($con);
