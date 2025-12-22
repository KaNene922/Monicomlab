<?php
include('../../connectMySql.php');

date_default_timezone_set('Asia/Manila'); 
$current_datetime = date('Y-m-d H:i:s');

include '../../loginverification.php';
if (logged_in()) {
    $device_name = "";
    $issue_type = "";
    $description = "";

    if (isset($_GET['id'])) {
        $query = "SELECT * FROM device WHERE id ='" . $_GET['id'] . "'";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
            $device = $row['device'];
            $ip_address = $row['ip_address'];
        }

        if (isset($_POST['btn_save'])) {
            $user_id = $_GET['id'];
            $name = $_POST['name'];
            $device = $_POST['device'];
            $ip_address = $_POST['ip_address'];

            $sql = "UPDATE device
                    SET 
                    name = '" . $name . "',
                    device  = '" . $device . "',
                    ip_address  = '" . $ip_address . "'
                    WHERE id = '" . $user_id . "'";
            $result = mysqli_query($conn, $sql);
            header("location:index.php");
        }
    } else {
        if (isset($_POST['btn_save'])) {
            $device_name = $_POST['device_name'];
            $issue_type = $_POST['issue_type'];
            $description = $_POST['description'];

            // ✅ Always get current Philippine time in real time
            $current_datetime = date('Y-m-d H:i:s');

            // Determine category based on issue type
            $category = 'GENERAL'; // Default category
            $issue_lower = strtolower($issue_type);

            // Network Issues
            if (strpos($issue_lower, 'offline') !== false ||
                strpos($issue_lower, 'network') !== false ||
                strpos($issue_lower, 'connection') !== false ||
                strpos($issue_lower, 'stale data') !== false ||
                strpos($issue_lower, 'router') !== false ||
                strpos($issue_lower, 'switch') !== false) {
                $category = 'NETWORK';
            }
            // Hardware Issues
            elseif (strpos($issue_lower, 'mouse') !== false ||
                    strpos($issue_lower, 'keyboard') !== false ||
                    strpos($issue_lower, 'printer') !== false ||
                    strpos($issue_lower, 'usb') !== false ||
                    strpos($issue_lower, 'audio') !== false ||
                    strpos($issue_lower, 'speaker') !== false ||
                    strpos($issue_lower, 'microphone') !== false ||
                    strpos($issue_lower, 'webcam') !== false ||
                    strpos($issue_lower, 'camera') !== false ||
                    strpos($issue_lower, 'monitor') !== false ||
                    strpos($issue_lower, 'display') !== false ||
                    strpos($issue_lower, 'hardware') !== false ||
                    strpos($issue_lower, 'device disconnected') !== false) {
                $category = 'HARDWARE';
            }
            // Software Issues
            elseif (strpos($issue_lower, 'cpu') !== false ||
                    strpos($issue_lower, 'ram') !== false ||
                    strpos($issue_lower, 'memory') !== false ||
                    strpos($issue_lower, 'disk') !== false ||
                    strpos($issue_lower, 'application') !== false ||
                    strpos($issue_lower, 'software') !== false ||
                    strpos($issue_lower, 'windows update') !== false ||
                    strpos($issue_lower, 'system file') !== false ||
                    strpos($issue_lower, 'registry') !== false ||
                    strpos($issue_lower, 'driver') !== false ||
                    strpos($issue_lower, 'dll') !== false ||
                    strpos($issue_lower, 'crash') !== false ||
                    strpos($issue_lower, 'freeze') !== false ||
                    strpos($issue_lower, 'installation') !== false ||
                    strpos($issue_lower, 'license') !== false ||
                    strpos($issue_lower, 'compatibility') !== false ||
                    strpos($issue_lower, 'leak') !== false ||
                    strpos($issue_lower, 'performance') !== false ||
                    strpos($issue_lower, 'startup') !== false ||
                    strpos($issue_lower, 'shutdown') !== false ||
                    strpos($issue_lower, 'antivirus') !== false ||
                    strpos($issue_lower, 'firewall') !== false ||
                    strpos($issue_lower, 'security') !== false ||
                    strpos($issue_lower, 'malware') !== false ||
                    strpos($issue_lower, 'access denied') !== false ||
                    strpos($issue_lower, 'high usage') !== false) {
                $category = 'SOFTWARE';
            }

            // Determine severity
            $severity = 'Medium';
            if (strpos($issue_lower, 'critical') !== false ||
                strpos($issue_lower, 'crash') !== false ||
                strpos($issue_lower, 'offline') !== false ||
                strpos($issue_lower, 'system file corruption') !== false ||
                strpos($issue_lower, 'malware detection') !== false) {
                $severity = 'Critical';
            } elseif (strpos($issue_lower, 'warning') !== false ||
                      strpos($issue_lower, 'high usage') !== false ||
                      strpos($issue_lower, 'memory leak') !== false ||
                      strpos($issue_lower, 'security') !== false ||
                      strpos($issue_lower, 'antivirus alert') !== false) {
                $severity = 'High';
            } elseif (strpos($issue_lower, 'slow') !== false ||
                      strpos($issue_lower, 'startup') !== false ||
                      strpos($issue_lower, 'shutdown') !== false ||
                      strpos($issue_lower, 'compatibility') !== false) {
                $severity = 'Low';
            }

            // ✅ Insert with correct timezone timestamp
            $sql = "INSERT INTO ticket (
                        device_name,
                        issue_type,
                        description,
                        date,
                        category,
                        severity
                    )
                    VALUES (
                        '" . $device_name . "',
                        '" . $issue_type . "',
                        '" . $description . "',
                        '" . $current_datetime . "',
                        '" . $category . "',
                        '" . $severity . "'
                    )";
            $result = mysqli_query($conn, $sql);
            header("location:index.php");
        }
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MONICOMLAB</title>

    <!-- Custom fonts for this template-->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">


    <!-- Custom styles for this template-->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

       <?php include'../sidebar.php';?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

               <?php include'../nav.php';?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                         <div class="container">

                            <div class="card o-hidden border-0 shadow-lg my-5">
                                <div class="card-body p-0">
                                    <!-- Nested Row within Card Body -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="p-5">
                                                <div class="text-center">
                                                    <h1 class="h4 text-gray-900 mb-4">Add Ticket</h1>
                                                </div>
                                                <form  method="post">
                                                    <div class="form-group row">
                                                        
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>Device Name</p>
                                                                <select class="form-control form-control" name="device_name" required>
                                                                    <option value="">---SELECT---</option>
                                                                    <?php
                                                                    $query = "SELECT * FROM device";
                                                                    $result = $conn->query($query);
                                                                    while ($row = $result->fetch_assoc()) {
                                                                        echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                        </div>
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>Issue Type </p>
                                                                <select class="form-control form-control" name="issue_type" required>
                                                                    <option value="">---SELECT---</option>
                                                                    
                                                                    <!-- NETWORK Issues -->
                                                                    <optgroup label="🔵 NETWORK ISSUES">
                                                                        <option value="OFFLINE PC">OFFLINE PC</option>
                                                                        <option value="OFFLINE SERVER">OFFLINE SERVER</option>
                                                                        <option value="OFFLINE ROUTER">OFFLINE ROUTER</option>
                                                                        <option value="OFFLINE SWITCH">OFFLINE SWITCH</option>
                                                                        <option value="Device Offline">Device Offline</option>
                                                                        <option value="Stale Data">Stale Data</option>
                                                                        <option value="Connection Lost">Connection Lost</option>
                                                                        <option value="Network Connectivity">Network Connectivity</option>
                                                                    </optgroup>
                                                                    
                                                                    <!-- SOFTWARE Issues -->
                                                                    <optgroup label="🟡 SOFTWARE ISSUES">
                                                                        <!-- Resource Monitoring (PC & Server) -->
                                                                        <option value="CPU Warning">CPU Warning</option>
                                                                        <option value="CPU Critical">CPU Critical</option>
                                                                        <option value="RAM Warning">RAM Warning</option>
                                                                        <option value="RAM Critical">RAM Critical</option>
                                                                        <option value="Disk Warning">Disk Warning</option>
                                                                        <option value="Disk Critical">Disk Critical</option>
                                                                        <option value="HIGH USAGE CPU PC">HIGH USAGE CPU PC</option>
                                                                        <option value="HIGH USAGE DISK PC">HIGH USAGE DISK PC</option>
                                                                        <option value="HIGH USAGE MEMORY PC">HIGH USAGE MEMORY PC</option>
                                                                        <option value="HIGH USAGE MEMORY SERVER">HIGH USAGE MEMORY SERVER</option>
                                                                        <option value="HIGH USAGE DISK SERVER">HIGH USAGE DISK SERVER</option>
                                                                        <option value="HIGH USAGE RAM SERVER">HIGH USAGE RAM SERVER</option>
                                                                        
                                                                        <!-- System Issues -->
                                                                        <option value="Windows Update Failed">Windows Update Failed</option>
                                                                        <option value="System File Corruption">System File Corruption</option>
                                                                        <option value="Registry Errors">Registry Errors</option>
                                                                        <option value="Driver Issues">Driver Issues</option>
                                                                        <option value="DLL Errors">DLL Errors</option>
                                                                        
                                                                        <!-- Application Issues -->
                                                                        <option value="Application Crash">Application Crash</option>
                                                                        <option value="Software Freeze">Software Freeze</option>
                                                                        <option value="Installation Failed">Installation Failed</option>
                                                                        <option value="License Expired">License Expired</option>
                                                                        <option value="Compatibility Issues">Compatibility Issues</option>
                                                                        
                                                                        <!-- Performance Issues -->
                                                                        <option value="Memory Leak">Memory Leak</option>
                                                                        <option value="High CPU Usage">High CPU Usage</option>
                                                                        <option value="Slow Performance">Slow Performance</option>
                                                                        <option value="Startup Issues">Startup Issues</option>
                                                                        <option value="Shutdown Problems">Shutdown Problems</option>
                                                                        
                                                                        <!-- Security Issues -->
                                                                        <option value="Antivirus Alert">Antivirus Alert</option>
                                                                        <option value="Firewall Problems">Firewall Problems</option>
                                                                        <option value="Security Updates">Security Updates</option>
                                                                        <option value="Malware Detection">Malware Detection</option>
                                                                        <option value="Access Denied">Access Denied</option>
                                                                    </optgroup>
                                                                    
                                                                    <!-- HARDWARE Issues -->
                                                                    <optgroup label="🟢 HARDWARE ISSUES">
                                                                        <!-- Peripheral Devices Only -->
                                                                        <option value="Mouse Issues">Mouse Issues</option>
                                                                        <option value="Keyboard Issues">Keyboard Issues</option>
                                                                        <option value="Printer Issues">Printer Issues</option>
                                                                        <option value="USB Device Issues">USB Device Issues</option>
                                                                        <option value="Audio Issues">Audio Issues</option>
                                                                        <option value="Speaker Problems">Speaker Problems</option>
                                                                        <option value="Microphone Issues">Microphone Issues</option>
                                                                        <option value="Webcam Issues">Webcam Issues</option>
                                                                        <option value="Camera Problems">Camera Problems</option>
                                                                        <option value="Monitor Issues">Monitor Issues</option>
                                                                        <option value="Display Problems">Display Problems</option>
                                                                        <option value="Hardware Error">Hardware Error</option>
                                                                        <option value="Device Disconnected">Device Disconnected</option>
                                                                    </optgroup>
                                                                    
                                                                </select>
                                                        </div>
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>Description </p>
                                                                <textarea rows="5" name="description" class="form-control"></textarea>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group row">
                                                    <button type="submit" name="btn_save" class="btn btn-primary btn-user btn-block col-sm-6"> Submit </button>
                                                    <hr>
                                                    <a href="index.php" class="btn btn-google btn-user btn-block col-sm-6"> Cancel </a>
                                                    </div>
                                                </form>
                                                <hr>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

            </div>
        </div>
            
            <!-- End of Main Content -->

            <?php include'../footer.php';?>

        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../js/sb-admin-2.min.js"></script>

    
    <!-- Page level plugins -->
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/demo/datatables-demo.js"></script>
    <script>
      $(function () {
        $("#dataTable").DataTable({
          "responsive": true,
          "autoWidth": false,
          "bDestroy": true,
        });
      });
    </script>
</body>

</html>
<?php
}
else
{
    header('location:../../index.php');
}?>