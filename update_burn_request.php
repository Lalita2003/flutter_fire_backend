<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require "connect.php"; // connect.php สำหรับ PostgreSQL

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['request_id'], $data['status'], $data['approved_by'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$request_id = intval($data['request_id']);
$status = ($data['status'] === "approved") ? "approved" : "rejected";
$approved_by = intval($data['approved_by']);
$approval_note = $data['approval_note'] ?? null;

// เตรียม SQL
$sql = "
UPDATE burn_requests
SET status=$1,
    approved_by=$2,
    approved_at=NOW(),
    approval_note=$3
WHERE id=$4
";
$stmt = pg_prepare($con, "update_burn_request", $sql);
$exec = pg_execute($con, "update_burn_request", array($status, $approved_by, $approval_note, $request_id));

if ($exec) {
    echo json_encode(["status" => "success", "message" => "Updated successfully"]);
} else {
    $err = pg_last_error($con);
    echo json_encode(["status" => "error", "message" => "Update failed: $err"]);
}

pg_close($con);
?>
