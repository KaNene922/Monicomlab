<?php
include 'connectMySql.php';

$ip = '192.168.1.1';

exec("ping -n 1 $ip", $output, $status);
$pingResult = implode("\n", $output);

if (strpos($pingResult, "Reply from") !== false && strpos($pingResult, "unreachable") === false) {
echo 'step1';


echo 'step2';

    function getRemoteContent($url) {
echo 'step3';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    $response = getRemoteContent("http://".$ip.":6969/netmofx/get_percentage.php?ip=".$ip."");

    echo "$ip is <span style='color: green;'>Online</span>";


} 
echo "<pre>";
print_r($output);
echo "</pre>";

?>