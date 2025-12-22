<?php
include '../../connectMySql.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomId = $conn->real_escape_string($_POST['roomId']);
    $roomName = $conn->real_escape_string($_POST['roomName']);
    
    $query = "UPDATE rooms SET room_name = '$roomName' WHERE room_id = '$roomId'";
    
    if ($conn->query($query)) {
        echo "success";
    } else {
        echo "Error updating room: " . $conn->error;
    }
    
    $conn->close();
}
?>