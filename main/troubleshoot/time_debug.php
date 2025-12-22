<?php
// Set timezone to Philippine time
date_default_timezone_set('Asia/Manila');

echo "<h3>Time Debug Information</h3>";
echo "<p><strong>Current PHP Timezone:</strong> " . date_default_timezone_get() . "</p>";
echo "<p><strong>Current PHP Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Current PHP Time (12-hour format):</strong> " . date('M d, Y h:i A') . "</p>";

// Test DateTime object
$now = new DateTime('now', new DateTimeZone('Asia/Manila'));
echo "<p><strong>DateTime Object Time:</strong> " . $now->format('M d, Y h:i A') . "</p>";

// Connect to database to check MySQL timezone
include '../connectMySql.php';

$result = mysqli_query($conn, "SELECT NOW() as current_time, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p><strong>MySQL Current Time:</strong> " . $row['current_time'] . "</p>";
    echo "<p><strong>MySQL Global Timezone:</strong> " . $row['global_tz'] . "</p>";
    echo "<p><strong>MySQL Session Timezone:</strong> " . $row['session_tz'] . "</p>";
}

echo "<p><strong>Server Time:</strong> " . exec('date') . "</p>";
?>