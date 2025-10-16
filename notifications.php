<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php";

// รองรับทั้ง JSON และ x-www-form-urlencoded
$input = json_decode(file_get_contents("php://input"), true);
$data = is_array($input) ? $input : $_POST;

$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$title = $data['title'] ?? '';
$message = $data['message'] ?? '';
$status = $data['status'] ?? 'pending';
$is_read = isset($data['is_read']) ? intval($data['is_read']) : 0;

if (!$con) {
    echo json_encode(["success" => false, "error" => "DB connect fail"]);
    exit;
}

if ($user_id <= 0 || $title == '' || $message == '') {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Insert
$sql = "INSERT INTO notifications (user_id, title, message, status, is_read, created_at)
        VALUES ($1,$2,$3,$4,$5,NOW()) RETURNING id";
pg_prepare($con, "insert_notification", $sql);
$result = pg_execute($con, "insert_notification", [$user_id,$title,$message,$status,$is_read]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    echo json_encode(["success" => true, "id" => intval($row['id'])]);
} else {
    echo json_encode(["success" => false, "error" => pg_last_error($con)]);
}
pg_close($con);
?>
