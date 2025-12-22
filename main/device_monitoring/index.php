<?php

include '../../connectMySql.php';
include '../../loginverification.php';
if(logged_in()){
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MONICOMLAB - Live Monitoring</title>
    <link rel="icon" type="image/x-icon" href="../../img/logo2.png"/>

    <!-- Custom fonts for this template-->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template-->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .progress {
            height: 20px;
            margin-bottom: 10px;
        }
        .progress-bar {
            font-size: 12px;
            line-height: 20px;
        }
        .last-updated {
            font-size: 11px;
            color: #6c757d;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .online {
            background-color: #28a745;
        }
        .offline {
            background-color: #dc3545;
        }
        .refresh-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .auto-refresh-label {
            margin-bottom: 0;
            font-size: 14px;
        }
        #close-monitoring-btn {
            margin-left: 10px;
        }
        .room-card {
            margin-bottom: 25px;
            border-left: 4px solid #4e73df;
        }
        .room-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 15px 20px;
        }
        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
            padding: 0 20px 20px 20px;
        }
        .device-card {
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .room-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        .room-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .view-room-btn {
            font-size: 13px;
        }
        .device-icons {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        .device-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            background-color: #f8f9fc;
            min-width: 80px;
        }
        .device-icon i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .device-icon span {
            font-size: 12px;
        }
        .room-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e3e6f0;
        }
        .empty-room-message {
            color: #6c757d;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }
        .device-icon.empty {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            color: #6c757d;
        }
    </style>

    <style>
        .table-minimized {
            max-height: 30px;
            overflow-y: auto;
        }
        .table-minimized .card-body {
            padding: 0.5rem;
            display: none; /* Hide by default when minimized */
        }
        .minimize-btn {
            cursor: pointer;
            transition: all 0.3s;
        }
        .minimize-btn:hover {
            opacity: 0.8;
        }
    </style>

    <style>
        /* Water wave effect for progress bars */
        .progress {
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            position: relative;
            overflow: hidden;
            transition: width 0.5s ease-in-out;
        }

        .water-wave {
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-100%);
            animation: wave 1.5s linear infinite;
            z-index: 1;
        }

        /* Add this new class to pause animation */
        .water-wave.paused {
            animation-play-state: paused;
            opacity: 0.5;
        }

        @keyframes wave {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        /* Add slight pulsing effect to make it more alive */
        .progress-bar.pulse {
            animation: pulse 2s infinite;
        }

        .progress-bar.pulse.paused {
            animation-play-state: paused;
            opacity: 0.7;
        }

        @keyframes pulse {
           0% {
                opacity: 1;
            }
            50% {
                opacity: 0.9;
            }
            100% {
                opacity: 1;
            }
        }
        
        /* Modal specific styles */
        .modal-device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            max-height: 60vh;
            overflow-y: auto;
            padding: 10px;
        }
        .modal-device-card {
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 15px;
            background-color: #fff;
        }
        .device-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .device-actions .btn-sm {
            font-size: 11px;
            padding: 2px 6px;
        }
    </style>

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
                        <h1 class="h3 mb-0 text-gray-800">Device Monitoring <span class="badge badge-success" id="live-badge">LIVE</span></h1>
                        <div>
                            <a href="register.php" class=" btn btn-sm btn-primary shadow-sm"><i class="fas fa-plus"></i> Add Device</a>
                            <a href="#" class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#addRoomModal">
                                <i class="fas fa-door-open"></i> Create Room
                            </a>
                            <a href="../../get_monitoring_data.php" target="_blank" class=" btn btn-sm btn-primary shadow-sm" id="start-monitoring-link"><i class="fas fa-laptop"></i> START MONITORING DATA</a>
                            <button id="close-monitoring-btn" class="btn btn-sm btn-danger shadow-sm"><i class="fas fa-times"></i> STOP</button>
                        </div>
                    </div>
                    
                    <div class="refresh-controls">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked>
                            <label class="form-check-label auto-refresh-label" for="autoRefreshToggle">
                                Auto Refresh
                            </label>
                        </div>
                        <span class="last-updated">Last updated: <span id="last-update-time">--:--:--</span></span>
                        <button class="btn btn-sm btn-outline-secondary" id="manualRefreshBtn">
                            <i class="fas fa-sync-alt"></i> Refresh Now
                        </button>
                    </div>

                    <div id="rooms-container">
                        <!-- Rooms will be loaded here via JavaScript -->
                    </div>

                    <!-- Room Management Section -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Room Management</h6>
                                    <button class="btn btn-sm btn-info minimize-btn" id="minimizeRoomTable">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="card-body" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="roomsTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Room Name</th>
                                                    <th>Date Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = "SELECT * FROM rooms ORDER BY date_created DESC";
                                                $result = $conn->query($query);
                                                
                                                if ($result->num_rows > 0) {
                                                    while($row = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . $row['room_id'] . "</td>";
                                                        echo "<td>" . $row['room_name'] . "</td>";
                                                        echo "<td>" . $row['date_created'] . "</td>";
                                                        echo "<td>
                                                                <button class='btn btn-sm btn-info edit-room' data-id='" . $row['room_id'] . "' data-name='" . $row['room_name'] . "'>Edit</button>
                                                                <button class='btn btn-sm btn-danger delete-room' data-id='" . $row['room_id'] . "'>Delete</button>
                                                            </td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>No rooms found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Device Management Section -->
                    <div class="row mt-1">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Device Management</h6>
                                    <button class="btn btn-sm btn-info minimize-btn" id="minimizeDeviceTable">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="card-body" style="display: none;">
                                    <div class="mb-3">
                                        <button class="btn btn-danger" id="deleteSelectedDevices" disabled>
                                            <i class="fas fa-trash"></i> Delete Selected Devices
                                        </button>
                                        <button class="btn btn-primary ml-2" id="selectAllDevices">
                                            <i class="fas fa-check-square"></i> Select All
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="devicesTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th width="5%">
                                                        <input type="checkbox" id="selectAllCheckbox">
                                                    </th>
                                                    <th>Device Name</th>
                                                    <th>IP Address</th>
                                                    <th>Room</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="devicesTableBody">
                                                <!-- Devices will be loaded here via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
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

    <!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" role="dialog" aria-labelledby="addRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoomModalLabel">Add New Room</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRoomForm" action="add_room.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="roomName">Room Name</label>
                        <input type="text" class="form-control" id="roomName" name="roomName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" role="dialog" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editRoomForm" action="edit_room.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="editRoomId" name="roomId">
                    <div class="form-group">
                        <label for="editRoomName">Room Name</label>
                        <input type="text" class="form-control" id="editRoomName" name="roomName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRoomModal" tabindex="-1" role="dialog" aria-labelledby="deleteRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteRoomModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this room? This action cannot be undone.</p>
                <input type="hidden" id="deleteRoomId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Room</button>
            </div>
        </div>
    </div>
</div>

<!-- View Devices Modal -->
<div class="modal fade" id="viewDevicesModal" tabindex="-1" role="dialog" aria-labelledby="viewDevicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDevicesModalLabel">Devices in <span id="modal-room-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-device-grid" id="modal-devices-container">
                    <!-- Devices will be loaded here via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary" id="refreshModalDevices">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button> -->
            </div>
        </div>
    </div>
</div>

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

    <script>
        // Global variables
        let refreshInterval;
        const refreshRate = 1000; // 5 seconds
        let monitoringWindow = null;
        let allDevicesData = [];
        let currentModalRoomId = null;
        let currentModalRoomName = null;
        
        function fetchDeviceData() {
            $.ajax({
                url: 'fetch_monitoring_data.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Clean and normalize status data
                    data.forEach(device => {
                        // Ensure status is lowercase for consistency
                        if (device.status) {
                            device.status = device.status.toLowerCase();
                        } else {
                            device.status = 'offline'; // Default to offline if no status
                        }
                        
                        // Debug log for troubleshooting
                        console.log(`Device ${device.name}: Status = ${device.status}, IP = ${device.ip_address}`);
                    });
                    
                    allDevicesData = data;
                    updateRoomsDisplay(data);
                    updateLastUpdateTime();
                    
                    // Check for resource issues and create tickets if needed
                    checkResourceIssues(data);
                    
                    // Always update modal content if it's open, regardless of room ID
                    if ($('#viewDevicesModal').hasClass('show')) {
                        showDevicesModal(currentModalRoomId, currentModalRoomName);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching device data:', error);
                    $('#live-badge').removeClass('badge-success').addClass('badge-danger').text('OFFLINE');
                }
            });
        }

        // Add this function to check resource issues
        function checkResourceIssues(devices) {
            // Filter only PC and SERVER devices with resource data
            const devicesWithResources = devices.filter(device => 
                (device.device_type === 'PC' || device.device_type === 'SERVER') && 
                (device.cpu !== null || device.ram !== null || device.disk !== null)
            );
            
            if (devicesWithResources.length > 0) {
                // Send resource data to server for ticket creation
                $.ajax({
                    url: 'check_resource_issues.php',
                    type: 'POST',
                    data: {
                        devices: JSON.stringify(devicesWithResources)
                    },
                    success: function(response) {
                        console.log('Resource issues checked:', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error checking resource issues:', error);
                    }
                });
            }
        }
        
        // Function to update the rooms display (without showing individual devices)
function updateRoomsDisplay(devices) {
    let html = '';
    
    // Group devices by room
    const rooms = {};
    devices.forEach(device => {
        const roomId = device.room_id || 'unassigned';
        const roomName = device.room_name || 'Unassigned Devices';
        
        if (!rooms[roomId]) {
            rooms[roomId] = {
                room_id: roomId,
                room_name: roomName,
                devices: [],
                onlineCount: 0,
                offlineCount: 0,
                deviceTypes: {}
            };
        }
        
        // Don't count empty room placeholders as real devices
        if (device.device_type !== 'EMPTY') {
            rooms[roomId].devices.push(device);
            
            if (device.status === 'online') {
                rooms[roomId].onlineCount++;
            } else {
                rooms[roomId].offlineCount++;
            }
            
            // Count device types
            if (!rooms[roomId].deviceTypes[device.device_type]) {
                rooms[roomId].deviceTypes[device.device_type] = 0;
            }
            rooms[roomId].deviceTypes[device.device_type]++;
        }
    });
    
    // Start a row container
    html += '<div class="row">';
    
    // Generate HTML for each room
    for (const roomId in rooms) {
        const room = rooms[roomId];
        
        // Create device type icons
        let deviceIconsHtml = '';
        if (Object.keys(room.deviceTypes).length === 0) {
            deviceIconsHtml = '<div class="device-icon"><i class="fas fa-plus text-muted"></i><span class="text-muted">No devices yet</span></div>';
        } else {
            for (const deviceType in room.deviceTypes) {
                const icon =
                (deviceType === 'PC') ? 'desktop' :
                (deviceType === 'SERVER') ? 'server' :
                (deviceType === 'ROUTER') ? 'satellite-dish' :
                (deviceType === 'SWITCH') ? 'project-diagram' :
                'network-wired';

                deviceIconsHtml += `
                <div class="device-icon">
                    <i class="fas fa-${icon} text-gray-500"></i>
                    <span>${deviceType} (${room.deviceTypes[deviceType]})</span>
                </div>`;
            }
        }
        
        // Use col-lg-4 to fit 3 cards in one row (12/4=3)
        html += `
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100 room-card">
                <div class="card-header py-3 room-header">
                    <div class="room-title">
                        <h6 class="m-0 font-weight-bold text-primary">${room.room_name}</h6>
                    </div>
                    <div class="room-stats">
                        <div class="stat-item">
                            <span class="status-indicator online"></span>
                            <span>Online: ${room.onlineCount}</span>
                        </div>
                        <div class="stat-item">
                            <span class="status-indicator offline"></span>
                            <span>Offline: ${room.offlineCount}</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-laptop text-primary"></i>
                            <span>Total: ${room.onlineCount + room.offlineCount}</span>
                        </div>
                    </div>

                </div>
                <div class="card-body d-flex flex-column">
                    <div class="device-icons">
                        ${deviceIconsHtml}
                    </div>
                    <div class="room-footer mt-auto">
                        <div class="last-updated">
                            Last checked: ${new Date().toLocaleString()}
                        </div>
                        <button class="btn btn-sm btn-primary view-devices-btn" data-room-id="${room.room_id}" data-room-name="${room.room_name}">
                            <i class="fas fa-eye"></i> View Devices
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    // Close the row container
    html += '</div>';
    
    $('#rooms-container').html(html);
    
    // Add click event to view devices buttons
    $('.view-devices-btn').click(function() {
        const roomId = $(this).data('room-id');
        const roomName = $(this).data('room-name');
        showDevicesModal(roomId, roomName);
    });
}
        
        // Function to show devices in modal
        function showDevicesModal(roomId, roomName) {
            currentModalRoomId = roomId;
            currentModalRoomName = roomName;
            
            $('#modal-room-name').text(roomName || 'All Devices');
            $('#modal-devices-container').empty();
            
            // If no roomId provided, show all devices
            const roomDevices = roomId ? allDevicesData.filter(device => device.room_id == roomId) : allDevicesData;
            
            if (roomDevices.length === 0) {
                $('#modal-devices-container').html('<p class="text-center">No devices found.</p>');
            } else {
                roomDevices.forEach(device => {
                const icon =
                (device.device_type === 'PC') ? 'desktop' :
                (device.device_type === 'SERVER') ? 'server' :
                (device.device_type === 'ROUTER') ? 'satellite-dish' :
                (device.device_type === 'SWITCH') ? 'project-diagram' :
                'network-wired';


                    const statusColor = device.status === 'online' ? 'text-success' : 'text-danger';
                    const statusText = device.status.charAt(0).toUpperCase() + device.status.slice(1);
                    const isOnline = device.status === 'online';
                    const animationClass = isOnline ? '' : 'paused';
                    
                    let deviceHtml = `
                    <div class="modal-device-card">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-${icon} fa-2x text-gray-300 mr-3"></i>
                            <div>
                                <div class="font-weight-bold">${device.device_type}-${device.name}</div>
                                <div class="small text-muted">${device.ip_address}</div>
                            </div>
                        </div>`;
            
                    if (device.device_type === 'PC' || device.device_type === 'SERVER') {
                        deviceHtml += `
                        <div class="mb-2">
                            <strong>CPU Usage</strong>
                            <div class="progress">
                                <div class="progress-bar bg-primary ${isOnline ? 'pulse' : 'pulse paused'}" style="width: ${device.cpu || 0}%">
                                    <div class="water-wave ${animationClass}"></div>
                                    ${device.cpu || 0}%
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <strong>RAM Usage</strong>
                            <div class="progress">
                                <div class="progress-bar bg-info ${isOnline ? 'pulse' : 'pulse paused'}" style="width: ${device.ram || 0}%">
                                    <div class="water-wave ${animationClass}"></div>
                                    ${device.ram || 0}%
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Disk</strong>
                            <div class="progress">
                                <div class="progress-bar bg-success ${isOnline ? 'pulse' : 'pulse paused'}" style="width: ${device.disk || 0}%">
                                    <div class="water-wave ${animationClass}"></div>
                                    ${device.disk || 0}%
                                </div>
                            </div>
                        </div>`;
                    }
                    
                    deviceHtml += `
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-status ${statusColor}">
                                <i class="fas fa-circle"></i> ${statusText}
                            </div>
                            <div class="device-actions">
                                ${(device.device_type === 'PC' || device.device_type === 'SERVER') ? 
                                    `<a href="view.php?ip_address=${device.ip_address}&date=${device.created_at}&device=${device.device_type}&name=${device.name}" class="btn btn-sm btn-primary">View apps</a>` : ''}
                            </div>
                        </div>
                        <div class="small text-muted text-right mt-2">
                            Last checked: <span class="realtime-clock">${new Date().toLocaleString()}</span>
                        </div>
                    </div>`;
                    
                    $('#modal-devices-container').append(deviceHtml);
                });
            }
            
            $('#viewDevicesModal').modal('show');
        }
        
        // Function to update the last update time
        function updateLastUpdateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            $('#last-update-time').text(timeString);
        }

        // Function to update real-time clock
        function updateRealTimeClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const fullTimeString = now.toLocaleString();
            
            // Update main page time
            $('#last-update-time').text(timeString);
            
            // Update modal timestamps if modal is open
            $('.realtime-clock').text(fullTimeString);
        }
        
        // Function to start auto-refresh
        function startAutoRefresh() {
            clearInterval(refreshInterval);
            refreshInterval = setInterval(fetchDeviceData, refreshRate);
            $('#live-badge').removeClass('badge-danger').addClass('badge-success').text('LIVE');
        }
        
        // Function to stop auto-refresh
        function stopAutoRefresh() {
            clearInterval(refreshInterval);
            $('#live-badge').removeClass('badge-success').addClass('badge-secondary').text('PAUSED');
        }
        
        // Function to close the monitoring tab
        function closeMonitoringTab() {
            if (monitoringWindow && !monitoringWindow.closed) {
                try {
                    monitoringWindow.close();
                    $('#close-monitoring-btn').html('<i class="fas fa-check"></i> TAB CLOSED');
                    $('#close-monitoring-btn').prop('disabled', true);
                } catch (error) {
                    alert('Error closing tab: ' + error.message + '. Please close the tab manually.');
                }
            } else {
                alert('No monitoring tab found or it was already closed. If you see the tab, please close it manually.');
            }
        }
        
        // Document ready
        $(document).ready(function() {
            // Initial data load
            fetchDeviceData();

            // Start real-time clock update
            updateRealTimeClock(); // Initial call
            setInterval(updateRealTimeClock, 1000); // Update every second
            
            // Check for URL parameters indicating fresh data needed
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') && urlParams.get('success').includes('Room added')) {
                // Force a fresh data load when a room was just added
                setTimeout(function() {
                    fetchDeviceData();
                }, 500); // Small delay to ensure database operation is complete
                
                // Clean up the URL to remove the parameters
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
            
            // Set up auto-refresh
            startAutoRefresh();
            
            // Toggle auto-refresh
            $('#autoRefreshToggle').change(function() {
                if ($(this).is(':checked')) {
                    startAutoRefresh();
                } else {
                    stopAutoRefresh();
                }
            });
            
            // Manual refresh button
            $('#manualRefreshBtn').click(function() {
                fetchDeviceData();
            });
            
            // Refresh modal devices button
            $('#refreshModalDevices').click(function() {
                if (currentModalRoomId && currentModalRoomName) {
                    showDevicesModal(currentModalRoomId, currentModalRoomName);
                }
            });
            
            // Modify the monitoring link to open in a new window and store reference
            $('#start-monitoring-link').click(function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                monitoringWindow = window.open(url, '_blank');
                $('#close-monitoring-btn').prop('disabled', false);
                $('#close-monitoring-btn').html('<i class="fas fa-times"></i> STOP');
                return false;
            });
            
            // Close monitoring tab button
            $('#close-monitoring-btn').click(function() {
                closeMonitoringTab();
            });
            
            // Clear modal data when modal is closed
            $('#viewDevicesModal').on('hidden.bs.modal', function () {
                currentModalRoomId = null;
                currentModalRoomName = null;
            });
        });
    </script>

    <script>
        // AJAX form submission for adding room
        $('#addRoomForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'add_room.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#addRoomModal').modal('hide');
                    location.reload(); // Reload the page to show the new room
                },
                error: function() {
                    location.reload();
                }
            });
        });

        // Handle edit room action
        $(document).on('click', '.edit-room', function() {
            var roomId = $(this).data('id');
            var roomName = $(this).data('name');
            
            $('#editRoomId').val(roomId);
            $('#editRoomName').val(roomName);
            $('#editRoomModal').modal('show');
        });

        // AJAX form submission for editing room
        $('#editRoomForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'edit_room.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response === 'success') {
                        $('#editRoomModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function() {
                    alert('Error updating room. Please try again.');
                }
            });
        });

        // Handle delete room action
        $(document).on('click', '.delete-room', function() {
            var roomId = $(this).data('id');
            $('#deleteRoomId').val(roomId);
            $('#deleteRoomModal').modal('show');
        });

        // Confirm delete action
        $('#confirmDelete').on('click', function() {
            var roomId = $('#deleteRoomId').val();
            
            $.ajax({
                url: 'delete_room.php',
                type: 'POST',
                data: { roomId: roomId },
                success: function(response) {
                    if (response === 'success') {
                        $('#deleteRoomModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response);
                    }
                },
                error: function() {
                    alert('Error deleting room. Please try again.');
                }
            });
        });
    </script>
    
    <script>  
        // Room table minimize functionality
        $(document).ready(function() {
            // Set initial state to minimized
            const tableContainer = $('#minimizeRoomTable').closest('.card').find('.card-body');
            const buttonIcon = $('#minimizeRoomTable').find('i');
            
            // Table is minimized by default (based on the display: none we added)
            $('#minimizeRoomTable').removeClass('btn-secondary').addClass('btn-info');
            buttonIcon.removeClass('fa-minus').addClass('fa-plus');
            
            $('#minimizeRoomTable').click(function() {
                if (tableContainer.is(':visible')) {
                    // Minimize table
                    tableContainer.slideUp(300);
                    buttonIcon.removeClass('fa-minus').addClass('fa-plus');
                    $(this).removeClass('btn-secondary').addClass('btn-info');
                } else {
                    // Expand table
                    tableContainer.slideDown(300);
                    buttonIcon.removeClass('fa-plus').addClass('fa-minus');
                    $(this).removeClass('btn-info').addClass('btn-secondary');
                }
            });
        });
    </script>

    <script>
        // Device Management functionality
        $(document).ready(function() {
            // Device table minimize functionality
            const deviceTableContainer = $('#minimizeDeviceTable').closest('.card').find('.card-body');
            const deviceButtonIcon = $('#minimizeDeviceTable').find('i');
            
            $('#minimizeDeviceTable').click(function() {
                if (deviceTableContainer.is(':visible')) {
                    // Minimize table
                    deviceTableContainer.slideUp(300);
                    deviceButtonIcon.removeClass('fa-minus').addClass('fa-plus');
                    $(this).removeClass('btn-secondary').addClass('btn-info');
                } else {
                    // Expand table and load devices
                    deviceTableContainer.slideDown(300);
                    deviceButtonIcon.removeClass('fa-plus').addClass('fa-minus');
                    $(this).removeClass('btn-info').addClass('btn-secondary');
                    loadDevicesForManagement();
                }
            });

            // Load devices into management table
            function loadDevicesForManagement() {
                $.ajax({
                    url: 'fetch_monitoring_data.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(devices) {
                        populateDevicesTable(devices);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching devices for management:', error);
                    }
                });
            }

            // Populate devices table
            function populateDevicesTable(devices) {
                let html = '';
                
                devices.forEach(device => {
                    const statusClass = device.status === 'online' ? 'text-success' : 'text-danger';
                    const statusIcon = device.status === 'online' ? 'fa-circle' : 'fa-circle';
                    const roomName = device.room_name || 'Unassigned';
                    
                    html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="device-checkbox" value="${device.device_id}">
                        </td>
                        <td>${device.name}</td>
                        <td>${device.ip_address}</td>
                        <td>${roomName}</td>
                        <td>
                            <span class="${statusClass}">
                                <i class="fas ${statusIcon}"></i> ${device.status.toUpperCase()}
                            </span>
                        </td>
                    </tr>`;
                });
                
                $('#devicesTableBody').html(html);
                updateDeleteButtonState();
            }

            // Select/Deselect all devices
            $('#selectAllCheckbox').change(function() {
                $('.device-checkbox').prop('checked', $(this).is(':checked'));
                updateDeleteButtonState();
            });

            $('#selectAllDevices').click(function() {
                $('.device-checkbox').prop('checked', true);
                $('#selectAllCheckbox').prop('checked', true);
                updateDeleteButtonState();
            });

            // Update delete button state
            $(document).on('change', '.device-checkbox', function() {
                updateDeleteButtonState();
                
                // Update select all checkbox
                const totalCheckboxes = $('.device-checkbox').length;
                const checkedCheckboxes = $('.device-checkbox:checked').length;
                $('#selectAllCheckbox').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            function updateDeleteButtonState() {
                const selectedDevices = $('.device-checkbox:checked').length;
                $('#deleteSelectedDevices').prop('disabled', selectedDevices === 0);
                
                if (selectedDevices > 0) {
                    $('#deleteSelectedDevices').html(`<i class="fas fa-trash"></i> Delete Selected Devices (${selectedDevices})`);
                } else {
                    $('#deleteSelectedDevices').html('<i class="fas fa-trash"></i> Delete Selected Devices');
                }
            }

            // Handle bulk delete
            $('#deleteSelectedDevices').click(function() {
                const selectedDevices = $('.device-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedDevices.length === 0) {
                    alert('Please select devices to delete.');
                    return;
                }

                if (confirm(`Are you sure you want to delete ${selectedDevices.length} selected device(s)? This action cannot be undone.`)) {
                    bulkDeleteDevices(selectedDevices);
                }
            });

            // Bulk delete devices function
            function bulkDeleteDevices(deviceIds) {
                let deletePromises = [];
                
                deviceIds.forEach(deviceId => {
                    deletePromises.push(
                        $.ajax({
                            url: 'delete_device.php',
                            type: 'POST',
                            data: { device_id: deviceId }
                        })
                    );
                });

                Promise.all(deletePromises)
                    .then(responses => {
                        const successCount = responses.filter(response => response === 'success').length;
                        const errorCount = responses.length - successCount;
                        
                        if (errorCount === 0) {
                            alert(`Successfully deleted ${successCount} device(s).`);
                        } else {
                            alert(`Deleted ${successCount} device(s). ${errorCount} device(s) failed to delete.`);
                        }
                        
                        // Refresh the table and main data
                        loadDevicesForManagement();
                        fetchDeviceData();
                        
                        // Reset checkboxes
                        $('#selectAllCheckbox').prop('checked', false);
                        updateDeleteButtonState();
                    })
                    .catch(error => {
                        console.error('Error during bulk delete:', error);
                        alert('Error occurred during device deletion. Please try again.');
                    });
            }
        });
    </script>

</body>

</html>
<?php
} else {
    header('location:../../index.php');
}
?>