<?php
include('../../connectMySql.php');
include '../../loginverification.php';
if(logged_in()){
$name = "";
$UserTypeId = "";
$address = "";
$device = "";
$Description = "";
$ip_address = "";

if(isset($_GET['id']))
{
    $query = "SELECT * FROM device WHERE id ='".$_GET['id']."'";
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $name =$row['name']; 
        $device = $row['device'];
        $ip_address = $row['ip_address'];
        $room_id = $row['room_id'];
    }

    if(isset($_POST['btn_save']))
    {
        $user_id = $_GET['id'];
        $name = $_POST['name'];
        $device = $_POST['device'];
        $ip_address = $_POST['ip_address'];
        $room_id = $_POST['room_id'];
        
        $sql= "UPDATE device
        SET 
        name = '". $name ."',
        device  = '". $device ."',
        ip_address  = '". $ip_address ."',
        room_id = '". $room_id ."' // Add this line
        WHERE id = '". $user_id ."'";
        $result = mysqli_query($conn, $sql);
        header("location:index.php");
    }
}
else
{
    if(isset($_POST['btn_save']))
    {
        $name = $_POST['name'];
        $device = $_POST['device'];
        $ip_address = $_POST['ip_address'];
        $room_id = $_POST['room_id'];

        $sql = "INSERT INTO device (Name, device, ip_address, room_id)
        VALUES ('". $name ."','". $device."','". $ip_address."','". $room_id."')";

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
                                                    <h1 class="h4 text-gray-900 mb-4">Add Device</h1>
                                                </div>
                                                <form method="post">
                                                    <div class="form-group row">
                                                        <div class="col-sm-12 col-12 mb-sm-0">
                                                            <p>Name</p>
                                                            <input type="text" class="form-control form-control"
                                                                name="name" value="<?php echo $name;?>" required>
                                                        </div>
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>Device </p>
                                                            <select class="form-control form-control" name="device">
                                                                <option value="PC" <?php echo ($device == 'PC') ? 'selected' : ''; ?>>PC</option>
                                                                <option value="SERVER" <?php echo ($device == 'SERVER') ? 'selected' : ''; ?>>SERVER</option>
                                                                <option value="ROUTER" <?php echo ($device == 'ROUTER') ? 'selected' : ''; ?>>ROUTER</option>
                                                                <option value="SWITCH" <?php echo ($device == 'SWITCH') ? 'selected' : ''; ?>>SWITCH</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>IP Address</p>
                                                            <input type="text" class="form-control form-control"
                                                                name="ip_address" value="<?php echo $ip_address;?>" required>
                                                        </div>
                                                        <div class="col-sm-12 col-12 mt-3">
                                                            <p>Room</p>
                                                            <select class="form-control form-control" name="room_id" required>
                                                                <option value="">-- Select Room --</option>
                                                                <?php
                                                                // Fetch all rooms from the database
                                                                $room_query = "SELECT * FROM rooms ORDER BY room_name";
                                                                $room_result = $conn->query($room_query);
                                                                
                                                                if ($room_result->num_rows > 0) {
                                                                    while($room = $room_result->fetch_assoc()) {
                                                                        $selected = (isset($room_id) && $room_id == $room['room_id']) ? 'selected' : '';
                                                                        echo "<option value='" . $room['room_id'] . "' $selected>" . $room['room_name'] . "</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-group row">
                                                        <button type="submit" name="btn_save" class="btn btn-primary btn-user btn-block col-sm-6"> Save </button>
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