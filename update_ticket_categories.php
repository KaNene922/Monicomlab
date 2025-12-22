<?php
include 'connectMySql.php';

echo "<h3>Updating Ticket Categories</h3>\n";

// Function to determine category based on issue type and device name
function getCategoryFromIssueType($issueType, $deviceName = '') {
    $issueType = strtolower($issueType);
    $deviceName = strtolower($deviceName);
    
    // Hardware peripheral device issues
    if (strpos($issueType, 'mouse') !== false || 
        strpos($issueType, 'keyboard') !== false ||
        strpos($issueType, 'printer') !== false ||
        strpos($issueType, 'usb') !== false ||
        strpos($issueType, 'audio') !== false ||
        strpos($issueType, 'speaker') !== false ||
        strpos($issueType, 'microphone') !== false ||
        strpos($issueType, 'webcam') !== false ||
        strpos($issueType, 'camera') !== false ||
        strpos($issueType, 'monitor') !== false ||
        strpos($issueType, 'display') !== false) {
        return 'HARDWARE';
    }
    
    // Resource usage issues - ALL resource monitoring is SOFTWARE
    if (strpos($issueType, 'cpu') !== false || 
        strpos($issueType, 'ram') !== false || 
        strpos($issueType, 'disk') !== false || 
        strpos($issueType, 'memory') !== false ||
        strpos($issueType, 'storage') !== false ||
        strpos($issueType, 'high usage') !== false) {
        return 'SOFTWARE'; // All resource monitoring (PC and Server) is SOFTWARE
    }
    
    // Network issues
    if (strpos($issueType, 'offline') !== false || 
        strpos($issueType, 'connection') !== false ||
        strpos($issueType, 'network') !== false ||
        strpos($issueType, 'ping') !== false ||
        strpos($issueType, 'stale data') !== false) {
        return 'NETWORK';
    }
    
    // Software issues
    if (strpos($issueType, 'application') !== false ||
        strpos($issueType, 'software') !== false ||
        strpos($issueType, 'service') !== false ||
        strpos($issueType, 'process') !== false) {
        return 'SOFTWARE';
    }
    
    // Default to GENERAL if can't determine
    return 'GENERAL';
}

// Get all tickets without categories or with empty categories OR tickets that need updating
$query = "SELECT id, device_name, issue_type, category FROM ticket WHERE 
    (category IS NULL OR category = '' OR category = 'General') OR
    (device_name LIKE '%PC%' AND issue_type IN ('CPU Warning', 'CPU Critical', 'RAM Warning', 'RAM Critical', 'Disk Warning', 'Disk Critical') AND category = 'HARDWARE')";
$result = $conn->query($query);

$updated = 0;
$total = $result->num_rows;

echo "<p>Found {$total} tickets that need category updates.</p>\n";

while ($row = $result->fetch_assoc()) {
    $newCategory = getCategoryFromIssueType($row['issue_type'], $row['device_name']);
    
    // Update the ticket
    $updateQuery = "UPDATE ticket SET category = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newCategory, $row['id']);
    
    if ($stmt->execute()) {
        echo "<p>✅ Ticket ID {$row['id']}: {$row['device_name']} - {$row['issue_type']} → {$newCategory}</p>\n";
        $updated++;
    } else {
        echo "<p>❌ Failed to update Ticket ID {$row['id']}: " . $conn->error . "</p>\n";
    }
}

echo "<br><h4>Summary:</h4>\n";
echo "<p>✅ Successfully updated {$updated} out of {$total} tickets</p>\n";

// Show current distribution
echo "<br><h4>Current Category Distribution:</h4>\n";
$distQuery = "SELECT category, COUNT(*) as count FROM ticket GROUP BY category ORDER BY count DESC";
$distResult = $conn->query($distQuery);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Category</th><th>Count</th></tr>";
while ($row = $distResult->fetch_assoc()) {
    $category = $row['category'] ?? 'NULL';
    echo "<tr><td>{$category}</td><td>{$row['count']}</td></tr>";
}
echo "</table>";

$conn->close();
?>