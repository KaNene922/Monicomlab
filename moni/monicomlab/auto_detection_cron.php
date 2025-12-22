<?php
// Automatic Issue Detection Cron Job
// This script should be run periodically (e.g., every 5 minutes) via cron job or Windows Task Scheduler

include 'connectMySql.php';
include 'main/troubleshoot/automatic_issue_detection.php';

// Set timezone for proper logging
date_default_timezone_set('Asia/Manila');

// Log file for debugging
$logFile = 'logs/auto_detection.log';
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

logMessage("Starting automatic issue detection...");

try {
    // Run the automatic issue detection
    $issues = detectSystemIssues();
    $issueCount = count($issues);
    
    logMessage("Scan completed. Issues detected: $issueCount");
    
    if ($issueCount > 0) {
        logMessage("Issues found:");
        foreach ($issues as $issue) {
            if ($issue['success']) {
                logMessage("- Created ticket #{$issue['ticket_id']}: {$issue['device']} - {$issue['issue_type']}");
            } else {
                logMessage("- Failed to create ticket: {$issue['message']}");
            }
        }
    } else {
        logMessage("No new issues detected.");
    }
    
    // Update system health summary
    updateSystemHealthSummary();
    
} catch (Exception $e) {
    logMessage("Error during automatic detection: " . $e->getMessage());
}

logMessage("Automatic issue detection completed.\n");

function updateSystemHealthSummary() {
    global $conn;
    
    try {
        // Create or update system health summary
        $healthQuery = "
            SELECT 
                d.name as device_name,
                d.ip_address,
                md.cpu, md.ram, md.disk, md.status,
                COUNT(t.id) as active_issues,
                CASE 
                    WHEN md.status = 'offline' THEN 0
                    WHEN md.cpu > 90 OR md.ram > 90 OR md.disk > 95 THEN 25
                    WHEN md.cpu > 70 OR md.ram > 75 OR md.disk > 80 THEN 50
                    ELSE 100
                END as health_score
            FROM device d
            LEFT JOIN (
                SELECT md1.*
                FROM monitoring_data md1
                INNER JOIN (
                    SELECT ip_address, MAX(id) AS max_id
                    FROM monitoring_data
                    GROUP BY ip_address
                ) md2 ON md1.ip_address = md2.ip_address AND md1.id = md2.max_id
            ) md ON d.ip_address = md.ip_address
            LEFT JOIN ticket t ON d.name = t.device_name AND t.status IN ('Pending', 'PENDING')
            GROUP BY d.id, d.name, d.ip_address, md.cpu, md.ram, md.disk, md.status
        ";
        
        $result = $conn->query($healthQuery);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                logMessage("Device health: {$row['device_name']} - Score: {$row['health_score']}%, Issues: {$row['active_issues']}");
            }
        }
        
    } catch (Exception $e) {
        logMessage("Error updating system health summary: " . $e->getMessage());
    }
}
?>