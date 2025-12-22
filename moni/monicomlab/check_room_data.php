<?php
include 'connectMySql.php';

echo "<h3>Database Room Information Check</h3>\n";

// Check rooms table
echo "<h4>Rooms in database:</h4>\n";
$roomQuery = "SELECT * FROM rooms";
$roomResult = $conn->query($roomQuery);

if ($roomResult && $roomResult->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Room ID</th><th>Room Name</th><th>Date Created</th></tr>";
    while ($row = $roomResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['room_id']}</td>";
        echo "<td>{$row['room_name']}</td>";
        echo "<td>{$row['date_created']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No rooms found in database</p>";
}

// Check devices and their rooms
echo "<h4>Devices and their rooms:</h4>\n";
$deviceQuery = "SELECT d.name, d.ip_address, d.room_id, r.room_name 
                FROM device d 
                LEFT JOIN rooms r ON d.room_id = r.room_id";
$deviceResult = $conn->query($deviceQuery);

if ($deviceResult && $deviceResult->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Device Name</th><th>IP Address</th><th>Room ID</th><th>Room Name</th></tr>";
    while ($row = $deviceResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['ip_address']}</td>";
        echo "<td>" . ($row['room_id'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['room_name'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No devices found in database</p>";
}

// Check tickets and what room data they should have
echo "<h4>Current tickets and their expected room data:</h4>\n";
$ticketQuery = "SELECT t.device_name, t.issue_type, d.name as device_match, r.room_name 
                FROM ticket t 
                LEFT JOIN device d ON t.device_name = d.name 
                LEFT JOIN rooms r ON d.room_id = r.room_id 
                WHERE t.status IN ('PENDING', 'Pending')
                ORDER BY t.id DESC LIMIT 10";
$ticketResult = $conn->query($ticketQuery);

if ($ticketResult && $ticketResult->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Ticket Device</th><th>Issue Type</th><th>Device Match</th><th>Room</th></tr>";
    while ($row = $ticketResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['device_name']}</td>";
        echo "<td>{$row['issue_type']}</td>";
        echo "<td>" . ($row['device_match'] ?? 'NO MATCH') . "</td>";
        echo "<td>" . ($row['room_name'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ No pending tickets found</p>";
}

$conn->close();
?>