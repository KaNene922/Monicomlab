<?php
include 'connectMySql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $location = trim($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Please enter a valid name (at least 2 characters)';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (empty($department)) {
        $errors[] = 'Please select a department';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=monicomlab', $username_server, $password_server);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors[] = 'This email is already registered';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // If there are errors, show them
    if (!empty($errors)) {
        echo "<script src='js/sweetalert2.all.min.js'></script>
           <body onload='showErrors()'></body>
           <script> 
           function showErrors(){
           Swal.fire({
                icon: 'error',
                title: 'Registration Failed!',
                html: '" . implode('<br>', $errors) . "'
           }).then(function() {
                window.location.href = 'register.php';
           });
           }</script>";
        exit();
    }

    // Create full location string
    $full_location = $department;
    if (!empty($location)) {
        $full_location .= ' - ' . $location;
    }

    // Insert new user
    try {
        // Detect schema differences (older deployments may not have department/location columns)
        $existingAdminColumns = [];
        try {
            $columnsStmt = $pdo->query("SHOW COLUMNS FROM admin");
            $existingAdminColumns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            // If SHOW COLUMNS is not permitted, fall back to a minimal insert below.
            $existingAdminColumns = [];
        }

        $insertColumns = ['email', 'password', 'name', 'status'];
        $insertPlaceholders = [':email', ':password', ':name', ':status'];
        $params = [
            ':email' => $email,
            ':password' => $password,
            ':name' => $name,
            ':status' => 'ACTIVE',
        ];

        // Only include these columns if they exist in the current DB schema
        if (in_array('department', $existingAdminColumns, true)) {
            $insertColumns[] = 'department';
            $insertPlaceholders[] = ':department';
            $params[':department'] = $department;
        }
        if (in_array('location', $existingAdminColumns, true)) {
            $insertColumns[] = 'location';
            $insertPlaceholders[] = ':location';
            $params[':location'] = $full_location;
        }

        $sql = sprintf(
            'INSERT INTO admin (%s) VALUES (%s)',
            implode(', ', $insertColumns),
            implode(', ', $insertPlaceholders)
        );
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Success message
        echo "<script src='js/sweetalert2.all.min.js'></script>
           <body onload='success()'></body>
           <script> 
           function success(){
           Swal.fire({
                icon: 'success',
                title: 'Account Created Successfully!',
                text: 'You can now login with your credentials',
                confirmButtonText: 'Go to Login'
           }).then(function() {
                window.location.href = 'index.php';
           });
           }</script>";
    } catch (PDOException $e) {
        echo "<script src='js/sweetalert2.all.min.js'></script>
           <body onload='error()'></body>
           <script> 
           function error(){
           Swal.fire({
                icon: 'error',
                title: 'Registration Failed!',
                text: 'Database error: " . addslashes($e->getMessage()) . "'
           }).then(function() {
                window.location.href = 'register.php';
           });
           }</script>";
    }
} else {
    header('Location: register.php');
    exit();
}
?>
