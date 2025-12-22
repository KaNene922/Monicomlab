<?php
header('Content-Type: application/json');
include 'connectMySql.php';


// Create connection using PDO (more secure)
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$db;charset=utf8mb4",$username_server, $password_server);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}


$ip_address = $_GET['ip'] ?? 'unknown';

$sql = "DELETE FROM opened_application WHERE ip_address = ? ";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ip_address]);

$sql = "DELETE FROM detect_issue WHERE ip_address = ? ";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ip_address]);

// Get CPU Usage
$cpuUsage = shell_exec('powershell -Command "Get-Counter -Counter \'\\Processor(_Total)\\% Processor Time\' | Select-Object -ExpandProperty CounterSamples | Select-Object -ExpandProperty CookedValue"');
$cpuUsage = round(floatval(trim($cpuUsage)));

// Memory Usage
$mem = shell_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object -Property TotalVisibleMemorySize,FreePhysicalMemory | ConvertTo-Json"');
$memData = json_decode($mem, true);
$totalMem = $memData['TotalVisibleMemorySize'] ?? 1; // prevent division by zero
$freeMem = $memData['FreePhysicalMemory'] ?? 0;
$usedMem = $totalMem - $freeMem;
$memUsage = round(($usedMem / $totalMem) * 100);

// Disk Usage (C drive)
$disk = shell_exec('powershell -Command "Get-PSDrive C | Select-Object -Property Used,Free | ConvertTo-Json"');
$diskData = json_decode($disk, true);
$usedDisk = $diskData['Used'] ?? 0;
$freeDisk = $diskData['Free'] ?? 1; // prevent division by zero
$diskTotal = $usedDisk + $freeDisk;
$diskUsage = round(($usedDisk / $diskTotal) * 100);

// Status always online
$status = 'online';



if ($cpuUsage >= 1) {
    $value = "CPU : {$cpuUsage}%";
    $insertSql = "INSERT INTO detect_issue (ip_address, name, value, color) VALUES (?, 'WARNING HIGH USAGE', ?, 'warning')";
    $stmtInsert = $pdo->prepare($insertSql);
    $stmtInsert->execute([$ip_address, $value]);
}

if ($memUsage >= 1) {
    $value = "Memory : {$memUsage}%";
    $insertSql = "INSERT INTO detect_issue (ip_address, name, value, color) VALUES (?, 'WARNING HIGH USAGE', ?, 'warning')";
    $stmtInsert = $pdo->prepare($insertSql);
    $stmtInsert->execute([$ip_address, $value]);
}

if ($diskUsage >= 1) {
    $value = "Disk : {$diskUsage}%";
    $insertSql = "INSERT INTO detect_issue (ip_address, name, value, color) VALUES (?, 'WARNING HIGH USAGE', ?, 'warning')";
    $stmtInsert = $pdo->prepare($insertSql);
    $stmtInsert->execute([$ip_address, $value]);
}

// Save monitoring data to DB
try {
    $stmt = $pdo->prepare("INSERT INTO monitoring_data (ip_address, cpu, ram, disk, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $ip_address,
        $cpuUsage ,
        $memUsage ,
        $diskUsage ,
        $status
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database insert failed: ' . $e->getMessage()]);
    exit;
}

// Get opened applications
$cmd = 'powershell -command "gps | where {$_.MainWindowTitle} | select Name,MainWindowTitle | ConvertTo-Json"';
$output = shell_exec($cmd);

$apps = json_decode($output, true);

// Normalize output if only one app
if ($apps && isset($apps['Name']) && isset($apps['MainWindowTitle'])) {
    $apps = [$apps]; // single app wrapped in array
}

if ($apps && is_array($apps)) {
    $stmtApp = $pdo->prepare("INSERT INTO opened_application (ip_address, application, window_title, status) VALUES (?, ?, ?, ?)");
    foreach ($apps as $app) {
        $application = $app['Name'] ?? '';
        $windowTitle = $app['MainWindowTitle'] ?? '';
        $appStatus = 'running';
        try {
            $stmtApp->execute([$ip_address ,$application, $windowTitle, $appStatus]);
        } catch (PDOException $e) {
            // Could log error but continue
        }
    }
}


// Output JSON response
echo json_encode([
    'ip_address' => $ip_address,
    'cpu' => $cpuUsage ,
    'memory' => $memUsage ,
    'disk' => $diskUsage ,
    'status' => $status,
    'opened_applications' => $apps ?: []
]);
