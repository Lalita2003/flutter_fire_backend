<?php
require 'connect.php'; // connect.php สำหรับ PostgreSQL

// เพิ่ม CORS header เพื่ออนุญาตให้เรียก API ข้ามโดเมนได้
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Query
$sql = "SELECT id, name_th FROM thai_provinces ORDER BY name_th ASC";
$result = pg_query($con, $sql);

$data = [];
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['status' => 'error', 'message' => pg_last_error($con)]);
}

pg_close($con);
?>
