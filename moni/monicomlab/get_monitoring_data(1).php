<?php
include 'connectMySql.php';
$query = "SELECT * FROM device";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {

$ip = $row['ip_address'];

exec("ping -n 1 $ip", $output, $status);
$pingResult = implode("\n", $output);

if (strpos($pingResult, "Reply from") !== false && strpos($pingResult, "unreachable") === false) {
echo 'step1';

if($row['device'] == 'PC'|| $row['device'] == 'SERVER'){
echo 'step2';

if (!function_exists('getRemoteContent')) {
    function getRemoteContent($url) {
        echo 'step3';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
    $url = "http://".$ip."/monicomlab/get_percentage.php?ip=".$ip."";
    echo $url;
    $response = getRemoteContent($url);
}
else{
    $insertSql = "INSERT INTO monitoring_data  (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'online')";
    $stmtInsert = $conn->prepare($insertSql);
    $stmtInsert->bind_param("s", $ip);
    $stmtInsert->execute();
}

    echo "$ip is <span style='color: green;'>Online</span>";


} else {
    echo "$ip is <span style='color: red;'>Offline</span>";

        // Insert new record because none exists with cpu=0,ram=0,temp=0 for this IP
        $insertSql = "INSERT INTO monitoring_data  (ip_address, cpu, ram, disk, status) VALUES (?, 0, 0, 0, 'offline')";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("s", $ip);
        $stmtInsert->execute();

        $sql = "DELETE FROM detect_issue WHERE ip_address = ? ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ip]);

        $sql = "DELETE FROM opened_application WHERE ip_address = ? ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ip]);

        $insertSql = "INSERT INTO detect_issue  (ip_address, name, value, color) VALUES (?, 'CONNECTION LOST', 'offline', 'danger')";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("s", $ip);
        $stmtInsert->execute();
}

echo "<pre>";
print_r($output);
echo "</pre>";
}
$conn->close();

?>