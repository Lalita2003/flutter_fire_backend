<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require "connect.php";

// รับค่า subdistrict_id และ user_id (เจ้าหน้าที่)
$subdistrict_id = isset($_GET['subdistrict_id']) ? intval($_GET['subdistrict_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if($subdistrict_id == 0 || $user_id == 0){
    echo json_encode(['status'=>'error','message'=>'Missing parameters']);
    exit;
}

// ดึงประวัติการตรวจสอบของเจ้าหน้าที่ในตำบล
$sql = "
SELECT 
    br.id, 
    br.area_name, 
    br.inspection_result, 
    br.inspection_note, 
    br.inspection_datetime
FROM burn_requests br
JOIN users u ON br.user_id = u.id
WHERE u.subdistrict_id=$1 AND br.inspected_by_id=$2
ORDER BY br.inspection_datetime DESC
";

$result = pg_query_params($con, $sql, [$subdistrict_id, $user_id]);

$history = [];
while($row = pg_fetch_assoc($result)){
    $history[] = [
        'id' => $row['id'],
        'area_name' => trim($row['area_name'] ?? ''),  
        'inspection_result' => trim($row['inspection_result'] ?? ''),
        'inspection_note' => trim($row['inspection_note'] ?? ''),
        'inspection_datetime' => $row['inspection_datetime'] ?? ''
    ];
}

// ส่งผลลัพธ์เป็น JSON
echo json_encode(['status'=>'success','history'=>$history], JSON_UNESCAPED_UNICODE);

// ปิดการเชื่อมต่อ
pg_close($con);
?>
