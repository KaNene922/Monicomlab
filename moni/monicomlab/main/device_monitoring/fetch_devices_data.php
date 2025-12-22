<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Use the same MySQLi connection
include '../../connectMySql.php';

$ip_address = $_GET['ip_address'] ?? '';

if (!$ip_address) {
    echo json_encode(['error' => 'No IP address provided']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT device_class, friendly_name, status, detected_at 
        FROM connected_devices 
        WHERE ip_address = ?
        ORDER BY detected_at DESC
    ");
    $stmt->bind_param("s", $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();

    $devices = [];
    while ($row = $result->fetch_assoc()) {
        // Convert OK -> Connected
        if ($row['status'] === 'OK') {
            $row['status'] = 'Connected';
        }
        $devices[] = $row;
    }

    echo json_encode($devices);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
