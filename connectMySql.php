<?php

$servername = getenv('MONICOMLAB_DB_HOST') ?: 'localhost';
$username_server = getenv('MONICOMLAB_DB_USER') ?: 'root';
$password_server = getenv('MONICOMLAB_DB_PASS');
$password_server = ($password_server === false) ? '' : $password_server;
$db = getenv('MONICOMLAB_DB_NAME') ?: 'monicomlab';

$port = getenv('MONICOMLAB_DB_PORT');
if ($port !== false && $port !== '') {
  $conn = mysqli_connect($servername, $username_server, $password_server, $db, (int)$port);
} else {
  $conn = mysqli_connect($servername, $username_server, $password_server, $db);
}

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

$pdoDsn = "mysql:host={$servername};dbname={$db};charset=utf8mb4";
$pdoOptions = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];
?>