<?php
include '../../connectMySql.php';

// Function to check resource usage and create ticket
function checkResourceUsageAndCreateTicket($device, $resourceType, $usageValue) {
    global $conn;
    
    // Define thresholds for each resource type
    $thresholds = [
        'CPU' => ['warning' => 70, 'critical' => 90],
        'RAM' => ['warning' => 75, 'critical' => 90],
        'Disk' => ['warning' => 80, 'critical' => 95]
    ];
    
    // Check if we need to create a ticket
    $issueType = '';
    $description = '';
    
    if ($usageValue > $thresholds[$resourceType]['critical']) {
        $issueType = $resourceType . ' Critical';
        $description = "{$resourceType} usage is critically high: {$usageValue}%";
    } else if ($usageValue >= $thresholds[$resourceType]['warning']) {
        $issueType = $resourceType . ' Warning';
        $description = "{$resourceType} usage is high: {$usageValue}%";
    }
    
    // If we have an issue, check if a ticket already exists
    if (!empty($issueType)) {
        // Check if there's already an Pending ticket for this device with the same issue type
        $checkQuery = "SELECT id FROM ticket 
                      WHERE device_name = ? 
                      AND issue_type = ? 
                      AND status = 'Pending'";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $device['name'], $issueType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Only create a new ticket if there isn't already an Pending one for this device and issue type
        if ($result->num_rows === 0) {
            $insertQuery = "INSERT INTO ticket (device_name, issue_type, description, status, date) 
                           VALUES (?, ?, ?, 'Pending', NOW())";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $device['name'], $issueType, $description);
            
            if ($stmt->execute()) {
                return "Ticket created for {$device['name']} - {$issueType}";
            } else {
                return "Error creating ticket: " . $conn->error;
            }
        } else {
            return "Pending ticket already exists for {$device['name']} - {$issueType}";
        }
    }
    
    return "No ticket needed";
}

// Process the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $devices = json_decode($_POST['devices'], true);
    $results = [];
    
    foreach ($devices as $device) {
        // Check CPU usage
        if (isset($device['cpu'])) {
            $result = checkResourceUsageAndCreateTicket($device, 'CPU', $device['cpu']);
            $results[$device['name']]['CPU'] = $result;
        }
        
        // Check RAM usage
        if (isset($device['ram'])) {
            $result = checkResourceUsageAndCreateTicket($device, 'RAM', $device['ram']);
            $results[$device['name']]['RAM'] = $result;
        }
        
        // Check Disk usage
        if (isset($device['disk'])) {
            $result = checkResourceUsageAndCreateTicket($device, 'Disk', $device['disk']);
            $results[$device['name']]['Disk'] = $result;
        }
    }
    
    echo json_encode($results);
} else {
    echo "Invalid request method";
}
?>