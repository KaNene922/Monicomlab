<?php
include '../../connectMySql.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['room_id'])) {
        $room_id = $conn->real_escape_string($_GET['room_id']);
        
        $query = "SELECT * FROM device WHERE room_id = $room_id ORDER BY device, name";
        $result = $conn->query($query);
        $devices = [];
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $devices[] = $row;
            }
        }
        
        echo json_encode($devices);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>