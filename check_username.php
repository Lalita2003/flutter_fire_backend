<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require 'connect.php'; // ใช้ pg_connect ของ Neon

// รับค่า username จาก $_POST ก่อน
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['username'])) {
        $username = trim($_POST['username']);
    } else {
        // ลองอ่านจาก JSON body เผื่อ Flutter ส่ง application/json
        $input = json_decode(file_get_contents('php://input'), true);
        if (!empty($input['username'])) {
            $username = trim($input['username']);
        }
    }
}

if (empty($username)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username ว่างเปล่า',
        'exists' => null
    ]);
    exit;
}

$sql = "SELECT COUNT(*) AS cnt FROM users WHERE LOWER(username) = LOWER($1)";
$res = pg_query_params($con, $sql, [$username]);

if (!$res) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . pg_last_error($con),
        'exists' => null
    ]);
    exit;
}

$row = pg_fetch_assoc($res);
$exists = intval($row['cnt']) > 0;

echo json_encode([
    'status' => 'success',
    'exists' => $exists
]);

pg_close($con);
?>
