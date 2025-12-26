<?php

$servername = "localhost"; 
$username_server = "monicomlab_user";
$password_server = "YOUR_PASSWORD";
$db = "monicomlab";
$conn = mysqli_connect($servername, $username_server, $password_server,$db);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
?>