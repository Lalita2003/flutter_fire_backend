<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php"; // connect.php สำหรับ PostgreSQL

if (!$con) {
    echo json_encode(["status" => "error", "message" => "ไม่สามารถเชื่อมต่อ DB ได้"]);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid user_id"]);
    exit;
}

// ดึง notifications
$sql = "
SELECT id, title, message, is_read, COALESCE(status,'pending') AS status, created_at
FROM notifications
WHERE user_id=$1
ORDER BY created_at DESC
";
$stmt = pg_prepare($con, "get_notifications", $sql);
$result = pg_execute($con, "get_notifications", array($user_id));

$notifications = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $notifications[] = [
            "id" => intval($row['id']),
            "user_id" => $user_id,
            "title" => $row['title'],
            "message" => $row['message'],
            "is_read" => intval($row['is_read']),
            "status" => $row['status'],
            "created_at" => $row['created_at']
        ];
    }
}

echo json_encode($notifications, JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
