<?php
include('../../connectMySql.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE ticket SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "Ticket #00$id has been updated to $status.";
    } else {
        echo "Update failed.";
    }

    $stmt->close();
    $conn->close();
}
?>
