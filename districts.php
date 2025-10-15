<?php
require 'connect.php'; // connect.php สำหรับ PostgreSQL

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$province_id = $_POST['province_id'] ?? $_GET['province_id'] ?? null;

if ($province_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing province_id']);
    exit();
}

$province_id = intval($province_id);

// Query ด้วย pg_query_params
$sql = "SELECT id, name_th FROM thai_amphures WHERE province_id = $1 ORDER BY name_th ASC";
$result = pg_query_params($con, $sql, [$province_id]);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => pg_last_error($con)]);
    exit();
}

$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

pg_close($con);
?>
