<?php
include 'connectMySql.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

echo "<h2>Database Date Analysis</h2>";

// Check current ticket dates
$query = "SELECT id, device_name, issue_type, date, created_at FROM ticket ORDER BY date DESC LIMIT 10";
$result = $conn->query($query);

echo "<h3>Recent Tickets:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Device</th><th>Issue</th><th>Date (stored)</th><th>Created At</th><th>Analysis</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stored_date = $row['date'];
        $created_at = $row['created_at'];
        $current_time = date('Y-m-d H:i:s');
        
        // Check if date is in future
        $is_future = strtotime($stored_date) > strtotime($current_time);
        $analysis = $is_future ? "<span style='color: red;'>FUTURE DATE!</span>" : "<span style='color: green;'>OK</span>";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['device_name'] . "</td>";
        echo "<td>" . $row['issue_type'] . "</td>";
        echo "<td>" . $stored_date . "</td>";
        echo "<td>" . $created_at . "</td>";
        echo "<td>" . $analysis . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No tickets found</td></tr>";
}
echo "</table>";

echo "<h3>Current System Time:</h3>";
echo "<p><strong>PHP Current Time:</strong> " . date('Y-m-d H:i:s') . " (Asia/Manila)</p>";

// Check MySQL timezone
$tz_query = "SELECT NOW() as mysql_time, @@session.time_zone as session_tz, @@global.time_zone as global_tz";
$tz_result = $conn->query($tz_query);
if ($tz_result && $tz_row = $tz_result->fetch_assoc()) {
    echo "<p><strong>MySQL Current Time:</strong> " . $tz_row['mysql_time'] . "</p>";
    echo "<p><strong>MySQL Session Timezone:</strong> " . $tz_row['session_tz'] . "</p>";
    echo "<p><strong>MySQL Global Timezone:</strong> " . $tz_row['global_tz'] . "</p>";
}

// Count future dated tickets
$future_query = "SELECT COUNT(*) as future_count FROM ticket WHERE date > NOW()";
$future_result = $conn->query($future_query);
if ($future_result && $future_row = $future_result->fetch_assoc()) {
    echo "<h3>Future Dated Tickets:</h3>";
    echo "<p><strong>Number of tickets with future dates:</strong> " . $future_row['future_count'] . "</p>";
    
    if ($future_row['future_count'] > 0) {
        echo "<h4>Options to fix future dates:</h4>";
        echo "<p>1. <a href='?fix_future_dates=1' onclick='return confirm(\"This will update all future-dated tickets to current time. Continue?\")'>Fix all future dates to current time</a></p>";
        echo "<p>2. <a href='?delete_future_dates=1' onclick='return confirm(\"This will DELETE all future-dated tickets. Continue?\")'>Delete all future-dated tickets</a></p>";
    }
}

// Handle fix requests
if (isset($_GET['fix_future_dates']) && $_GET['fix_future_dates'] == '1') {
    $current_time = date('Y-m-d H:i:s');
    $fix_query = "UPDATE ticket SET date = ? WHERE date > NOW()";
    $stmt = $conn->prepare($fix_query);
    if ($stmt) {
        $stmt->bind_param("s", $current_time);
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0;'>";
            echo "Successfully updated " . $stmt->affected_rows . " future-dated tickets to current time: " . $current_time;
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0;'>";
            echo "Error updating tickets: " . $stmt->error;
            echo "</div>";
        }
    }
}

if (isset($_GET['delete_future_dates']) && $_GET['delete_future_dates'] == '1') {
    $delete_query = "DELETE FROM ticket WHERE date > NOW()";
    if ($conn->query($delete_query)) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0;'>";
        echo "Successfully deleted " . $conn->affected_rows . " future-dated tickets";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0;'>";
        echo "Error deleting tickets: " . $conn->error;
        echo "</div>";
    }
}
?>