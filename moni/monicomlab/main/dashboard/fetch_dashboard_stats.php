<?php
include '../../connectMySql.php';
header('Content-Type: application/json');

$year = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : date('Y');
$response = ['monthly'=>[], 'devices'=>[]];

// Monthly ticket counts
$stmt = $conn->prepare("SELECT MONTH(date) AS m, COUNT(*) AS cnt FROM ticket WHERE YEAR(date)=? GROUP BY MONTH(date)");
$stmt->bind_param('i', $year);
if($stmt->execute()){
	$res = $stmt->get_result();
	$months = array_fill(1,12,0);
	while($r = $res->fetch_assoc()){
		$months[(int)$r['m']] = (int)$r['cnt'];
	}
	$labels = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
	for($i=1;$i<=12;$i++) $response['monthly'][] = ['month'=>$labels[$i], 'count'=>(int)$months[$i]];
}
$stmt->close();

// Device counts
$q = "SELECT UPPER(device) AS device_type, COUNT(*) AS cnt FROM device GROUP BY device_type ORDER BY cnt DESC";
if($r = $conn->query($q)){
	while($row = $r->fetch_assoc()){
		$response['devices'][] = ['type'=>$row['device_type'], 'count'=>(int)$row['cnt']];
	}
}

echo json_encode($response);
$conn->close();
?>
