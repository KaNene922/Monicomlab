<?php
// Check for potential central monitoring setup
include 'connectMySql.php';

echo "<h2>Monitoring Architecture Analysis</h2>";

// Check device table
$query = "SELECT * FROM device ORDER BY device, ip_address";
$result = $conn->query($query);

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

echo "<h3>Device Inventory:</h3>";
echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
echo "<tr><th>Name</th><th>IP Address</th><th>Device Type</th><th>Room</th><th>Potential Role</th></tr>";

foreach ($devices as $device) {
    echo "<tr>";
    echo "<td>{$device['name']}</td>";
    echo "<td>{$device['ip_address']}</td>";
    echo "<td>{$device['device']}</td>";
    echo "<td>{$device['room_id']}</td>";
    
    // Analyze potential role
    $role = "Regular Device";
    if (strpos(strtolower($device['name']), 'server') !== false) {
        $role = "🔴 CRITICAL: Server (May affect others)";
    } elseif (strpos(strtolower($device['name']), 'gateway') !== false || 
              strpos(strtolower($device['name']), 'router') !== false) {
        $role = "🔴 CRITICAL: Network Infrastructure";
    } elseif ($device['device'] == 'SERVER') {
        $role = "🟡 IMPORTANT: Server Device";
    } elseif (strpos($device['ip_address'], '.1') !== false || 
              strpos($device['ip_address'], '.254') !== false) {
        $role = "🟡 SUSPICIOUS: Gateway IP Range";
    }
    
    echo "<td>$role</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Analysis Questions:</h3>";
echo "<ul>";
echo "<li><strong>Question 1:</strong> Yung PC na in-off mo, ano ang name at IP?</li>";
echo "<li><strong>Question 2:</strong> May nakikita ka bang 'Server' o 'Gateway' sa list?</li>";
echo "<li><strong>Question 3:</strong> Saan naka-connect ang monitoring system? (Same PC ba?)</li>";
echo "<li><strong>Question 4:</strong> Lahat ba ng devices ay nasa same network segment?</li>";
echo "</ul>";

echo "<h3>Network Troubleshooting:</h3>";
echo "<ol>";
echo "<li><strong>Test Individual Devices:</strong> Run debug_network_connectivity.php</li>";
echo "<li><strong>Check Network Topology:</strong> Tignan kung connected via switch/hub</li>";
echo "<li><strong>Monitor Logs:</strong> Check kung may error sa monitoring scripts</li>";
echo "<li><strong>Isolate Test:</strong> I-off individually ang devices</li>";
echo "</ol>";

$conn->close();
?>