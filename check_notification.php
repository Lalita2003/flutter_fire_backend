<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require "connect.php"; // connect.php สำหรับ PostgreSQL

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$title   = isset($_GET['title']) ? trim($_GET['title']) : '';
$message = isset($_GET['message']) ? trim($_GET['message']) : '';
$status  = isset($_GET['status']) ? trim($_GET['status']) : '';

if (!$con) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

if ($user_id <= 0 || empty($title) || empty($message) || empty($status)) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// เตรียม query
$sql = "SELECT id FROM notifications WHERE user_id=$1 AND title=$2 AND message=$3 AND status=$4 LIMIT 1";
$stmt = pg_prepare($con, "check_notification", $sql);
$result = pg_execute($con, "check_notification", array($user_id, $title, $message, $status));

$exists = false;
$notifId = null;
if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $exists = true;
    $notifId = intval($row['id']);
}

echo json_encode(["exists" => $exists, "id" => $notifId]);

pg_close($con);
?>
