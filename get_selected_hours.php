<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require "connect.php"; // connect.php สำหรับ PostgreSQL

// รับพิกัดผู้ใช้
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : 0;

// รัศมีในการล็อค (กิโลเมตร)
$radius_km = 5;

try {
    if ($lat != 0 && $lon != 0) {
        // Haversine formula
        $sql = "
            SELECT w.forecast_date, w.forecast_hour
            FROM weather_logs w
            JOIN burn_requests b ON w.burn_request_id = b.id
            WHERE b.status != 'cancelled'
              AND (
                  6371 * acos(
                      cos(radians($1)) * cos(radians(b.latitude)) * cos(radians(b.longitude) - radians($2))
                      + sin(radians($1)) * sin(radians(b.latitude))
                  )
              ) <= $3
        ";
        $result = pg_query_params($con, $sql, array($lat, $lon, $radius_km));
    } else {
        $sql = "
            SELECT w.forecast_date, w.forecast_hour
            FROM weather_logs w
            JOIN burn_requests b ON w.burn_request_id = b.id
            WHERE b.status != 'cancelled'
        ";
        $result = pg_query($con, $sql);
    }

    if (!$result) throw new Exception(pg_last_error($con));

    $hours = [];
    while ($row = pg_fetch_assoc($result)) {
        $hours[] = [
            'forecast_date' => $row['forecast_date'],
            'forecast_hour' => $row['forecast_hour'],
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $hours], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

pg_close($con);
?>
