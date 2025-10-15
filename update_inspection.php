<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
require "connect.php"; // connect.php สำหรับ PostgreSQL

$burn_id = isset($_POST['burn_id']) ? intval($_POST['burn_id']) : 0;
$inspected_by_id = isset($_POST['inspected_by_id']) ? intval($_POST['inspected_by_id']) : 0;
$inspection_result = $_POST['inspection_result'] ?? '';
$inspection_note = $_POST['inspection_note'] ?? '';

if (!$burn_id || !$inspected_by_id || !$inspection_result) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

// เตรียม query
$result = pg_prepare($con, "update_inspection",
    "UPDATE burn_requests
     SET inspected_by_id = $1,
         inspection_result = $2,
         inspection_note = $3,
         inspection_datetime = NOW()
     WHERE id = $4"
);

if ($result) {
    $exec = pg_execute($con, "update_inspection", array($inspected_by_id, $inspection_result, $inspection_note, $burn_id));
    if ($exec) {
        echo json_encode(['status' => 'success']);
    } else {
        $err = pg_last_error($con);
        echo json_encode(['status' => 'error', 'message' => "Failed to execute query: $err"]);
    }
} else {
    $err = pg_last_error($con);
    echo json_encode(['status' => 'error', 'message' => "Failed to prepare query: $err"]);
}

pg_close($con);
?>
