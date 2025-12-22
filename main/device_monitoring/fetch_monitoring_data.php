<?php
include '../../connectMySql.php';

header('Content-Type: application/json');

// First, get all devices with their monitoring data
$query = "SELECT d.id as device_id, d.*, d.ip_address as ip_address_r, md.*, r.room_name, r.room_id
          FROM device d
          LEFT JOIN (
              SELECT md1.*
              FROM monitoring_data md1
              INNER JOIN (
                  SELECT ip_address, MAX(id) AS max_id
                  FROM monitoring_data
                  GROUP BY ip_address
              ) md2 ON md1.ip_address = md2.ip_address AND md1.id = md2.max_id
          ) md ON d.ip_address = md.ip_address
          LEFT JOIN rooms r ON d.room_id = r.room_id
          ORDER BY r.room_name, d.device, d.name";

$result = $conn->query($query);
$devices = [];

while ($row = $result->fetch_assoc()) {
    $device = [
        'id' => $row['device_id'], // Use the device_id from device table
        'device_id' => $row['device_id'], // Add explicit device_id field  
        'name' => $row['name'],
        'ip_address' => $row['ip_address_r'],
        'device_type' => strtoupper($row['device']),
        'status' => strtolower($row['status'] ?? 'offline'), // Normalize status to lowercase
        'cpu' => $row['cpu'] ?? 0,
        'ram' => $row['ram'] ?? 0,
        'disk' => $row['disk'] ?? 0,
        'created_at' => $row['created_at'],
        'room_id' => $row['room_id'],
        'room_name' => $row['room_name']
    ];
    $devices[] = $device;
}

// Now get all rooms that don't have devices yet (empty rooms)
$emptyRoomsQuery = "SELECT r.room_id, r.room_name 
                    FROM rooms r 
                    LEFT JOIN device d ON r.room_id = d.room_id 
                    WHERE d.room_id IS NULL
                    ORDER BY r.room_name";

$emptyRoomsResult = $conn->query($emptyRoomsQuery);

// Add empty rooms as placeholder entries
while ($row = $emptyRoomsResult->fetch_assoc()) {
    $emptyRoom = [
        'id' => 'empty_' . $row['room_id'], // Unique ID for empty rooms
        'device_id' => null,
        'name' => 'No devices',
        'ip_address' => null,
        'device_type' => 'EMPTY',
        'status' => 'offline',
        'cpu' => 0,
        'ram' => 0,
        'disk' => 0,
        'created_at' => null,
        'room_id' => $row['room_id'],
        'room_name' => $row['room_name']
    ];
    $devices[] = $emptyRoom;
}

echo json_encode($devices);
?>