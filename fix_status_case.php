<?php
// Fix status case in existing monitoring data
include 'connectMySql.php';

echo "<h2>Fixing status case in monitoring_data table...</h2>";

// Update ONLINE to online
$updateOnline = "UPDATE monitoring_data SET status = 'online' WHERE status = 'ONLINE'";
$result1 = $conn->query($updateOnline);
$affected1 = $conn->affected_rows;

// Update OFFLINE to offline  
$updateOffline = "UPDATE monitoring_data SET status = 'offline' WHERE status = 'OFFLINE'";
$result2 = $conn->query($updateOffline);
$affected2 = $conn->affected_rows;

echo "<p>✓ Updated $affected1 ONLINE records to online</p>";
echo "<p>✓ Updated $affected2 OFFLINE records to offline</p>";

// Also update detect_issue table
$updateDetectIssue = "UPDATE detect_issue SET value = 'offline' WHERE value = 'OFFLINE'";
$result3 = $conn->query($updateDetectIssue);
$affected3 = $conn->affected_rows;

echo "<p>✓ Updated $affected3 detect_issue records</p>";
echo "<p><strong>Status case fix completed!</strong></p>";
echo "<p>Please refresh your monitoring page to see the changes.</p>";

$conn->close();
?>