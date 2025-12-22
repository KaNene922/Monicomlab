<?php
include '../../connectMySql.php';

// Set timezone to Philippine time
date_default_timezone_set('Asia/Manila');

// Function to control detection frequency
function shouldRunDetection() {
    // ALWAYS run detection - we only detect REAL issues now
    return true;
}

// REALTIME Issue Detection - ONLY REAL DATA
function detectSystemIssues() {
    global $conn;
    $detectedIssues = [];
    
    // ALWAYS run detection since we only detect REAL issues
    if (!shouldRunDetection()) {
        return $detectedIssues;
    }
    
    // Get all devices for monitoring - ONLY devices that actually exist
    $deviceQuery = "SELECT d.*, r.room_name, md.cpu, md.ram, md.disk, md.status, md.created_at
                    FROM device d
                    LEFT JOIN rooms r ON d.room_id = r.room_id
                    LEFT JOIN (
                        SELECT md1.*
                        FROM monitoring_data md1
                        INNER JOIN (
                            SELECT ip_address, MAX(id) AS max_id
                            FROM monitoring_data
                            GROUP BY ip_address
                        ) md2 ON md1.ip_address = md2.ip_address AND md1.id = md2.max_id
                    ) md ON d.ip_address = md.ip_address
                    WHERE d.name IS NOT NULL AND d.ip_address IS NOT NULL";
    
    $devices = $conn->query($deviceQuery);
    
    if ($devices) {
        while ($device = $devices->fetch_assoc()) {
            // 1. REALTIME Network Issues Detection (only actual offline/stale data)
            $networkIssues = checkRealNetworkIssues($device);
            $detectedIssues = array_merge($detectedIssues, $networkIssues);
            
            // 2. REALTIME Software Issues Detection (only from detect_issue table)
            $softwareIssues = checkRealSoftwareIssues($device);
            $detectedIssues = array_merge($detectedIssues, $softwareIssues);
            
            // 3. REALTIME Hardware Issues Detection (only from detect_issue table)  
            $hardwareIssues = checkRealHardwareIssues($device);
            $detectedIssues = array_merge($detectedIssues, $hardwareIssues);
            
            // 4. REALTIME Resource Monitoring (actual CPU/RAM/Disk data)
            $resourceIssues = checkResourceIssues($device);
            $detectedIssues = array_merge($detectedIssues, $resourceIssues);
        }
    }
    
    // 5. REALTIME Network Infrastructure Issues (only actual multiple offline devices)
    $infraIssues = checkRealNetworkInfrastructure();
    $detectedIssues = array_merge($detectedIssues, $infraIssues);

    // 6. Sync alerts from connected_devices (peripherals, USB, charger, etc.)
    $connectedDeviceIssues = checkConnectedDeviceAlerts();
    $detectedIssues = array_merge($detectedIssues, $connectedDeviceIssues);
    
    return $detectedIssues;
}

// Check hardware resource issues (CPU, RAM, Disk)
function checkResourceIssues($device) {
    global $conn;
    $issues = [];
    
    $thresholds = [
        'CPU' => ['warning' => 70, 'critical' => 90],
        'RAM' => ['warning' => 75, 'critical' => 90],
        'Disk' => ['warning' => 80, 'critical' => 95]
    ];
    
    $resources = ['cpu' => 'CPU', 'ram' => 'RAM', 'disk' => 'Disk'];
    
    foreach ($resources as $dbField => $displayName) {
        if (!empty($device[$dbField]) && $device[$dbField] > 0) {
            $usage = $device[$dbField];
            $issueType = '';
            $severity = '';
            
            // All resource usage monitoring is SOFTWARE category (both PC and Server)
            $category = 'SOFTWARE'; // All resource monitoring is software-level monitoring
            
            if ($usage >= $thresholds[$displayName]['critical']) {
                $issueType = $displayName . ' Critical';
                $severity = 'Critical';
            } elseif ($usage >= $thresholds[$displayName]['warning']) {
                $issueType = $displayName . ' Warning';
                $severity = 'High';
            }
            
            if (!empty($issueType)) {
                // Check if ticket already exists
                if (!ticketExists($device['name'], $issueType)) {
                    $issues[] = createIssueTicket(
                        $device['name'],
                        $issueType,
                        $category,
                        $severity,
                        "{$displayName} usage is " . ($severity === 'Critical' ? 'critically' : '') . " high: {$usage}%",
                        $device['room_name'] ?? 'Unknown'
                    );
                }
            }
        }
    }
    
    return $issues;
}

// REALTIME Network Issues Detection - ONLY from actual monitoring data
function checkRealNetworkIssues($device) {
    global $conn;
    $issues = [];
    
    // 1. ONLY detect if device is ACTUALLY OFFLINE in monitoring_data table
    if ($device['status'] === 'offline' || empty($device['status'])) {
        $deviceType = strtoupper($device['device'] ?? 'DEVICE');
        $issueType = "Device Offline";
        
        if (!ticketExists($device['name'], $issueType)) {
            $issues[] = createIssueTicket(
                $device['name'],
                $issueType,
                'NETWORK',
                'High',
                "Device is not responding to network requests",
                $device['room_name'] ?? 'Unknown'
            );
        }
    }
    
    // 2. ONLY detect stale data if monitoring data is ACTUALLY old (>10 minutes)
    if (!empty($device['created_at'])) {
        $lastUpdate = strtotime($device['created_at']);
        $currentTime = time();
        $minutesSinceUpdate = ($currentTime - $lastUpdate) / 60;
        
        if ($minutesSinceUpdate > 10) {
            if (!ticketExists($device['name'], 'Stale Data')) {
                $issues[] = createIssueTicket(
                    $device['name'],
                    'Stale Data',
                    'NETWORK',
                    'Medium',
                    "No monitoring data received for " . round($minutesSinceUpdate) . " minutes",
                    $device['room_name'] ?? 'Unknown'
                );
            }
        }
    }
    
    return $issues;
}

// REALTIME Network Infrastructure Detection - ONLY actual multiple offline devices  
function checkRealNetworkInfrastructure() {
    global $conn;
    $issues = [];
    
    // ONLY check for ACTUAL multiple devices offline (real network infrastructure issues)
    $roomQuery = "SELECT r.room_name, COUNT(*) as offline_count, 
                         COUNT(d.id) as total_devices
                  FROM device d
                  LEFT JOIN rooms r ON d.room_id = r.room_id
                  LEFT JOIN monitoring_data md ON d.ip_address = md.ip_address
                  WHERE md.status = 'offline' OR md.status IS NULL
                  GROUP BY r.room_id, r.room_name
                  HAVING offline_count >= 3 AND total_devices > 1";
    
    $result = $conn->query($roomQuery);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $deviceName = 'NETWORK_' . $row['room_name'];
            $issueType = 'Multiple Devices Offline';
            $description = "Multiple devices offline in " . $row['room_name'] . " (" . $row['offline_count'] . " out of " . $row['total_devices'] . " devices)";
            
            if (!ticketExists($deviceName, $issueType)) {
                $issues[] = createIssueTicket(
                    $deviceName,
                    $issueType,
                    'NETWORK',
                    'Critical',
                    $description,
                    $row['room_name']
                );
            }
        }
    }
    
    return $issues;
}

// REALTIME Software Issues Detection - ONLY from detect_issue table (ACTUAL logged issues)
function checkRealSoftwareIssues($device) {
    global $conn;
    $issues = [];
    
    // ONLY check for SOFTWARE issues that are ACTUALLY logged in detect_issue table
    $query = "SELECT * FROM detect_issue WHERE ip_address = ? AND (
        LOWER(name) LIKE '%application%' OR LOWER(name) LIKE '%software%' OR 
        LOWER(name) LIKE '%service%' OR LOWER(name) LIKE '%process%' OR
        LOWER(name) LIKE '%program%' OR LOWER(name) LIKE '%system%' OR
        LOWER(name) LIKE '%update%' OR LOWER(name) LIKE '%install%' OR
        LOWER(name) LIKE '%driver%' OR LOWER(name) LIKE '%registry%' OR
        LOWER(name) LIKE '%dll%' OR LOWER(name) LIKE '%exe%' OR
        LOWER(name) LIKE '%crash%' OR LOWER(name) LIKE '%freeze%' OR
        LOWER(name) LIKE '%hang%' OR LOWER(name) LIKE '%error%' OR
        LOWER(name) LIKE '%exception%' OR LOWER(name) LIKE '%memory leak%' OR
        LOWER(name) LIKE '%performance%' OR LOWER(name) LIKE '%slow%' OR
        LOWER(name) LIKE '%boot%' OR LOWER(name) LIKE '%startup%' OR
        LOWER(name) LIKE '%shutdown%' OR LOWER(name) LIKE '%license%' OR
        LOWER(name) LIKE '%activation%' OR LOWER(name) LIKE '%compatibility%' OR
        LOWER(name) LIKE '%virus%' OR LOWER(name) LIKE '%malware%' OR
        LOWER(name) LIKE '%antivirus%' OR LOWER(name) LIKE '%firewall%' OR
        LOWER(name) LIKE '%security%' OR LOWER(name) LIKE '%patch%' OR
        LOWER(value) LIKE '%failed%' OR LOWER(value) LIKE '%stopped%' OR
        LOWER(value) LIKE '%not responding%' OR LOWER(value) LIKE '%access denied%' OR
        LOWER(value) LIKE '%permission%' OR LOWER(value) LIKE '%corrupted%' OR
        LOWER(value) LIKE '%missing%' OR LOWER(value) LIKE '%outdated%' OR
        LOWER(value) LIKE '%incompatible%' OR LOWER(value) LIKE '%expired%'
    ) ORDER BY date DESC LIMIT 10";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $device['ip_address']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($issue = $result->fetch_assoc()) {
            $severity = determineSeverity($issue['name'], $issue['value']);
            $category = 'SOFTWARE';
            
            // Network-related software issues
            if (strpos(strtolower($issue['name']), 'connection lost') !== false || 
                strpos(strtolower($issue['value']), 'connection lost') !== false) {
                $category = 'NETWORK';
            }
            
            $issueType = $issue['name'];
            $description = $issue['value'];
            
            if (!ticketExists($device['name'], $issueType)) {
                $issues[] = createIssueTicket(
                    $device['name'],
                    $issueType,
                    $category,
                    $severity,
                    $description,
                    $device['room_name'] ?? 'Unknown'
                );
            }
        }
    }
    
    return $issues;
}

// REALTIME Hardware Issues Detection - ONLY from detect_issue table (ACTUAL logged issues)
function checkRealHardwareIssues($device) {
    global $conn;
    $issues = [];
    
    // ONLY check for HARDWARE issues that are ACTUALLY logged in detect_issue table
    $query = "SELECT * FROM detect_issue WHERE ip_address = ? AND (
        LOWER(name) LIKE '%mouse%' OR LOWER(name) LIKE '%keyboard%' OR 
        LOWER(name) LIKE '%printer%' OR LOWER(name) LIKE '%usb%' OR 
        LOWER(name) LIKE '%audio%' OR LOWER(name) LIKE '%speaker%' OR 
        LOWER(name) LIKE '%microphone%' OR LOWER(name) LIKE '%webcam%' OR 
        LOWER(name) LIKE '%camera%' OR LOWER(name) LIKE '%monitor%' OR 
        LOWER(name) LIKE '%display%' OR LOWER(name) LIKE '%hardware%' OR
        LOWER(value) LIKE '%device not found%' OR
        LOWER(value) LIKE '%hardware error%' OR
        LOWER(value) LIKE '%device disconnected%' OR
        LOWER(value) LIKE '%not detected%' OR
        LOWER(value) LIKE '%malfunction%'
    ) ORDER BY date DESC LIMIT 10";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $device['ip_address']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($issue = $result->fetch_assoc()) {
            $severity = determineSeverity($issue['name'], $issue['value']);
            $issueType = $issue['name'];
            $description = $issue['value'];
            
            if (!ticketExists($device['name'], $issueType)) {
                $issues[] = createIssueTicket(
                    $device['name'],
                    $issueType,
                    'HARDWARE',
                    $severity,
                    $description,
                    $device['room_name'] ?? 'Unknown'
                );
            }
        }
    }
    
    return $issues;
}

// Helper function to determine severity based on issue name and value
function determineSeverity($issueName, $issueValue) {
    $name = strtolower($issueName);
    $value = strtolower($issueValue);
    
    // Critical issues
    if (strpos($name, 'critical') !== false || strpos($value, 'critical') !== false ||
        strpos($name, 'crash') !== false || strpos($value, 'crash') !== false ||
        strpos($name, 'system') !== false || strpos($value, 'system') !== false ||
        strpos($name, 'boot') !== false || strpos($value, 'boot') !== false ||
        strpos($name, 'virus') !== false || strpos($value, 'virus') !== false ||
        strpos($name, 'malware') !== false || strpos($value, 'malware') !== false ||
        strpos($name, 'corruption') !== false || strpos($value, 'corruption') !== false) {
        return 'Critical';
    }
    // High priority issues
    elseif (strpos($name, 'warning') !== false || strpos($value, 'warning') !== false ||
            strpos($name, 'error') !== false || strpos($value, 'error') !== false ||
            strpos($name, 'failed') !== false || strpos($value, 'failed') !== false ||
            strpos($name, 'stopped') !== false || strpos($value, 'stopped') !== false ||
            strpos($name, 'security') !== false || strpos($value, 'security') !== false ||
            strpos($name, 'license') !== false || strpos($value, 'license') !== false ||
            strpos($name, 'offline') !== false || strpos($value, 'offline') !== false ||
            strpos($name, 'disconnected') !== false || strpos($value, 'disconnected') !== false) {
        return 'High';
    }
    // Low priority issues
    elseif (strpos($name, 'slow') !== false || strpos($value, 'slow') !== false ||
            strpos($name, 'performance') !== false || strpos($value, 'performance') !== false ||
            strpos($name, 'compatibility') !== false || strpos($value, 'compatibility') !== false) {
        return 'Low';
    }
    
    return 'Medium'; // Default
}

// Helper function to check if ticket already exists
function ticketExists($deviceName, $issueType) {
    global $conn;
    
    $query = "SELECT id FROM ticket 
              WHERE device_name = ? 
              AND issue_type = ? 
              AND status IN ('Pending', 'PENDING')";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ss", $deviceName, $issueType);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    return false;
}

// Helper function to update existing ticket
function updateExistingTicket($deviceName, $issueType, $description) {
    global $conn;
    
    // Force Philippine timezone for updates
    date_default_timezone_set('Asia/Manila');
    $current_time = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
    
    $query = "UPDATE ticket 
              SET description = ?, date = ? 
              WHERE device_name = ? 
              AND issue_type = ? 
              AND status IN ('Pending', 'PENDING')";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssss", $description, $current_time, $deviceName, $issueType);
        return $stmt->execute();
    }
    return false;
}

// Helper function to create issue ticket
function createIssueTicket($deviceName, $issueType, $category, $severity, $description, $location = 'Unknown') {
    global $conn;
    
    // First check if columns exist, if not use basic insert
    $columnsToCheck = ['category', 'severity', 'location', 'auto_detected'];
    $availableColumns = [];
    
    $result = $conn->query("DESCRIBE ticket");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (in_array($row['Field'], $columnsToCheck)) {
                $availableColumns[] = $row['Field'];
            }
        }
    }
    
    // Build dynamic query based on available columns
    $fields = ['device_name', 'issue_type', 'description', 'status', 'date'];
    // Force Philippine timezone for new entries
    date_default_timezone_set('Asia/Manila');
    $current_time = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
    $values = [$deviceName, $issueType, $description, 'Pending', $current_time];
    $placeholders = ['?', '?', '?', '?', '?'];
    
    if (in_array('category', $availableColumns)) {
        $fields[] = 'category';
        $values[] = $category;
        $placeholders[] = '?';
    }
    
    if (in_array('severity', $availableColumns)) {
        $fields[] = 'severity';
        // Ensure we don't insert empty severity into DB; default to 'Medium'
        $values[] = !empty($severity) ? $severity : 'Medium';
        $placeholders[] = '?';
    }
    
    if (in_array('location', $availableColumns)) {
        $fields[] = 'location';
        $values[] = $location;
        $placeholders[] = '?';
    }
    
    if (in_array('auto_detected', $availableColumns)) {
        $fields[] = 'auto_detected';
        $values[] = 1;
        $placeholders[] = '?';
    }
    
    $query = "INSERT INTO ticket (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        // Create dynamic bind_param string
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => "Ticket created for {$deviceName} - {$issueType}",
                'ticket_id' => $conn->insert_id,
                'device' => $deviceName,
                'issue_type' => $issueType,
                'category' => $category,
                'severity' => $severity,
                'description' => $description,
                'location' => $location
            ];
        } else {
            return [
                'success' => false,
                'message' => "Error creating ticket: " . $conn->error
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => "Error preparing statement: " . $conn->error
        ];
    }
}

// Sync connected_devices alerts into troubleshooting tickets
function checkConnectedDeviceAlerts() {
    global $conn;
    $issues = [];

    // Fetch most recent connected_devices entries grouped by ip/device to detect disconnected items
    $query = "SELECT cd.* , d.name as device_name, r.room_name
              FROM connected_devices cd
              LEFT JOIN device d ON cd.ip_address = d.ip_address
              LEFT JOIN rooms r ON d.room_id = r.room_id
              WHERE cd.status IN ('Disconnected', 'Removed')
              ORDER BY cd.detected_at DESC";

    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Determine host device name (the PC) and peripheral friendly name
            $friendly = trim($row['friendly_name'] ?? ''); // e.g., Power Adapter
            $hostName = trim($row['device_name'] ?? '');   // device.name from device table (host)

            // For the ticket's device_name use the host (so Troubleshooting can resolve Room). If host not found, fall back to IP.
            $ticketDevice = $hostName !== '' ? $hostName : $row['ip_address'];

            $issueType = 'Peripheral Disconnected';
            $category = 'HARDWARE';
            $severity = 'Medium';

            // Description should mention the peripheral only; do not include IP or last-seen per request
            $peripheralLabel = $friendly !== '' ? $friendly : 'Peripheral';
            $description = "{$peripheralLabel} disconnected";

            // Keep location resolved from device join (room of host) when available
            $location = $row['room_name'] ?? 'Unknown';

            // Only create ticket if one doesn't already exist (Pending)
            if (!ticketExists($ticketDevice, $issueType)) {
                $created = createIssueTicket(
                    $ticketDevice,
                    $issueType,
                    $category,
                    $severity,
                    $description,
                    $location
                );

                if (is_array($created) && !empty($created['success'])) {
                    $issues[] = $created;
                }
            } else {
                // If ticket exists, update description/time so techs see latest info
                updateExistingTicket($ticketDevice, $issueType, $description);
            }
        }
    }

    return $issues;
}

// If called directly, run detection and return results
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['run']) && $_GET['run'] === 'true')) {
    header('Content-Type: application/json');
    $issues = detectSystemIssues();
    // Use DateTime for proper timezone handling
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    echo json_encode([
        'success' => true,
        'issues_detected' => count($issues),
        'issues' => $issues,
        'timestamp' => $now->format('Y-m-d H:i:s')
    ]);
    exit;
}
?>