<?php
include '../../connectMySql.php';

$ip_address = $_GET['ip_address'];

$sql = "SELECT application, window_title, status FROM opened_application WHERE ip_address = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();

$apps = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $apps[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($apps);

$stmt->close();
$conn->close();
?>