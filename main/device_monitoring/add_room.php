<?php
include '../../connectMySql.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roomName = $conn->real_escape_string($_POST['roomName']);
    
    $query = "INSERT INTO rooms (room_name) VALUES ('$roomName')";
    
    if ($conn->query($query)) {
        // Add a small delay and force page reload to ensure data is refreshed
        header("Location: index.php?success=Room added successfully&refresh=" . time());
    } else {
        header("Location: index.php?error=Error adding room: " . $conn->error);
    }
    
    $conn->close();
}
?>