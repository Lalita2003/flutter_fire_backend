<?php
include "connect.php"; // $con เป็น pg_connect

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "error" => ""];

// ตรวจสอบ POST ข้อมูลครบถ้วน
if (
    isset($_POST['id'], $_POST['username'], $_POST['lastname'], 
          $_POST['phone'], $_POST['village'], $_POST['role'])
) {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $village = trim($_POST['village']);
    $role = trim($_POST['role']);

    // จัดการ agency เฉพาะ officer หรือ admin
    $agency = null;
    if ($role === 'officer' || $role === 'admin') {
        $agency = isset($_POST['agency']) ? trim($_POST['agency']) : '';
    }

    // สร้าง SQL
    $sql = "UPDATE users SET 
                username = $1,
                lastname = $2,
                phone = $3,
                village = $4,
                role = $5";

    $params = [$username, $lastname, $phone, $village, $role];

    if ($agency !== null) {
        $sql .= ", agency = $6";
        $params[] = $agency;
        $sql .= " WHERE id = $7";
        $params[] = $id;
    } else {
        $sql .= ", agency = NULL WHERE id = $6";
        $params[] = $id;
    }

    $res = pg_query_params($con, $sql, $params);

    if ($res) {
        $response['success'] = true;
    } else {
        $response['error'] = "Database error: " . pg_last_error($con);
    }

} else {
    $response['error'] = "Missing required parameters";
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
pg_close($con);
?>
