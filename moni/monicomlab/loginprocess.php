<?php
include 'connectMySql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        // Display error message if username or password is empty
        echo "<script src='js/sweetalert2.all.min.js'></script>
           <body onload='error()'></body>
           <script> 
           function error(){
           Swal.fire({
                icon: 'error',
                title: 'Login failed!'
           })
           }</script>";
        include 'index.php';
    } else {

        $pdo = new PDO($pdoDsn, $username_server, $password_server, $pdoOptions);
        
        // Prepare a SQL statement with placeholders
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email AND password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
       
        $rowCount = $stmt->rowCount();

            if ($rowCount > 0) {
            // Fetch multiple rows using a while loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               
                $user_id = $row['user_id'];
                $name = $row['name'];
                $image = $row['image'];
                

                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['name'] = $name;
                $_SESSION['image'] = $image;
               
                header('location:main/dashboard/');
            }
        }


        echo "<script src='js/sweetalert2.all.min.js'></script>
           <body onload='error()'></body>
           <script> 
           function error(){
           Swal.fire({
                icon: 'error',
                title: 'Login failed!'
           })
           }</script>";
        include 'index.php';
    }
}
?>
