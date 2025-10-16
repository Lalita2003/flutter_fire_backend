<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php"; // ✅ ต้องเป็น pg_connect

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
$is_read = isset($_POST['is_read']) ? intval($_POST['is_read']) : 0;

if (!$con) {
    echo json_encode(["success" => false, "error" => "DB connection failed"]);
    exit;
}

if ($user_id <= 0 || $title === '' || $message === '') {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// ✅ เตรียมและ execute
$sql = "INSERT INTO notifications (user_id, title, message, status, is_read, created_at) 
        VALUES ($1, $2, $3, $4, $5, NOW()) RETURNING id";
pg_prepare($con, "insert_notification", $sql);
$result = pg_execute($con, "insert_notification", [$user_id, $title, $message, $status, $is_read]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    echo json_encode(["success" => true, "id" => intval($row['id'])]);
} else {
    echo json_encode(["success" => false, "error" => pg_last_error($con)]);
}
pg_close($con);
?>
