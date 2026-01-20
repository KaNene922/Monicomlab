<?php

// Database connection settings
// Defaults target common XAMPP setups (MariaDB root with empty password).
// Override via environment variables if needed.
$servername = getenv('MONICOMLAB_DB_HOST') ?: 'localhost';
$db = getenv('MONICOMLAB_DB_NAME') ?: 'monicomlab';
$username_server = getenv('MONICOMLAB_DB_USER') ?: 'root';
$password_server = getenv('MONICOMLAB_DB_PASS');
if ($password_server === false) {
  $password_server = '';
}

// MySQLi connection (used by most pages)
$conn = mysqli_connect($servername, $username_server, $password_server, $db);
if (!$conn) {
  die('Connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// PDO connection (used by login/register flows)
$pdoDsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $servername, $db);
try {
  $pdo = new PDO($pdoDsn, $username_server, $password_server, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  // Keep legacy behavior (fail fast) while being explicit about PDO.
  die('Connection failed: ' . $e->getMessage());
}
?>