<?php
// Check current status case in database
include 'connectMySql.php';

echo "<h2>Current Status Values in Database:</h2>";

// Check monitoring_data table
$query1 = "SELECT DISTINCT status, COUNT(*) as count FROM monitoring_data GROUP BY status";
$result1 = $conn->query($query1);

echo "<h3>monitoring_data table:</h3>";
echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
echo "<tr><th>Status Value</th><th>Count</th><th>Case Type</th></tr>";

while ($row = $result1->fetch_assoc()) {
    $status = $row['status'];
    $count = $row['count'];
    $caseType = '';
    
    if ($status === strtoupper($status)) {
        $caseType = "UPPERCASE";
    } elseif ($status === strtolower($status)) {
        $caseType = "lowercase";
    } else {
        $caseType = "Mixed Case";
    }
    
    echo "<tr>";
    echo "<td>'$status'</td>";
    echo "<td>$count</td>";
    echo "<td style='color: " . ($caseType === 'lowercase' ? 'green' : 'red') . "'>$caseType</td>";
    echo "</tr>";
}
echo "</table>";

// Check detect_issue table
$query2 = "SELECT DISTINCT value, COUNT(*) as count FROM detect_issue WHERE name = 'CONNECTION LOST' GROUP BY value";
$result2 = $conn->query($query2);

echo "<h3>detect_issue table (CONNECTION LOST):</h3>";
echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
echo "<tr><th>Value</th><th>Count</th><th>Case Type</th></tr>";

while ($row = $result2->fetch_assoc()) {
    $value = $row['value'];
    $count = $row['count'];
    $caseType = '';
    
    if ($value === strtoupper($value)) {
        $caseType = "UPPERCASE";
    } elseif ($value === strtolower($value)) {
        $caseType = "lowercase";
    } else {
        $caseType = "Mixed Case";
    }
    
    echo "<tr>";
    echo "<td>'$value'</td>";
    echo "<td>$count</td>";
    echo "<td style='color: " . ($caseType === 'lowercase' ? 'green' : 'red') . "'>$caseType</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><p><strong>✅ Goal:</strong> All status values should be lowercase ('online', 'offline')</p>";
echo "<p><strong>❌ Fix needed:</strong> If you see UPPERCASE values, run fix_status_case.php</p>";

$conn->close();
?>