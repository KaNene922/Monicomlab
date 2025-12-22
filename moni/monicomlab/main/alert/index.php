<?php
include '../../connectMySql.php';
include '../../loginverification.php';
if(logged_in()){
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MONICOMLAB - Alerts</title>
    <link rel="icon" type="image/x-icon" href="../../img/logo3.png" />

    <!-- Custom fonts for this template -->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="../../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

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

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Alerts</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Alert ID</th>
                                            <th>Device</th>
                                            <th>Room</th>
                                            <th>IP Address</th>
                                            <th>Device Class</th>
                                            <th>Device Name</th>
                                            <th>Status</th>
                                            <th>Last Detected</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT cd.*, d.device, r.room_name 
                                                 FROM connected_devices cd 
                                                 LEFT JOIN device d ON cd.ip_address = d.ip_address 
                                                 LEFT JOIN rooms r ON d.room_id = r.room_id 
                                                 WHERE cd.id IN (
                                                     SELECT MAX(id) 
                                                     FROM connected_devices 
                                                     GROUP BY ip_address
                                                 )
                                                 ORDER BY cd.detected_at DESC 
                                                 LIMIT 5";
                                        $result = $conn->query($query);
                                        if ($result && $result->num_rows > 0) {
                                            $count = 0;
                                            while (($row = $result->fetch_assoc()) && $count < 5) {
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                if (strtolower($row['status']) == 'connected') {
                                                    $statusClass = 'success';
                                                    $statusText = 'Connected';
                                                } else {
                                                    $statusClass = 'danger';
                                                    $statusText = 'Disconnected';
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td>#" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['device'] ?? 'N/A') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['room_name'] ?? 'N/A') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['device_class']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['friendly_name']) . "</td>";
                                                echo "<td><span class='badge badge-" . $statusClass . "'>" . $statusText . "</span></td>";
                                                echo "<td>" . htmlspecialchars($row['detected_at'] ?? 'Never') . "</td>";
                                                echo "</tr>";
                                                $count++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center text-muted'>No alerts found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <?php include'../footer.php';?>

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

</body>

</html>
<?php
}
else
{
    header('location:../../index.php');
}
?>