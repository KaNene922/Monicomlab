<?php
include('../../connectMySql.php');
include '../../loginverification.php';

if(logged_in()) {
    if(isset($_POST['device_id'])) {
        $device_id = $_POST['device_id'];
        
        try {
            // First, get the IP address of the device
            $sql_get_ip = "SELECT ip_address FROM device WHERE id = ?";
            $stmt_get_ip = mysqli_prepare($conn, $sql_get_ip);
            mysqli_stmt_bind_param($stmt_get_ip, "i", $device_id);
            mysqli_stmt_execute($stmt_get_ip);
            $result = mysqli_stmt_get_result($stmt_get_ip);
            
            if($row = mysqli_fetch_assoc($result)) {
                $ip_address = $row['ip_address'];
                
                // Delete related monitoring data first
                $sql2 = "DELETE FROM monitoring_data WHERE ip_address = ?";
                $stmt2 = mysqli_prepare($conn, $sql2);
                mysqli_stmt_bind_param($stmt2, "s", $ip_address);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                
                // Then delete the device record from device table
                $sql = "DELETE FROM device WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $device_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    echo 'success';
                } else {
                    echo 'Error deleting device: ' . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                echo 'Device not found';
            }
            
            mysqli_stmt_close($stmt_get_ip);
        } catch(Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo 'Device ID not provided';
    }
} else {
    echo 'Unauthorized access';
}
?>