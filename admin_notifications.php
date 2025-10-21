<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

include 'connect.php'; // ใช้ $con จาก pg_connect()

$response = ["success" => false, "notifications" => []];

// ดึงข้อมูล notifications เรียงตาม created_at ล่าสุด
$sql = "
    SELECT 
        id, 
        user_id, 
        title, 
        message, 
        is_read, 
        TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS') AS created_at
    FROM notifications
    ORDER BY created_at DESC
";

$res = pg_query($con, $sql);

if ($res && pg_num_rows($res) > 0) {
    $data = [];
    while ($row = pg_fetch_assoc($res)) {
        // แปลงค่า is_read เป็น boolean
        $row['is_read'] = ($row['is_read'] == 1);

        // แปลงค่า id และ user_id เป็น int (เผื่อ Flutter จะ parse ได้ง่าย)
        $row['id'] = (int)$row['id'];
        $row['user_id'] = (int)$row['user_id'];

        $data[] = $row;
    }

    $response['success'] = true;
    $response['notifications'] = $data;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
pg_close($con);
?>
