<?php
include 'connectMySql.php';

echo "<h3>Direct Category Update for PC Issues</h3>\n";

// Direct update for PC resource issues to SOFTWARE category
$updateQuery = "UPDATE ticket SET category = 'SOFTWARE' WHERE 
    device_name LIKE '%PC%' AND 
    issue_type IN ('CPU Warning', 'CPU Critical', 'RAM Warning', 'RAM Critical', 'Disk Warning', 'Disk Critical') AND
    category = 'HARDWARE'";

$result = $conn->query($updateQuery);

if ($result) {
    $affectedRows = $conn->affected_rows;
    echo "<p>✅ Successfully updated {$affectedRows} PC tickets from HARDWARE to SOFTWARE category</p>\n";
    
    // Show current tickets
    echo "<h4>Current PC Tickets:</h4>\n";
    $selectQuery = "SELECT device_name, issue_type, category, severity FROM ticket WHERE device_name LIKE '%PC%' ORDER BY id DESC";
    $selectResult = $conn->query($selectQuery);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Device</th><th>Issue Type</th><th>Category</th><th>Severity</th></tr>";
    while ($row = $selectResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['device_name']}</td>";
        echo "<td>{$row['issue_type']}</td>";
        echo "<td><strong>{$row['category']}</strong></td>";
        echo "<td>{$row['severity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>❌ Error updating tickets: " . $conn->error . "</p>\n";
}

$conn->close();
?>