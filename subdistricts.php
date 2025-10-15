<?php
require 'connect.php'; // connect.php สำหรับ PostgreSQL

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$amphure_id = $_POST['amphure_id'] ?? $_GET['amphure_id'] ?? null;

if ($amphure_id === null) {
    echo json_encode(['status' => 'error', 'message' => 'Missing amphure_id']);
    exit();
}

$amphure_id = intval($amphure_id);

// Prepare & execute query
$sql = "SELECT id, name_th FROM thai_tambons WHERE amphure_id = $1 ORDER BY name_th ASC";
$result = pg_query_params($con, $sql, [$amphure_id]);

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
