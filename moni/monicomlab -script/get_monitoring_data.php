<?php
// Set maximum execution time to allow long runs (optional)
set_time_limit(0);

// Configuration
$maxIterations = 1000; // Maximum monitoring loops
$sleepDuration = 5;    // Time (seconds) between each loop

// Function to remove duplicate monitoring data
function removeDuplicateMonitoringDataAlternative($conn) {
    $deleteQuery = "
        DELETE FROM monitoring_data 
        WHERE id NOT IN (
            SELECT id FROM (
                SELECT MAX(id) as id 
                FROM monitoring_data 
                GROUP BY ip_address, DATE(created_at)
            ) as latest_records
        )
    ";

    $result = $conn->query($deleteQuery);

    if ($result) {
        $affectedRows = $conn->affected_rows;
        echo "<p style='color: green;'>✓ Duplicate monitoring data cleaned up. Removed $affectedRows duplicate records</p>";
        return true;
    } else {
        echo "<p style='color: red;'>✗ Error cleaning duplicate data: " . $conn->error . "</p>";
        return false;
    }
}

// Start monitoring
date_default_timezone_set('Asia/Manila');

for ($iteration = 1; $iteration <= $maxIterations; $iteration++) {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 5px solid #4CAF50;'>";
    echo "<h3>Iteration $iteration of $maxIterations</h3>";
    echo "<p>Monitoring cycle started at: " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";

    include 'connectMySql.php';

    // Clean duplicate monitoring data
    echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo "<h4>Checking for duplicate monitoring data...</h4>";
    removeDuplicateMonitoringDataAlternative($conn);
    echo "</div>";

    $query = "SELECT * FROM device";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $ip = $row['ip_address'];
        $deviceType = $row['device'];

        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h4>Monitoring: $ip ($deviceType)</h4>";

        // Clear previous ping results for each device
        $output = [];
        $status = 0;

        // Ping test
        exec("ping -n 1 $ip", $output, $status);
        $pingResult = implode("\n", $output);

        // Determine if device is online
        if (strpos($pingResult, "Reply from") !== false && strpos($pingResult, "unreachable") === false && $status === 0) {
            echo "<p style='color: green;'>✓ Device is reachable</p>";

            if ($deviceType == 'PC' || $deviceType == 'SERVER') {
                echo "<p>Device is a PC or SERVER - attempting to get remote content</p>";

                if (!function_exists('getRemoteContent')) {
                    function getRemoteContent($url) {
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                        $output = curl_exec($ch);
                        curl_close($ch);
                        return $output;
                    }
                }

                $url = "http://" . $ip . "/monicomlab/get_percentage.php?ip=" . $ip;
                echo "<p>Request URL: $url</p>";
                $response = getRemoteContent($url);
                echo "<p>Response: " . htmlspecialchars($response) . "</p>";
            } else {
                $insertSql = "INSERT INTO monitoring_data (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'online')";
                $stmtInsert = $conn->prepare($insertSql);
                $stmtInsert->bind_param("s", $ip);
                $stmtInsert->execute();
                echo "<p>✓ Device status recorded as online in database</p>";
            }

            // Remove from detect_issue once back online
            $sql = "DELETE FROM detect_issue WHERE ip_address = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ip);
            $stmt->execute();

            echo "<p style='color: green; font-weight: bold;'>$ip is ONLINE → removed from detect_issue</p>";

        } else {
            // Device is offline
            echo "<p style='color: red; font-weight: bold;'>$ip is OFFLINE</p>";

            // Insert offline record
            $insertSql = "INSERT INTO monitoring_data (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'offline')";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("s", $ip);
            $stmtInsert->execute();

            // Remove any existing detect_issue and opened_application records for this IP
            $sql = "DELETE FROM detect_issue WHERE ip_address = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ip);
            $stmt->execute();

            $sql = "DELETE FROM opened_application WHERE ip_address = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ip);
            $stmt->execute();

            // Add offline issue
            $insertSql = "INSERT INTO detect_issue (ip_address, name, value, color) VALUES (?, 'CONNECTION LOST', 'offline', 'danger')";
            $stmtInsert = $conn->prepare($insertSql);
            $stmtInsert->bind_param("s", $ip);
            $stmtInsert->execute();

            echo "<p>✓ Device status recorded as offline in database</p>";
            echo "<p>✓ Related records cleaned up</p>";
        }

        echo "<details style='margin-top: 10px;'>";
        echo "<summary>Ping Details</summary>";
        echo "<pre style='background: #eee; padding: 10px;'>";
        print_r($output);
        echo "</pre>";
        echo "</details>";
        echo "</div>";
    }

    $conn->close();

    // Divider between iterations
    echo "<hr style='margin: 20px 0; border: 1px dashed #ccc;'>";

    if ($iteration < $maxIterations) {
        sleep($sleepDuration);
    }
}

echo "<div style='background: #e1f5fe; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center;'>";
echo "<h3>Monitoring Complete</h3>";
echo "<p>Finished $maxIterations iterations of device monitoring</p>";
echo "<p>Final iteration completed at: " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";
?>
