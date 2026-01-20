<?php
// Set maximum execution time to allow long runs (optional)
set_time_limit(0);

// Configuration
$maxIterations = 1000; // Maximum monitoring loops
$sleepDuration = 5;    // Time (seconds) between each loop

function isValidIpAddress($ip) {
    return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP);
}

function buildPingCommandForHost($ip) {
    // Keep it simple and cross-platform.
    // Windows: -n count, -w timeout(ms)
    // Linux:   -c count, -W timeout(seconds)
    if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
        return "ping -n 1 -w 1000 $ip";
    }

    return "ping -c 1 -W 1 $ip";
}

function pingHostOnce($ip, &$debug) {
    $output = [];
    $status = 0;
    $cmd = buildPingCommandForHost($ip);
    exec($cmd, $output, $status);
    $text = implode("\n", $output);

    $debug['ping_cmd'] = $cmd;
    $debug['ping_status'] = $status;
    $debug['ping_output'] = $output;

    if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
        // Typical success: "Reply from ..."
        return ($status === 0) && (stripos($text, 'Reply from') !== false) && (stripos($text, 'unreachable') === false);
    }

    // Linux success patterns vary; be permissive.
    return ($status === 0) && ((stripos($text, 'bytes from') !== false) || (preg_match('/\b1\s+received\b/i', $text) === 1));
}

function checkTcpPorts($ip, $ports, $timeoutSeconds, &$debug) {
    foreach ($ports as $port) {
        $errno = 0;
        $errstr = '';
        $fp = @fsockopen($ip, (int)$port, $errno, $errstr, $timeoutSeconds);
        if (is_resource($fp)) {
            fclose($fp);
            $debug['tcp_open_port'] = (int)$port;
            return true;
        }
    }
    return false;
}

function fetchHttp($url, &$httpCode, &$debug) {
    if (!function_exists('curl_init')) {
        $httpCode = 0;
        $debug['http_error'] = 'cURL not available';
        return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $debug['http_code'] = $httpCode;
    if ($curlErr) {
        $debug['http_error'] = $curlErr;
    }

    return $output;
}

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

        if (!isValidIpAddress($ip)) {
            echo "<p style='color: red; font-weight: bold;'>Invalid IP address: " . htmlspecialchars((string)$ip) . "</p>";
            echo "</div>";
            continue;
        }

        $debug = [];
        $online = false;
        $reason = '';

        // 1) ICMP ping (may be blocked by firewall)
        $pingOk = pingHostOnce($ip, $debug);
        if ($pingOk) {
            $online = true;
            $reason = 'ping';
        }

        // 2) HTTP probe for PC/SERVER (works even if ICMP is blocked)
        $httpResponse = false;
        $httpCode = 0;
        $url = "http://" . $ip . "/monicomlab/get_percentage.php?ip=" . urlencode($ip);
        if (!$online && ($deviceType == 'PC' || $deviceType == 'SERVER')) {
            $httpResponse = fetchHttp($url, $httpCode, $debug);
            if ($httpResponse !== false && $httpCode > 0 && $httpCode < 500) {
                $online = true;
                $reason = 'http';
            }
        }

        // 3) TCP port probe fallback (useful when ICMP is blocked and device doesn't host our endpoint)
        if (!$online) {
            $ports = [80, 443];
            if ($deviceType == 'PC') {
                $ports = [3389, 445, 135, 80, 443];
            } elseif ($deviceType == 'SERVER') {
                $ports = [22, 80, 443, 445];
            }

            if (checkTcpPorts($ip, $ports, 1, $debug)) {
                $online = true;
                $reason = 'tcp';
            }
        }

        if ($online) {
            echo "<p style='color: green;'>✓ Device is reachable (<strong>" . htmlspecialchars($reason) . "</strong>)</p>";

            if ($deviceType == 'PC' || $deviceType == 'SERVER') {
                echo "<p>Device is a PC or SERVER - attempting to get remote content</p>";

                echo "<p>Request URL: " . htmlspecialchars($url) . "</p>";
                if ($httpResponse === false) {
                    $httpResponse = fetchHttp($url, $httpCode, $debug);
                }
                echo "<p>HTTP Code: " . (int)$httpCode . "</p>";
                if ($httpResponse !== false) {
                    echo "<p>Response: " . htmlspecialchars((string)$httpResponse) . "</p>";
                } else {
                    echo "<p style='color: #b45309;'>⚠ Unable to fetch remote content, but device appears reachable.</p>";
                }
            } else {
                $insertSql = "INSERT INTO monitoring_data (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'online')";
                $stmtInsert = $conn->prepare($insertSql);
                $stmtInsert->bind_param("s", $ip);
                $stmtInsert->execute();
                echo "<p>✓ Device status recorded as online in database</p>";
            }

            // ✅ Remove from detect_issue once back online
            $sql = "DELETE FROM detect_issue WHERE ip_address = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $ip);
            $stmt->execute();

            echo "<p style='color: green; font-weight: bold;'>$ip is ONLINE → removed from detect_issue</p>";

        } else {
            // ❌ Device is offline
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
        print_r($debug);
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
