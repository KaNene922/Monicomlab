<?php
require_once __DIR__ . '/../../connectMySql.php';
require_once __DIR__ . '/../../loginverification.php';
if(logged_in()){
$session_user_id = $_SESSION['user_id'];


if(isset($_POST['submit']))
{
        $email = $_POST['email'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $condition = "";

        $target_dir = "../../image/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        } 
        $image = basename($_FILES["image"]["name"]);

        $condition = $image != '' ? " , image = '".$image."' ":'';

        $sql= sprintf("UPDATE admin
        SET 
        email = '". $email ."',
        password  = '". $password ."',
        name  = '". $name ."' $condition
        WHERE user_id = '". $session_user_id ."'");
        $result = mysqli_query($conn, $sql);
        
        echo "<script src='../../js/sweetalert2.all.min.js'></script>
            <body onload='save()'></body>
            <script> 
            function save(){
            Swal.fire(
                 'Record Saved!',
                 '',
                 'success'
               )
            }</script>";
}

$email = "";
$password = "";
$name = "";
$image = "";

$query = "SELECT * FROM admin WHERE user_id = '".$session_user_id."'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
$email = $row['email'];
$password = $row['password'];
$name = $row['name'];
$image = $row['image'];
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

    <title>MONICOMLAB </title>
    <link rel="icon" type="image/x-icon" href="../../img/logo1.png"/>

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

                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Edit Profile</h1>
                    </div>

                         <div class="row">
                            <div class="col-xl-4">
                                <!-- Profile picture card-->
                                <div class="card mb-4 mb-xl-0">
                                    <div class="card-header">Profile Picture</div>
                                    <div class="card-body text-center">
                                        <!-- Profile picture image-->
                                        <img class="img-account-profile rounded-circle mb-2" style="height:200px;width: 200px;"  src="../../image/<?=$image;?>">
                                        <!-- Profile picture help block-->
                                        <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                                        <!-- Profile picture upload button-->
                                        <form method="post"  enctype="multipart/form-data">
                                        <input class="form-control btn btn-primary" type="file" accept="image/*"  name="image">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <!-- Account details card-->
                                <div class="card mb-4">
                                    <div class="card-header">Account Details</div>
                                    <div class="card-body">
                                        <div class="row gx-3 mb-3">
                                            <div class="col-md-12">
                                                <label class="small mb-1" for="name">Name</label>
                                                <input class="form-control" name="name" id="name" type="text" placeholder="Enter your name" value="<?= $name;?>" required>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small mb-1" for="email">Email</label>
                                                <input class="form-control" name="email" id="email" type="text" placeholder="Enter your email" value="<?= $email;?>" required>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small mb-1" for="password">Password</label>
                                                <input class="form-control" name="password" id="password" type="password" placeholder="Enter your password" value="<?= $password;?>" required>
                                            </div>
                                        </div>
                                                <button class="btn btn-primary" name="submit" type="submit">Save</button>
                                            
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>  


                </div>
            </div>
        <?php include'../footer.php';?>
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
    <script src="../../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/demo/chart-area-demo.js"></script>
    <script src="../../js/demo/chart-pie-demo.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
      $(function () {
        $("#dataTable").DataTable({
          "responsive": true,
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