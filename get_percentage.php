<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/get_percentage.log');

function log_debug($message) {
    error_log('[get_percentage] ' . $message);
}

include 'connectMySql.php';
log_debug('Request from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' ip=' . ($_GET['ip'] ?? 'unknown'));

// Create connection using PDO (secure)
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db;charset=utf8mb4", $username_server, $password_server);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    log_debug('DB connection failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$ip_address = $_GET['ip'] ?? 'unknown';

// --- CLEAN PREVIOUS OPENED APPS ---
try {
    $stmt = $pdo->prepare("DELETE FROM opened_application WHERE ip_address = ?");
    $stmt->execute([$ip_address]);
} catch (PDOException $e) {
    // continue silently
}

// --- SAFE POWERSHELL EXECUTION HELPER ---
function safe_exec($command) {
    $output = shell_exec($command);
    return $output ? trim($output) : '';
}

// --- CPU USAGE ---
$cpuUsage = safe_exec('powershell -Command "Get-Counter -Counter \'\\Processor(_Total)\\% Processor Time\' | Select-Object -ExpandProperty CounterSamples | Select-Object -ExpandProperty CookedValue"');
$cpuUsage = is_numeric($cpuUsage) ? round(floatval($cpuUsage)) : 0;

// --- MEMORY USAGE ---
$memJson = safe_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object TotalVisibleMemorySize,FreePhysicalMemory | ConvertTo-Json"');
$memData = json_decode($memJson, true);
if (is_array($memData)) {
    $totalMem = $memData['TotalVisibleMemorySize'] ?? 1;
    $freeMem = $memData['FreePhysicalMemory'] ?? 0;
    $memUsage = $totalMem > 0 ? round((($totalMem - $freeMem) / $totalMem) * 100) : 0;
} else {
    $memUsage = 0;
}

// --- DISK USAGE (C:) ---
$diskJson = safe_exec('powershell -Command "Get-PSDrive C | Select-Object Used,Free | ConvertTo-Json"');
$diskData = json_decode($diskJson, true);
if (is_array($diskData)) {
    $usedDisk = $diskData['Used'] ?? 0;
    $freeDisk = $diskData['Free'] ?? 1;
    $diskTotal = $usedDisk + $freeDisk;
    $diskUsage = $diskTotal > 0 ? round(($usedDisk / $diskTotal) * 100) : 0;
} else {
    $diskUsage = 0;
}

// --- STATUS ---
$status = 'ONLINE';

// --- INSERT MONITORING DATA ---
try {
    $stmt = $pdo->prepare("
        INSERT INTO monitoring_data (ip_address, cpu, ram, disk, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$ip_address, $cpuUsage, $memUsage, $diskUsage, $status]);
} catch (PDOException $e) {
    // return JSON error response if DB fails
    http_response_code(500);
    log_debug('Insert monitoring_data failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Database insert failed: ' . $e->getMessage()]);
    exit;
}

// --- OPENED APPLICATIONS ---
$appsJson = safe_exec('powershell -Command "gps | where {$_.MainWindowTitle} | select Name,MainWindowTitle | ConvertTo-Json"');
$apps = json_decode($appsJson, true);
if ($apps && isset($apps['Name']) && isset($apps['MainWindowTitle'])) {
    $apps = [$apps];
}
if (!is_array($apps)) $apps = [];

try {
    $stmtApp = $pdo->prepare("
        INSERT INTO opened_application (ip_address, application, window_title, status)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($apps as $app) {
        $application = $app['Name'] ?? '';
        $windowTitle = $app['MainWindowTitle'] ?? '';
        $stmtApp->execute([$ip_address, $application, $windowTitle, 'running']);
    }
} catch (PDOException $e) {
    // ignore errors
}

// --- INPUT DEVICES (Mouse, Keyboard) ---
$dataInputJson = safe_exec('powershell -Command "Get-CimInstance Win32_PnPEntity | Where-Object { $_.PNPClass -eq \'Mouse\' -or $_.PNPClass -eq \'Keyboard\' } | Select-Object PNPClass, Name, Status | ConvertTo-Json"');
$dataInput = json_decode($dataInputJson, true);
if ($dataInput && isset($dataInput['PNPClass'])) $dataInput = [$dataInput];
if (!is_array($dataInput)) $dataInput = [];

foreach ($dataInput as &$dev) {
    $dev['Class'] = $dev['PNPClass'] ?? '';
    $dev['FriendlyName'] = $dev['Name'] ?? '';
    unset($dev['PNPClass'], $dev['Name']);
}

// --- USB DEVICES ---
$dataUSBJson = safe_exec('powershell -Command "Get-PnpDevice -PresentOnly | Where-Object { $_.InstanceId -like \'USBSTOR*\' } | Select-Object Class, FriendlyName, Status | ConvertTo-Json"');
$dataUSB = json_decode($dataUSBJson, true);
if ($dataUSB && isset($dataUSB['Class'])) $dataUSB = [$dataUSB];
if (!is_array($dataUSB)) $dataUSB = [];

// --- CHARGER STATUS ---
$chargerJson = safe_exec('powershell -Command "Get-CimInstance Win32_Battery | Select-Object BatteryStatus | ConvertTo-Json"');
$chargerData = json_decode($chargerJson, true);
$chargerStatus = 'Disconnected';
if (is_array($chargerData) && isset($chargerData['BatteryStatus']) && $chargerData['BatteryStatus'] == 2) {
    $chargerStatus = 'Connected';
}
$dataCharger = [[
    'Class' => 'Charger',
    'FriendlyName' => 'Power Adapter',
    'Status' => $chargerStatus
]];

// --- MERGE ALL DEVICES ---
$devicesData = array_merge($dataInput, $dataUSB, $dataCharger);

// --- INSERT CONNECTED DEVICES ---
try {
    $stmtDev = $pdo->prepare("
        INSERT INTO connected_devices (ip_address, device_class, friendly_name, status)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), detected_at = CURRENT_TIMESTAMP
    ");
    foreach ($devicesData as $dev) {
        $deviceClass = $dev['Class'] ?? '';
        $friendlyName = $dev['FriendlyName'] ?? '';
        $devStatus = $dev['Status'] ?? 'Unknown';
        $stmtDev->execute([$ip_address, $deviceClass, $friendlyName, $devStatus]);
    }
} catch (PDOException $e) {
    // ignore
}

// --- MARK OLD DEVICES AS DISCONNECTED ---
try {
    $detected = [];
    foreach ($devicesData as $dev) {
        $detected[] = $pdo->quote($ip_address . "|" . ($dev['Class'] ?? '') . "|" . ($dev['FriendlyName'] ?? ''));
    }
    $detectedList = implode(",", $detected);

    $sql = "UPDATE connected_devices 
            SET status = 'Disconnected', detected_at = CURRENT_TIMESTAMP
            WHERE ip_address = :ip";
    if (!empty($detectedList)) {
        $sql .= " AND CONCAT(ip_address,'|',device_class,'|',friendly_name) NOT IN ($detectedList)";
    }
    $stmtUpdate = $pdo->prepare($sql);
    $stmtUpdate->execute([':ip' => $ip_address]);
} catch (PDOException $e) {
    // ignore
}

// --- ALWAYS RETURN VALID JSON ---
echo json_encode([
    'ip_address' => $ip_address,
    'cpu' => $cpuUsage,
    'memory' => $memUsage,
    'disk' => $diskUsage,
    'status' => $status,
    'opened_applications' => $apps,
    'connected_devices' => $devicesData
]);
?>
