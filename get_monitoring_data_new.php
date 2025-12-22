<?php
include 'connectMySql.php';

$query = "SELECT * FROM device";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $ip = $row['ip_address'];

    exec("ping -n 1 $ip", $output, $status);
    $pingResult = implode("\n", $output);

    if (strpos($pingResult, "Reply from") !== false && strpos($pingResult, "unreachable") === false) {
            
    if($row['device'] == 'PC'|| $row['device'] == 'SERVER'){
    echo 'step2';

        function getRemoteContent($url) {
    echo 'step3';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }
        $response = getRemoteContent("http://".$ip."/netmofx/get_percentage.php?ip=".$ip."");
    }
    else{
        $insertSql = "INSERT INTO monitoring_data  (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'online')";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("s", $ip);
        $stmtInsert->execute();
    }


        echo "$ip is <span style='color: green;'>Online</span><br>";
    } else {
        $insertSql = "INSERT INTO monitoring_data  (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'offline')";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("s", $ip);
        $stmtInsert->execute();

        $sql = "DELETE FROM detect_issue WHERE ip_address = ? ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ip]);

        $insertSql = "INSERT INTO detect_issue  (ip_address, name, value, color) VALUES (?, 'CONNECTION LOST', 'offline', 'danger')";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("s", $ip);
        $stmtInsert->execute();

        echo "$ip is <span style='color: red;'>Offline</span><br>";
    }

    echo "<pre>";
    print_r($output);
    echo "</pre>";

    // Clear output array for next loop
    $output = [];
}

$conn->close();
?>
