<?php
include '../../connectMySql.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                r.room_id, 
                r.room_name,
                COUNT(d.id) as total_devices,
                SUM(CASE WHEN d.status = 'online' THEN 1 ELSE 0 END) as online_devices,
                SUM(CASE WHEN d.status = 'offline' OR d.status IS NULL THEN 1 ELSE 0 END) as offline_devices,
                SUM(CASE WHEN d.device = 'Switch' THEN 1 ELSE 0 END) as switch_count,
                SUM(CASE WHEN d.device = 'Router' THEN 1 ELSE 0 END) as router_count,
                SUM(CASE WHEN d.device = 'PC' THEN 1 ELSE 0 END) as pc_count,
                SUM(CASE WHEN d.device = 'Server' THEN 1 ELSE 0 END) as server_count,
                MAX(d.created_at) as last_checked
              FROM rooms r
              LEFT JOIN device d ON r.room_id = d.room_id
              GROUP BY r.room_id, r.room_name
              ORDER BY r.room_name";

    $result = $conn->query($query);
    $rooms = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
    }

    echo json_encode($rooms);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>