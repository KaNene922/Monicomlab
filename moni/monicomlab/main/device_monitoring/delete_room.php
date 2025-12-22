<?php
include '../../connectMySql.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $conn->real_escape_string($_POST['roomId']);
    
    // Start transaction to ensure both operations succeed or fail together
    $conn->begin_transaction();
    
    try {
        // First, delete all devices associated with this room
        $deleteDevicesQuery = "DELETE FROM device WHERE room_id = '$roomId'";
        if (!$conn->query($deleteDevicesQuery)) {
            throw new Exception("Error deleting devices: " . $conn->error);
        }
        
        // Then delete the room
        $deleteRoomQuery = "DELETE FROM rooms WHERE room_id = '$roomId'";
        if (!$conn->query($deleteRoomQuery)) {
            throw new Exception("Error deleting room: " . $conn->error);
        }
        
        // Commit the transaction if both operations succeeded
        $conn->commit();
        echo "success";
        
    } catch (Exception $e) {
        // Rollback the transaction if any operation failed
        $conn->rollback();
        echo $e->getMessage();
    }
    
    $conn->close();
}
?>