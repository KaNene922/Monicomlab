<?php
include '../../connectMySql.php';
include '../../loginverification.php';

if(logged_in()){

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete a specific device
    if (isset($_POST['delete_device'])) {
        $ip = $conn->real_escape_string($_POST['delete_device']);
        $conn->query("DELETE FROM connected_devices WHERE ip_address = '$ip'");
    }

    // Delete a specific issue
    if (isset($_POST['delete_issue'])) {
        $id = intval($_POST['delete_issue']);
        $conn->query("DELETE FROM detect_issue WHERE id = $id");
    }

    // Clear all
    if (isset($_POST['clear_all'])) {
        $conn->query("DELETE FROM connected_devices WHERE status IN ('Disconnected', 'Removed')");
        $conn->query("DELETE FROM detect_issue");
    }

    // Delete selected tickets
    if (isset($_POST['delete_selected']) && isset($_POST['selected_tickets'])) {
        $selectedTickets = $_POST['selected_tickets'];
        foreach ($selectedTickets as $ticketId) {
            $id = intval($ticketId);
            $conn->query("DELETE FROM ticket WHERE id = $id");
        }
    }

    // Clear all tickets
    if (isset($_POST['clear_all_tickets'])) {
        $conn->query("DELETE FROM ticket");
    }
}

// Handle Downloads
if (isset($_GET['download'])) {
    $downloadType = $_GET['download'];
    
    // Build query for download
    $condition = "";
    if(isset($_GET['type'])){
        $condition = "WHERE t.status = '".$conn->real_escape_string($_GET['type'])."'";
    }
    
    // Add date filtering for reports
    $dateCondition = "";
    $reportTitle = "All_Logs";
    if (isset($_GET['report'])) {
        switch ($_GET['report']) {
            case 'daily':
                $dateCondition = "AND DATE(t.date) = CURDATE()";
                $reportTitle = "Daily_Report_" . date('Y-m-d');
                break;
            case 'weekly':
                $dateCondition = "AND WEEK(t.date) = WEEK(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                $reportTitle = "Weekly_Report_Week" . date('W') . "_" . date('Y');
                break;
            case 'monthly':
                $dateCondition = "AND MONTH(t.date) = MONTH(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                $reportTitle = "Monthly_Report_" . date('F_Y');
                break;
        }
    }
    
    // Combine all conditions
    $excludeCondition = "AND t.issue_type != 'Multiple Devices Offline' AND t.issue_type != 'MULTIPLE DEVICES OFFLINE'";
    if ($condition) {
        $finalCondition = $condition . " " . $excludeCondition . " " . $dateCondition;
    } else {
        $finalCondition = "WHERE 1=1 " . $excludeCondition . " " . $dateCondition;
    }
    
    $downloadQuery = "SELECT DISTINCT t.*, d.ip_address, r.room_name
                     FROM ticket t
                     LEFT JOIN device d ON t.device_name = d.name
                     LEFT JOIN rooms r ON d.room_id = r.room_id
                     $finalCondition
                     GROUP BY t.device_name, t.issue_type, t.description, DATE(t.date)
                     ORDER BY t.date DESC";
    
    $downloadResult = $conn->query($downloadQuery);
    
    if ($downloadType == 'csv') {
        // Generate CSV
        $filename = $reportTitle . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, array('Device', 'Room', 'IP Address', 'Issue Type', 'Category', 'Severity', 'Description', 'Status', 'Reported Date'));
        
        while ($row = $downloadResult->fetch_assoc()) {
            // Category logic - prefer stored ticket category if present
            $category = !empty($row['category']) ? strtoupper($row['category']) : 'GENERAL';
            $issueType = strtolower($row['issue_type']);

            // Only re-infer category if none stored (GENERAL)
            if ($category === 'GENERAL') {
                if (strpos($issueType, 'mouse') !== false || strpos($issueType, 'keyboard') !== false ||
                    strpos($issueType, 'printer') !== false || strpos($issueType, 'usb') !== false ||
                    strpos($issueType, 'audio') !== false || strpos($issueType, 'speaker') !== false ||
                    strpos($issueType, 'microphone') !== false || strpos($issueType, 'webcam') !== false ||
                    strpos($issueType, 'camera') !== false || strpos($issueType, 'monitor') !== false ||
                    strpos($issueType, 'display') !== false) {
                    $category = 'HARDWARE';
                } elseif (strpos($issueType, 'cpu') !== false || strpos($issueType, 'ram') !== false || 
                    strpos($issueType, 'disk') !== false || strpos($issueType, 'memory') !== false ||
                    strpos($issueType, 'storage') !== false || strpos($issueType, 'high usage') !== false ||
                    strpos($issueType, 'application') !== false || strpos($issueType, 'software') !== false ||
                    strpos($issueType, 'service') !== false || strpos($issueType, 'process') !== false) {
                    $category = 'SOFTWARE';
                } elseif (strpos($issueType, 'offline') !== false || strpos($issueType, 'connection') !== false ||
                       strpos($issueType, 'network') !== false || strpos($issueType, 'stale data') !== false ||
                       strpos($issueType, 'ping') !== false) {
                    $category = 'NETWORK';
                }
            }
            
            // Severity logic
            $severity = 'MEDIUM';
            if (strpos($issueType, 'critical') !== false) {
                $severity = 'CRITICAL';
            } elseif (strpos($issueType, 'warning') !== false || strpos($issueType, 'high') !== false) {
                $severity = 'HIGH';
            }
            
            // Format date properly - handle NULL or invalid dates
            $dateFormatted = 'N/A';
            if (!empty($row['date']) && $row['date'] !== '0000-00-00 00:00:00') {
                $timestamp = strtotime($row['date']);
                if ($timestamp !== false) {
                    $dateFormatted = date('M d, Y g:i A', $timestamp);
                }
            }
            
            $csvRow = array(
                strtoupper($row['device_name']),
                strtoupper($row['room_name'] ?? 'N/A'),
                $row['ip_address'] ?? 'N/A',
                strtoupper($row['issue_type']),
                ($category === 'GENERAL') ? '' : $category,
                $severity,
                strtoupper($row['description'] ?? 'N/A'),
                strtoupper($row['status']),
                $dateFormatted
            );
            fputcsv($output, $csvRow);
        }
        
        fclose($output);
        exit;
    }
    elseif ($downloadType == 'excel') {
        // Generate Excel (HTML table format)
        $filename = $reportTitle . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>Device</th><th>Room</th><th>IP Address</th><th>Issue Type</th><th>Category</th><th>Severity</th><th>Description</th><th>Status</th><th>Reported Date</th>';
        echo '</tr>';
        
        while ($row = $downloadResult->fetch_assoc()) {
            // Category logic (prefer stored category if present)
            $category = !empty($row['category']) ? strtoupper($row['category']) : 'GENERAL';
            $issueType = strtolower($row['issue_type']);
            // Only infer category when none is stored (GENERAL)
            if ($category === 'GENERAL') {
                // Hardware peripheral device issues
                if (strpos($issueType, 'mouse') !== false || strpos($issueType, 'keyboard') !== false ||
                    strpos($issueType, 'printer') !== false || strpos($issueType, 'usb') !== false ||
                    strpos($issueType, 'audio') !== false || strpos($issueType, 'speaker') !== false ||
                    strpos($issueType, 'microphone') !== false || strpos($issueType, 'webcam') !== false ||
                    strpos($issueType, 'camera') !== false || strpos($issueType, 'monitor') !== false ||
                    strpos($issueType, 'display') !== false) {
                    $category = 'HARDWARE';
                }
                // Resource / OS / app related issues -> SOFTWARE
                elseif (strpos($issueType, 'cpu') !== false || strpos($issueType, 'ram') !== false ||
                        strpos($issueType, 'disk') !== false || strpos($issueType, 'memory') !== false ||
                        strpos($issueType, 'storage') !== false || strpos($issueType, 'high usage') !== false ||
                        strpos($issueType, 'application') !== false || strpos($issueType, 'software') !== false ||
                        strpos($issueType, 'service') !== false || strpos($issueType, 'process') !== false) {
                    $category = 'SOFTWARE';
                }
                // Network related issues
                elseif (strpos($issueType, 'offline') !== false || strpos($issueType, 'connection') !== false ||
                       strpos($issueType, 'network') !== false || strpos($issueType, 'stale data') !== false ||
                       strpos($issueType, 'ping') !== false) {
                    $category = 'NETWORK';
                }
            }
            
            // Severity logic
            $severity = 'MEDIUM';
            if (strpos($issueType, 'critical') !== false) {
                $severity = 'CRITICAL';
            } elseif (strpos($issueType, 'warning') !== false || strpos($issueType, 'high') !== false) {
                $severity = 'HIGH';
            }
            
            // Format date properly - handle NULL or invalid dates
            $dateFormatted = 'N/A';
            if (!empty($row['date']) && $row['date'] !== '0000-00-00 00:00:00') {
                $timestamp = strtotime($row['date']);
                if ($timestamp !== false) {
                    $dateFormatted = date('M d, Y g:i A', $timestamp);
                }
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars(strtoupper($row['device_name'])) . '</td>';
            echo '<td>' . htmlspecialchars(strtoupper($row['room_name'] ?? 'N/A')) . '</td>';
            echo '<td>' . htmlspecialchars($row['ip_address'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars(strtoupper($row['issue_type'])) . '</td>';
            echo '<td>' . htmlspecialchars(($category === 'GENERAL') ? '' : $category) . '</td>';
            echo '<td>' . htmlspecialchars($severity) . '</td>';
            echo '<td>' . htmlspecialchars(strtoupper($row['description'] ?? 'N/A')) . '</td>';
            echo '<td>' . htmlspecialchars(strtoupper($row['status'])) . '</td>';
            echo '<td>' . htmlspecialchars($dateFormatted) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>MONICOMLAB </title>
    <link rel="icon" type="image/x-icon" href="../../img/logo1.png" />

    <!-- Custom fonts for this template-->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <script src="../../js/html2canvas.min.js"></script>
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src='../../js/sweetalert2.all.min.js'></script>

    <!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<!-- jQuery (required for DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<style>
  /* Reduce overall text size - keep default colors */
  body {
    font-size: 14px !important;
  }
  
  /* Table text styling - increased size */
  #dataTable, #appsTable, #disconnectedTable, #issuesTable {
    font-size: 13px !important;
  }
  
  #dataTable th, #appsTable th, #disconnectedTable th, #issuesTable th {
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 10px 8px !important;
  }
  
  #dataTable td, #appsTable td, #disconnectedTable td, #issuesTable td {
    font-size: 12px !important;
    padding: 8px 8px !important;
    line-height: 1.4 !important;
  }
  
  /* Card titles and headers */
  .card-title, .h3, h1, h2, h3 {
    font-size: 18px !important;
  }
  
  /* Stats cards */
  .text-xs {
    font-size: 11px !important;
  }
  
  .h5 {
    font-size: 15px !important;
  }
  
  .suggestion-list ul {
    padding-left: 0;
  }
  .suggestion-list li {
    border: none;
    border-bottom: 1px solid #eaeaea;
    background: #fff;
    transition: background 0.2s;
    font-size: 12px !important;
  }
  .suggestion-list li:hover {
    background: #f8f9fa;
  }
  
  /* Enhanced category badges - reduced size */
  .badge {
    font-size: 11px !important;
    padding: 0.3em 0.5em !important;
  }
  .badge i {
    margin-right: 3px;
  }
  
  /* Category-specific styling */
  .badge-success { background-color: #28a745 !important; color: white !important; } /* Green for Hardware */
  .badge-warning { background-color: #ffc107 !important; color: #212529 !important; } /* Yellow for Software */
  .badge-primary { background-color: #007bff !important; color: white !important; } /* Blue for Network */
  .badge-secondary { background-color: #6c757d !important; color: white !important; }
  .badge-danger { background-color: #dc3545 !important; color: white !important; }
  .badge-info { background-color: #17a2b8 !important; color: white !important; }
  
  /* Button text size - reduced */
  .btn {
    font-size: 12px !important;
    padding: 4px 8px !important;
  }
  
  /* Action column button spacing - minimal */
  #dataTable td:last-child .btn, #disconnectedTable td:last-child .btn, #issuesTable td:last-child .btn {
    margin-right: 2px;
    margin-bottom: 0px;
  }
  
  /* Action column specific styling */
  #dataTable td:last-child, #disconnectedTable td:last-child, #issuesTable td:last-child {
    white-space: nowrap;
    text-align: left !important;
    padding: 4px 6px !important;
    width: 1%;
  }
  
  /* Ensure action buttons wrap nicely */
  .d-flex.flex-wrap {
    align-items: flex-start;
    justify-content: flex-start;
  }
  
  /* Modal and form text - reduced */
  .modal-body, .form-control, .form-label {
    font-size: 13px !important;
  }
  
  /* Breadcrumb and navigation - reduced */
  .breadcrumb-item, .nav-link {
    font-size: 13px !important;
  }
  
  /* Table wrapper fix */
  .table-responsive {
    overflow-x: auto;
  }
  
  /* DataTables wrapper styling */
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate {
    font-size: 12px !important;
  }
  
  /* Ensure tables don't break layout */
  .table {
    margin-bottom: 0 !important;
  }
  
  /* Stats card click animation - box only */
  .stats-card {
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    overflow: hidden !important;
  }
  
  .stats-card:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
  }
  
  .stats-card:active {
    transform: translateY(-2px) scale(0.98) !important;
    transition: all 0.1s ease !important;
  }
  
  .stats-card.clicked {
    animation: card-pulse 0.6s ease-out !important;
  }
  
  @keyframes card-pulse {
    0% { 
      transform: translateY(-5px) scale(1); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
    }
    50% { 
      transform: translateY(-8px) scale(1.05); 
      box-shadow: 0 15px 35px rgba(0,0,0,0.25); 
    }
    100% { 
      transform: translateY(-5px) scale(1); 
      box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
    }
  }
  
  /* Ripple effect for cards */
  .stats-card::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(0,123,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
    z-index: 0;
  }
  
  .stats-card.ripple::before {
    width: 300px;
    height: 300px;
  }
  
  .stats-card > * {
    position: relative;
    z-index: 1;
  }

  /* AI Suggestion content styling - matching troubleshoot page */
  .suggestion-list ul {
    padding-left: 0;
  }
  .suggestion-list li {
    border: none;
    border-bottom: 1px solid #eaeaea;
    background: #fff;
    transition: background 0.2s;
    font-size: 14px !important;
  }
  .suggestion-list li:hover {
    background: #f8f9fa;
  }
  
  #fixSuggestion {
    font-size: 14px !important;
    line-height: 1.6 !important;
  }
  
  #fixSuggestion h6 {
    font-size: 16px !important;
    color: #007bff !important;
    font-weight: 600 !important;
    margin-bottom: 8px !important;
  }
  
  #fixSuggestion li {
    font-size: 14px !important;
    margin-bottom: 5px !important;
    list-style: none !important;
    padding-left: 20px !important;
    position: relative !important;
  }
  
  #fixSuggestion li:before {
    content: "•";
    color: #28a745;
    font-weight: bold;
    position: absolute;
    left: 0;
  }
  
  #fixSuggestion div {
    margin-bottom: 15px !important;
  }
  
  /* Download dropdown styling */
  .dropdown-menu {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    border-radius: 0.35rem;
  }
  
  .dropdown-item {
    padding: 0.75rem 1.5rem;
    font-size: 14px;
    transition: all 0.2s ease-in-out;
  }
  
  .dropdown-item:hover {
    background-color: #f8f9fc;
    color: #5a5c69;
    transform: translateX(5px);
  }
  
  .dropdown-item i {
    width: 20px;
    margin-right: 8px;
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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <?php if(isset($_GET['view']) && $_GET['view'] == 'issues'): ?>
                            <h1 class="h3 mb-0 text-gray-800"></h1>
                        <?php else: ?>
                            <h1 class="h3 mb-0 text-gray-800"></h1>
                        <?php endif; ?>
                    </div>

                    <?php if(!isset($_GET['view']) || $_GET['view'] != 'issues'): ?>
                    <div class="row mb-2 ml-2">

                      <div style="display: flex; gap: 10px; font-family: sans-serif; flex-wrap: wrap;">
                        <!-- Status Filters -->
                        <a href="index.php" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #e8e8e8; color: #333; text-decoration: none; font-weight: 600; border: none;">All</a>
                        
                        <a href="index.php?type=PENDING" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #ffecd1; color: #b8860b; text-decoration: none; font-weight: 600; border: none;">
                            Pending
                        </a>
                        
                        <a href="index.php?type=UNRESOLVED" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #d6ebf5; color: #2874a6; text-decoration: none; font-weight: 600; border: none;">
                            Unresolved
                        </a>
                        
                        <a href="index.php?type=RESOLVED" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #d5f4e6; color: #239b56; text-decoration: none; font-weight: 600; border: none;">
                            Resolved
                        </a>
                        
                        <!-- Date Range Separator -->
                        <div style="border-left: 2px solid #ccc; margin: 0 10px;"></div>
                        
                        <!-- Date Range Filters -->
                        <a href="index.php?report=daily<?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #e8f4f8; color: #2c5aa0; text-decoration: none; font-weight: 600; border: none;">
                            <i class="fas fa-calendar-day"></i> Daily
                        </a>
                        
                        <a href="index.php?report=weekly<?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #f0e8ff; color: #6a4c93; text-decoration: none; font-weight: 600; border: none;">
                            <i class="fas fa-calendar-week"></i> Weekly
                        </a>
                        
                        <a href="index.php?report=monthly<?php echo isset($_GET['type']) ? '&type='.$_GET['type'] : ''; ?>" class="btn" style="font-size: 16px; padding: 8px 16px; background-color: #fff2e8; color: #e67e22; text-decoration: none; font-weight: 600; border: none;">
                            <i class="fas fa-calendar-alt"></i> Monthly
                        </a>
                    </div>
                    <?php endif; ?>

                    </div>

                    <?php if(isset($_GET['view']) && $_GET['view'] == 'issues'): ?>

                        <?php
                        // ================= Connected Devices Totals =================
                        $connectedCount = $conn->query("SELECT COUNT(*) as cnt FROM connected_devices WHERE status = 'Connected'")->fetch_assoc()['cnt'];
                        $disconnectedCount = $conn->query("SELECT COUNT(*) as cnt FROM connected_devices WHERE status IN ('Disconnected', 'Removed')")->fetch_assoc()['cnt'];

                        // ================= Detect Issues Totals =================
                        $onlineCount = $conn->query("SELECT COUNT(*) as cnt FROM detect_issue WHERE value = 'ONLINE'")->fetch_assoc()['cnt'];
                        $offlineCount = $conn->query("SELECT COUNT(*) as cnt FROM detect_issue WHERE value = 'OFFLINE'")->fetch_assoc()['cnt'];
                        ?>

                        <!-- Totals Row -->
                        <div class="row" style="margin: 20px;">
                            <!-- Connected Devices -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-success shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Connected Devices</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $connectedCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Disconnected Devices -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-danger shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Disconnected Devices</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $disconnectedCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Online Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-primary shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Online Issues</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $onlineCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Offline Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Offline Issues</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $offlineCount; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Content Row -->
                        <!-- Issue Notifications Card Style Display -->
                        <div class="card shadow mb-4" style="margin-left: 20px; margin-right: 20px;">
                            <div class="card-body">
                                <!-- Disconnected Devices Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-danger">Disconnected Devices Per PC</h6>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete ALL disconnected devices and issues?');" style="margin: 0;">
                                <button type="submit" name="clear_all" class="btn btn-danger" style="font-size: 16px; padding: 8px 16px;">
                                    <i class="fas fa-trash"></i> Clear All Logs
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="disconnectedTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Room</th>
                                            <th>IP Address</th>
                                            <th>Device Class</th>
                                            <th>Device Name</th>
                                            <th>Status</th>
                                            <th>Last Detected</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get disconnected devices
                                        $discQuery = "
                                            SELECT cd.ip_address, cd.device_class, cd.friendly_name, cd.status, cd.detected_at,
                                                d.name AS device_name, r.room_name
                                            FROM connected_devices cd
                                            LEFT JOIN device d ON cd.ip_address = d.ip_address
                                            LEFT JOIN rooms r ON d.room_id = r.room_id
                                            WHERE cd.status IN ('Disconnected', 'Removed')
                                            ORDER BY cd.ip_address, cd.detected_at DESC
                                        ";
                                        $discResult = $conn->query($discQuery);

                                        if ($discResult && $discResult->num_rows > 0) {
                                            while ($row = $discResult->fetch_assoc()) {
                                                $statusLabel = "<span class='badge badge-danger'>Disconnected</span>";
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['device_name'] ?? 'Unknown') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['room_name'] ?? 'Unknown') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['device_class']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['friendly_name']) . "</td>";
                                                echo "<td>$statusLabel</td>";
                                                echo "<td>" . htmlspecialchars($row['detected_at']) . "</td>";
                                                echo "<td>
                                                        <button class='btn btn-info btn-sm view-fix'
                                                            data-ip='" . htmlspecialchars($row['ip_address']) . "'
                                                            data-device='" . htmlspecialchars($row['friendly_name']) . "'
                                                            data-class='" . htmlspecialchars($row['device_class']) . "'
                                                            data-status='" . htmlspecialchars($row['status']) . "'>
                                                            <i class='fas fa-lightbulb'></i> View Fix
                                                        </button>
                                                        <button class='btn btn-danger btn-sm delete-device' data-ip='" . htmlspecialchars($row['ip_address']) . "'>
                                                            <i class='fas fa-trash'></i> Delete
                                                        </button>

                                                    </td>";
                                                    
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center text-muted'>No disconnected devices found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Issues Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-warning">Detected Issues</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="issuesTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Room</th>
                                    <th>IP Address</th>
                                    <th>Issue</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php
                    // Get issues from detect_issue with PC + Room
                    $issueQuery = "
                        SELECT di.id, di.ip_address, di.name AS issue_name, di.value, di.date, di.color,
                            d.name AS device_name, r.room_name
                        FROM detect_issue di
                        LEFT JOIN device d ON di.ip_address = d.ip_address
                        LEFT JOIN rooms r ON d.room_id = r.room_id
                        ORDER BY di.date DESC
                    ";
                    $issueResult = $conn->query($issueQuery);

                    if ($issueResult && $issueResult->num_rows > 0) {
                        while ($row = $issueResult->fetch_assoc()) {
                            $statusLabel = "<span class='badge badge-" . htmlspecialchars($row['color']) . "'>" 
                                        . htmlspecialchars($row['value']) . "</span>";

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['device_name'] ?? 'Unknown') . "</td>";
                            echo "<td>" . htmlspecialchars($row['room_name'] ?? 'Unknown') . "</td>";
                            echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['issue_name']) . "</td>";
                            echo "<td>$statusLabel</td>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "<td>
                                    <button class='btn btn-info btn-sm view-fix'
                                        data-ip='" . htmlspecialchars($row['ip_address']) . "'
                                        data-device='" . htmlspecialchars($row['issue_name']) . "'
                                        data-class='Issue'
                                        data-status='" . htmlspecialchars($row['value']) . "'
                                        data-pc='" . htmlspecialchars($row['device_name'] ?? 'Unknown') . "'
                                        data-room='" . htmlspecialchars($row['room_name'] ?? 'Unknown') . "'>
                                        <i class='fas fa-lightbulb'></i> View Fix
                                    </button>
                                    <button class='btn btn-danger btn-sm delete-issue' data-id='" . htmlspecialchars($row['id']) . "'>
                                        <i class='fas fa-trash'></i> Delete
                                    </button>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center text-muted'>No issues found</td></tr>";
                    }
                    ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Suggestion Modal -->
            <div class="modal fade" id="fixModal" tabindex="-1" role="dialog" aria-labelledby="fixLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content shadow-lg border-0 rounded-lg">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="fixLabel"><i class="fas fa-lightbulb"></i> AI Suggested Fix</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body bg-light">
                                    <div id="fixSuggestion" class="suggestion-list">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                                            <p>Loading suggestions...</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                <?php else: ?>
                        <?php
                        // Report Statistics Logic
                        $reportTitle = "All Logs";
                        $dateCondition = "";
                        $reportPeriod = "";
                        
                        if (isset($_GET['report'])) {
                            switch ($_GET['report']) {
                                case 'daily':
                                    $dateCondition = "AND DATE(t.date) = CURDATE()";
                                    $reportTitle = "Daily Report";
                                    $reportPeriod = date('F j, Y');
                                    break;
                                case 'weekly':
                                    $dateCondition = "AND WEEK(t.date) = WEEK(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                                    $reportTitle = "Weekly Report";
                                    $reportPeriod = "Week of " . date('F j', strtotime('monday this week')) . " - " . date('F j, Y', strtotime('sunday this week'));
                                    break;
                                case 'monthly':
                                    $dateCondition = "AND MONTH(t.date) = MONTH(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                                    $reportTitle = "Monthly Report";
                                    $reportPeriod = date('F Y');
                                    break;
                            }
                        }
                        
                        // Get report statistics if report is selected
                        if (isset($_GET['report'])) {
                            $statusCondition = "";
                            if (isset($_GET['type'])) {
                                $statusCondition = "AND t.status = '" . $conn->real_escape_string($_GET['type']) . "'";
                            }
                            
                            // Count by status
                            $pendingCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND t.status = 'PENDING' $statusCondition")->fetch_assoc()['cnt'];
                            $unresolvedCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND t.status = 'UNRESOLVED' $statusCondition")->fetch_assoc()['cnt'];
                            $resolvedCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND t.status = 'RESOLVED' $statusCondition")->fetch_assoc()['cnt'];
                            $totalCount = $pendingCount + $unresolvedCount + $resolvedCount;
                            
                            // Count by category
                            $hardwareCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND (
                                LOWER(t.issue_type) LIKE '%mouse%' OR LOWER(t.issue_type) LIKE '%keyboard%' OR 
                                LOWER(t.issue_type) LIKE '%printer%' OR LOWER(t.issue_type) LIKE '%usb%' OR 
                                LOWER(t.issue_type) LIKE '%audio%' OR LOWER(t.issue_type) LIKE '%speaker%' OR 
                                LOWER(t.issue_type) LIKE '%microphone%' OR LOWER(t.issue_type) LIKE '%webcam%' OR 
                                LOWER(t.issue_type) LIKE '%camera%' OR LOWER(t.issue_type) LIKE '%monitor%' OR 
                                LOWER(t.issue_type) LIKE '%display%'
                            ) $statusCondition")->fetch_assoc()['cnt'];
                            
                            $softwareCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND (
                                LOWER(t.issue_type) LIKE '%cpu%' OR LOWER(t.issue_type) LIKE '%ram%' OR 
                                LOWER(t.issue_type) LIKE '%disk%' OR LOWER(t.issue_type) LIKE '%memory%' OR 
                                LOWER(t.issue_type) LIKE '%storage%' OR LOWER(t.issue_type) LIKE '%high usage%' OR
                                LOWER(t.issue_type) LIKE '%application%' OR LOWER(t.issue_type) LIKE '%software%' OR 
                                LOWER(t.issue_type) LIKE '%service%' OR LOWER(t.issue_type) LIKE '%process%'
                            ) $statusCondition")->fetch_assoc()['cnt'];
                            
                            $networkCount = $conn->query("SELECT COUNT(*) as cnt FROM ticket t WHERE 1=1 $dateCondition AND (
                                LOWER(t.issue_type) LIKE '%offline%' OR LOWER(t.issue_type) LIKE '%connection%' OR
                                LOWER(t.issue_type) LIKE '%network%' OR LOWER(t.issue_type) LIKE '%stale data%' OR 
                                LOWER(t.issue_type) LIKE '%ping%'
                            ) $statusCondition")->fetch_assoc()['cnt'];
                        ?>
                        
                        <!-- Report Statistics Cards -->
                        <div class="row" style="margin: 20px;">
                            <div class="col-12 mb-3">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo $reportTitle; ?></div>
                                                <div class="h6 mb-0 font-weight-bold text-gray-600"><?php echo $reportPeriod; ?></div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" style="margin: 20px;">
                            <!-- Total Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-primary shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Issues</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-danger shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Pending</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendingCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unresolved Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unresolved</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $unresolvedCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resolved Issues -->
                            <div class="col-md-3 mb-3">
                                <div class="card border-left-success shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Resolved</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $resolvedCount; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category Statistics Row -->
                        <div class="row" style="margin: 20px;">
                            <!-- Hardware Issues -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-success shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            <i class="fas fa-cogs"></i> Hardware Issues
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $hardwareCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Software Issues -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            <i class="fas fa-desktop"></i> Software Issues
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $softwareCount; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Network Issues -->
                            <div class="col-md-4 mb-3">
                                <div class="card border-left-info shadow h-100 py-2 stats-card">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            <i class="fas fa-network-wired"></i> Network Issues
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $networkCount; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <!-- Regular Logs Table Display -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary"><?php echo isset($_GET['report']) ? $reportTitle : 'Logs'; ?></h6>
                                <div>
                                    <?php if (isset($_GET['report'])): ?>
                                    <div class="dropdown d-inline-block mr-2">
                                        <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="downloadDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-download"></i> Download Report
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="downloadDropdown">
                                            <a class="dropdown-item" href="?<?php echo $_SERVER['QUERY_STRING']; ?>&download=csv">
                                                <i class="fas fa-file-csv"></i> Download as CSV
                                            </a>
                                            <a class="dropdown-item" href="?<?php echo $_SERVER['QUERY_STRING']; ?>&download=excel">
                                                <i class="fas fa-file-excel"></i> Download as Excel
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="downloadPDF()">
                                                <i class="fas fa-file-pdf"></i> Download as PDF
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <button type="button" id="deleteSelected" class="btn btn-danger btn-sm" style="display:none;">
                                        <i class="fas fa-trash"></i> Delete Selected
                                    </button>
                                    <button type="button" id="clearAllLogs" class="btn btn-warning btn-sm">
                                        <i class="fas fa-broom"></i> Clear All Logs
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="myDiv">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selectAll">
                                                </th>
                                                <th>Device</th>
                                                <th>Room</th>
                                                <th>IP Address</th>
                                                <th>Issue Type</th>
                                                <th>Category</th>
                                                <th>Severity</th>
                                                <th>Description</th>
                                                <th>Issue Status</th>
                                                <th>Reported</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Enhanced query similar to troubleshooting page
                                            $condition = "";
                                            if(isset($_GET['type'])){
                                              $condition = "WHERE t.status = '".$_GET['type']."'";
                                            }
                                            
                                            // Add date filtering for reports
                                            $dateCondition = "";
                                            if (isset($_GET['report'])) {
                                                switch ($_GET['report']) {
                                                    case 'daily':
                                                        $dateCondition = "AND DATE(t.date) = CURDATE()";
                                                        break;
                                                    case 'weekly':
                                                        $dateCondition = "AND WEEK(t.date) = WEEK(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                                                        break;
                                                    case 'monthly':
                                                        $dateCondition = "AND MONTH(t.date) = MONTH(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())";
                                                        break;
                                                }
                                            }

                                            // Check which columns exist in the ticket table
                                            $columnQuery = "DESCRIBE ticket";
                                            $columnResult = $conn->query($columnQuery);
                                            $availableColumns = [];
                                            while ($col = $columnResult->fetch_assoc()) {
                                                $availableColumns[] = $col['Field'];
                                            }
                                            
                                            // Build enhanced query with room information - prevent duplicates and exclude multiple device issues
                                            $excludeCondition = "AND t.issue_type != 'Multiple Devices Offline' AND t.issue_type != 'MULTIPLE DEVICES OFFLINE'";
                                            
                                            // Combine all conditions
                                            if ($condition) {
                                                $finalCondition = $condition . " " . $excludeCondition . " " . $dateCondition;
                                            } else {
                                                $finalCondition = "WHERE 1=1 " . $excludeCondition . " " . $dateCondition;
                                            }
                                            
                                                                                        // Combine ticket rows with detect_issue rows so both tickets and detected issues appear
                                                                                        $query = "(
                                                                                                         SELECT
                                                                                                             t.id,
                                                                                                             t.device_name,
                                                                                                             t.issue_type,
                                                                                                             COALESCE(t.category, '') AS category,
                                                                                                             COALESCE(t.severity, '') AS severity,
                                                                                                             t.description,
                                                                                                             t.status,
                                                                                                             t.date,
                                                                                                             d.ip_address,
                                                                                                             r.room_name
                                                                                                         FROM ticket t
                                                                                                         LEFT JOIN device d ON t.device_name = d.name
                                                                                                         LEFT JOIN rooms r ON d.room_id = r.room_id
                                                                                                         $finalCondition
                                                                                                         GROUP BY t.device_name, t.issue_type, t.description, DATE(t.date)
                                                                                                     )
                                                                                                     UNION ALL
                                                                                                     (
                                                                                                         SELECT
                                                                                                             di.id AS id,
                                                                                                             COALESCE(d.name, di.ip_address) AS device_name,
                                                                                                             di.name AS issue_type,
                                                                                                             '' AS category,
                                                                                                             NULL AS severity,
                                                                                                             di.value AS description,
                                                                                                             'PENDING' AS status,
                                                                                                             di.date AS date,
                                                                                                             di.ip_address AS ip_address,
                                                                                                             r.room_name
                                                                                                         FROM detect_issue di
                                                                                                         LEFT JOIN device d ON di.ip_address = d.ip_address
                                                                                                         LEFT JOIN rooms r ON d.room_id = r.room_id
                                                                                                     )
                                                                                                     ORDER BY date DESC";
                                                     
                                            $result = $conn->query($query);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr role='row'>";

                                                // Checkbox column
                                                echo "<td><input type='checkbox' class='row-checkbox' value='" . $row['id'] . "'></td>";

                                                // Issue status color
                                                $color = "";
                                                if($row['status']=='PENDING' || $row['status']=='Pending'){
                                                    $color = "danger";
                                                }
                                                else if($row['status']=='UNRESOLVED'){
                                                    $color = "warning";
                                                }
                                                else if($row['status']=='RESOLVED'){
                                                    $color = "success";
                                                }
                                                
                                                // Device
                                                echo "<td>" . strtoupper($row['device_name']) . "</td>";
                                                
                                                // Room
                                                $roomName = !empty($row['room_name']) ? strtoupper($row['room_name']) : 'N/A';
                                                echo "<td>" . $roomName . "</td>";
                                                
                                                // IP Address
                                                $ipAddress = !empty($row['ip_address']) ? $row['ip_address'] : 'N/A';
                                                echo "<td>" . $ipAddress . "</td>";
                                                
                                                // Issue Type
                                                echo "<td>" . strtoupper($row['issue_type']) . "</td>";
                                                
                                                // Category with logic from troubleshooting - prefer stored category
                                                $category = !empty($row['category']) ? strtoupper($row['category']) : 'GENERAL';
                                                $issueType = strtolower($row['issue_type']);
                                                $deviceName = strtolower($row['device_name']);
                                                // Only infer when category is GENERAL (no stored category)
                                                if ($category === 'GENERAL') {
                                                    // Hardware peripheral device issues
                                                    if (strpos($issueType, 'mouse') !== false || 
                                                        strpos($issueType, 'keyboard') !== false ||
                                                        strpos($issueType, 'printer') !== false ||
                                                        strpos($issueType, 'usb') !== false ||
                                                        strpos($issueType, 'audio') !== false ||
                                                        strpos($issueType, 'speaker') !== false ||
                                                        strpos($issueType, 'microphone') !== false ||
                                                        strpos($issueType, 'webcam') !== false ||
                                                        strpos($issueType, 'camera') !== false ||
                                                        strpos($issueType, 'monitor') !== false ||
                                                        strpos($issueType, 'display') !== false) {
                                                        $category = 'HARDWARE';
                                                    }
                                                    // Resource / OS / app related issues -> SOFTWARE
                                                    elseif (strpos($issueType, 'cpu') !== false || 
                                                        strpos($issueType, 'ram') !== false || 
                                                        strpos($issueType, 'disk') !== false ||
                                                        strpos($issueType, 'memory') !== false ||
                                                        strpos($issueType, 'storage') !== false ||
                                                        strpos($issueType, 'high usage') !== false ||
                                                        strpos($issueType, 'application') !== false ||
                                                        strpos($issueType, 'software') !== false || 
                                                        strpos($issueType, 'service') !== false ||
                                                        strpos($issueType, 'process') !== false) {
                                                        $category = 'SOFTWARE';
                                                    }
                                                    // Network issues
                                                    elseif (strpos($issueType, 'offline') !== false || 
                                                           strpos($issueType, 'connection') !== false ||
                                                           strpos($issueType, 'network') !== false ||
                                                           strpos($issueType, 'stale data') !== false ||
                                                           strpos($issueType, 'ping') !== false) {
                                                        $category = 'NETWORK';
                                                    }
                                                }
                                                
                                                // Category colors
                                                $categoryColor = "";
                                                switch($category) {
                                                    case 'HARDWARE':
                                                        $categoryColor = "success"; // Green
                                                        break;
                                                    case 'SOFTWARE':
                                                        $categoryColor = "warning"; // Yellow
                                                        break;
                                                    case 'NETWORK':
                                                        $categoryColor = "primary"; // Blue
                                                        break;
                                                    default:
                                                        $categoryColor = "secondary";
                                                }
                                                if ($category === 'GENERAL') {
                                                    echo "<td></td>";
                                                } else {
                                                    echo "<td><span class='badge badge-" . $categoryColor . "'>" . $category . "</span></td>";
                                                }
                                                
                                                // Severity
                                                // Determine severity: prefer stored value when non-empty, otherwise infer from issue text
                                                if (in_array('severity', $availableColumns) && isset($row['severity']) && trim((string)$row['severity']) !== '') {
                                                    $severity = strtoupper($row['severity']);
                                                } else {
                                                    // Infer severity from issue_type (works for both ticket and detect_issue rows)
                                                    $issueType = strtolower($row['issue_type']);
                                                    if (strpos($issueType, 'critical') !== false) {
                                                        $severity = 'CRITICAL';
                                                    } elseif (strpos($issueType, 'warning') !== false || strpos($issueType, 'high') !== false) {
                                                        $severity = 'HIGH';
                                                    } else {
                                                        $severity = 'MEDIUM';
                                                    }
                                                }
                                                
                                                $severityColor = "";
                                                switch($severity) {
                                                    case 'CRITICAL':
                                                        $severityColor = "danger";
                                                        break;
                                                    case 'HIGH':
                                                        $severityColor = "warning";
                                                        break;
                                                    case 'MEDIUM':
                                                        $severityColor = "info";
                                                        break;
                                                    case 'LOW':
                                                        $severityColor = "success";
                                                        break;
                                                    default:
                                                        $severityColor = "secondary";
                                                }
                                                echo "<td><span class='badge badge-" . $severityColor . "'>" . $severity . "</span></td>";
                                                
                                                // Description
                                                $description = !empty($row['description']) ? strtoupper($row['description']) : 'N/A';
                                                echo "<td style='width: 250px;'>" . $description . "</td>";
                                                
                                                // Issue Status
                                                echo "<td><span class='badge badge-" . $color . "'>" . strtoupper($row['status']) . "</span></td>";
                                                
                                                // Reported date - handle NULL or invalid dates
                                                $dateFormatted = 'N/A';
                                                if (!empty($row['date']) && $row['date'] !== '0000-00-00 00:00:00') {
                                                    $timestamp = strtotime($row['date']);
                                                    if ($timestamp !== false) {
                                                        $dateFormatted = date('M d, Y g:i A', $timestamp);
                                                    }
                                                }
                                                echo "<td style='width: 150px;'>" . $dateFormatted . "</td>";
                                                
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <!-- End of Main Content -->
        </div>
        <!-- End of Content Wrapper -->
            <?php include'../footer.php';?>

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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

    <!-- Page level plugins -->
    <script src="../../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/demo/chart-area-demo.js"></script>
    <script src="../../js/demo/chart-pie-demo.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
      $(function () {
        $("#dataTable").DataTable({
          "autoWidth": false,
          "responsive": true,
          "pageLength": 10,
          "lengthMenu": [5, 10, 25, 50, 100]
        });
      });
    </script>
    <!-- SweetAlert2 -->
<!-- jQuery -->
<script>
$(document).ready(function() {
    $('.update-status').on('click', function() {
        var ticketId = $(this).data('id');
        var newStatus = $(this).data('status');

        Swal.fire({
            title: 'Are you sure?',
            text: "Set ticket #00" + ticketId + " as " + newStatus + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: {
                        id: ticketId,
                        status: newStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Updated!',
                            text: response,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // refresh the table after update
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                });
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {
    $('.view-suggestion').on('click', function() {
        var name = $(this).data('name');
        var ip = $(this).data('ip');
        var value = $(this).data('value');

        $('#aiSuggestion').html(`
            <div class="text-center text-muted">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading suggestions...</p>
            </div>
        `);
        $('#suggestionModal').modal('show');

        // Call Gemini API
        $.ajax({
            url: "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=AIzaSyBAWR5fbU_6DmNDhy-3u3RBgiD3tIakLLw",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                systemInstruction: {
                  parts: [
                    { text: "You are a network and system troubleshooting assistant. Always respond with a numbered list of suggestions, max 500 words." }
                  ]
                },
                contents: [
                    {
                        parts: [
                          { text: "Device: " + name + "\nIP Address: " + ip + "\nIssue Value: " + value + "\n\nProvide troubleshooting suggestions in a numbered list (maximum 500 words)." }
                        ]
                    }
                ]
            }),
            success: function(response) {
                var suggestion = "";
                if (response.candidates && response.candidates.length > 0 &&
                    response.candidates[0].content &&
                    response.candidates[0].content.parts &&
                    response.candidates[0].content.parts[0].text) {
                    
                    suggestion = response.candidates[0].content.parts[0].text;

                    // Format into list with icons
                    var formatted = "<ul class='list-group'>";
                    suggestion.split(/\n/).forEach(function(line) {
                        if (line.trim() !== "") {
                            formatted += "<li class='list-group-item d-flex align-items-start'>" +
                                         "<i class='fas fa-cog text-info mr-2 mt-1'></i>" +
                                         "<span>" + line.replace(/^\d+[\).]\s*/, '') + "</span>" +
                                         "</li>";
                        }
                    });
                    formatted += "</ul>";

                    $('#aiSuggestion').html(formatted);
                } else {
                    $('#aiSuggestion').html("<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> No valid suggestion returned by AI.</div>");
                }
            },
            error: function(xhr) {
                $('#aiSuggestion').html("<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error fetching suggestion from AI.</div>");
                console.error("AI error:", xhr.responseText);
            }
        });
    });
});
</script>

<script>
    $(document).ready(function() {
    // Handle View Fix button click
    $('.view-fix').on('click', function() {
        var ip = $(this).data('ip');
        var device = $(this).data('device');
        var devClass = $(this).data('class');
        var status = $(this).data('status');

        $('#fixSuggestion').html(`
            <div class="text-center text-muted">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading suggestions...</p>
            </div>
        `);
        $('#fixModal').modal('show');

        // Call Gemini API
        $.ajax({
            url: "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=AIzaSyBAWR5fbU_6DmNDhy-3u3RBgiD3tIakLLw",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                systemInstruction: {
                  parts: [
                    { text: "You are a network and hardware troubleshooting assistant. Always respond with a numbered list of clear, practical suggestions (max 500 words)." }
                  ]
                },
                contents: [
                    {
                        parts: [
                          { text: "Device: " + device + 
                                  "\nDevice Class: " + devClass + 
                                  "\nIP Address: " + ip + 
                                  "\nStatus: " + status + 
                                  "\n\nProvide troubleshooting suggestions in a numbered list." }
                        ]
                    }
                ]
            }),
            success: function(response) {
                var suggestion = "";
                if (response.candidates && response.candidates.length > 0 &&
                    response.candidates[0].content &&
                    response.candidates[0].content.parts &&
                    response.candidates[0].content.parts[0].text) {
                    
                    suggestion = response.candidates[0].content.parts[0].text;

                    // Remove asterisks and format the text properly - matching troubleshoot page
                    suggestion = suggestion.replace(/\*+/g, ''); // Remove all asterisks (single, double, triple, etc.)
                    suggestion = suggestion.replace(/\*\*([^*]+)\*\*/g, '$1'); // Remove markdown bold formatting
                    suggestion = suggestion.replace(/\*([^*]+)\*/g, '$1'); // Remove markdown italic formatting
                    
                    // Format the suggestion with proper HTML structure and sizing
                    var formattedSuggestion = suggestion.replace(/(\d+\.\s)/g, '<div class="mb-3"><h6 class="text-primary font-weight-bold">$1</h6>')
                                                      .replace(/•\s/g, '<li class="mb-1" style="font-size: 14px;">')
                                                      .replace(/\n\n/g, '</div><div class="mt-2">')
                                                      .replace(/\n/g, '</li><li class="mb-1" style="font-size: 14px;">');
                    
                    // Wrap in proper container with better styling
                    var finalHTML = '<div style="font-size: 14px; line-height: 1.6;">' + formattedSuggestion + '</div>';

                    $('#fixSuggestion').html(finalHTML);
                } else {
                    $('#fixSuggestion').html("<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> No valid suggestion returned by AI.</div>");
                }
            },
            error: function(xhr) {
                $('#fixSuggestion').html("<div class='alert alert-danger'><i class='fas fa-times-circle'></i> Error fetching suggestion from AI.</div>");
                console.error("AI error:", xhr.responseText);
            }
        });
    });
});

</script>

<script>
$(document).ready(function() {
    $('#disconnectedTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "autoWidth": false,
        "responsive": true,
        "order": [[ 6, "desc" ]] // Sort by Last Detected column descending
    });
    $('#issuesTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 25, 50, 100],
        "autoWidth": false,
        "responsive": true,
        "order": [[ 5, "desc" ]] // Sort by Date column descending
    });
});
</script>

<script>
$(document).ready(function() {
    // Delete disconnected device
    $('.delete-device').click(function() {
        var ip = $(this).data('ip');
        if (confirm('Are you sure you want to delete this device?')) {
            $('<form method="POST"><input type="hidden" name="delete_device" value="'+ip+'"></form>')
                .appendTo('body').submit();
        }
    });

    // Delete issue
    $('.delete-issue').click(function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to delete this issue?')) {
            $('<form method="POST"><input type="hidden" name="delete_issue" value="'+id+'"></form>')
                .appendTo('body').submit();
        }
    });
});
</script>

<!-- Checkbox and Delete Functionality -->
<script>
$(document).ready(function() {
    // Select All checkbox functionality
    $('#selectAll').change(function() {
        $('.row-checkbox').prop('checked', $(this).is(':checked'));
        toggleDeleteButton();
    });

    // Individual checkbox functionality
    $(document).on('change', '.row-checkbox', function() {
        var allChecked = $('.row-checkbox').length === $('.row-checkbox:checked').length;
        $('#selectAll').prop('checked', allChecked);
        toggleDeleteButton();
    });

    // Show/hide delete button based on selection
    function toggleDeleteButton() {
        if ($('.row-checkbox:checked').length > 0) {
            $('#deleteSelected').show();
        } else {
            $('#deleteSelected').hide();
        }
    }

    // Delete selected functionality
    $('#deleteSelected').click(function() {
        var selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to delete ' + selectedIds.length + ' selected log(s). This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form to submit selected IDs
                    var form = $('<form method="POST">')
                        .append('<input type="hidden" name="delete_selected" value="1">');
                    
                    selectedIds.forEach(function(id) {
                        form.append('<input type="hidden" name="selected_tickets[]" value="' + id + '">');
                    });
                    
                    $('body').append(form);
                    form.submit();
                }
            });
        }
    });

    // Clear all logs functionality
    $('#clearAllLogs').click(function() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete ALL logs. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, clear all logs!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('<form method="POST"><input type="hidden" name="clear_all_tickets" value="1"></form>')
                    .appendTo('body').submit();
            }
        });
    });
    
    // Stats card click animation - box only
    $('.stats-card').on('click', function(e) {
        var $card = $(this);
        
        // Remove any existing animation classes
        $card.removeClass('clicked ripple');
        
        // Add click animation
        $card.addClass('clicked');
        
        // Add ripple effect
        setTimeout(() => {
            $card.addClass('ripple');
        }, 100);
        
        // Reset after animation
        setTimeout(() => {
            $card.removeClass('clicked ripple');
        }, 600);
        
        // Optional: Add some feedback
        console.log('Logs card clicked!');
    });
    
    // Add pointer cursor hint
    $('.stats-card').css('cursor', 'pointer');
});
</script>

<!-- PDF Download Function -->
<script>
function downloadPDF() {
    // Step 1: Collect unique rooms from the table
    var rooms = new Set();
    $('#dataTable tbody tr').each(function() {
        var room = $(this).find('td:nth-child(3)').text().trim(); // Adjust if 'Room' column index changes
        if (room) rooms.add(room);
    });

    var roomList = Array.from(rooms);

    // Step 2: Ask the user which room to download
    Swal.fire({
        title: 'Select Room',
        input: 'select',
        inputOptions: roomList.reduce((opts, room) => {
            opts[room] = room;
            return opts;
        }, {}),
        inputPlaceholder: 'Choose a room',
        showCancelButton: true,
        confirmButtonText: 'Generate PDF',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'Please select a room first';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var selectedRoom = result.value;

            // Step 3: Continue PDF generation
            var reportTitle = $('.card-header h6').text() || 'Logs_Report';
            reportTitle = reportTitle.replace(/\s+/g, '_');
            
            var currentDate = new Date();
            var dateStr = currentDate.getFullYear() + '-' + 
                        String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(currentDate.getDate()).padStart(2, '0');
            
            var filename = reportTitle + '_' + selectedRoom + '_' + dateStr + '.pdf';

            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait while we generate your report for ' + selectedRoom,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });

            var doc = new jsPDF('l', 'mm', 'a4');
            var img = new Image();
            var img1 = new Image();
            img.src = '../../img/logo-lspu.png';
            img1.src = '../../img/logo-ccs.png';

            img.onload = function() {
                // --- HEADER ---
                doc.addImage(img, 'PNG', 65, 20, 25, 25);
                doc.addImage(img1, 'PNG', 210, 20, 25, 25);

                doc.setFontSize(20);
                doc.setFont(undefined, 'bold');
                doc.text('MONICOMLAB - ' + reportTitle.replace(/_/g, ' '), 100, 25);
                
                doc.setFontSize(12);
                doc.setFont(undefined, 'normal');
                doc.text('Generated on: ' + new Date().toLocaleString(), 110, 35);
                doc.text('Room: ' + selectedRoom, 65, 60);

                var startY = 50;

                <?php if (isset($_GET['report'])): ?>
                doc.text('Report Period: <?php echo $reportPeriod; ?>', 115, 45);
                var startY = 60;
                <?php else: ?>
                var startY = 50;
                <?php endif; ?>
                
                // Add statistics if in report mode
                <?php if (isset($_GET['report'])): ?>
                doc.setFontSize(14);
                doc.setFont(undefined, 'bold');
                doc.text('Report Summary:', 20, startY);
                
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                startY += 10;
                doc.text('Total Issues: <?php echo $totalCount; ?>', 20, startY);
                doc.text('Pending: <?php echo $pendingCount; ?>', 80, startY);
                doc.text('Unresolved: <?php echo $unresolvedCount; ?>', 130, startY);
                doc.text('Resolved: <?php echo $resolvedCount; ?>', 180, startY);
                
                startY += 10;
                doc.text('Hardware Issues: <?php echo $hardwareCount; ?>', 20, startY);
                doc.text('Software Issues: <?php echo $softwareCount; ?>', 80, startY);
                doc.text('Network Issues: <?php echo $networkCount; ?>', 140, startY);
                
                startY += 20;
                <?php endif; ?>

                // --- TABLE HEADERS ---
                var headers = ['Device', 'Room', 'IP Address', 'Issue Type', 'Category', 'Severity','Description', 'Status', 'Date'];
                var headerX = [20, 45, 75, 105, 145, 175, 205, 235, 255];
                
                doc.setFillColor(52, 73, 94);
                doc.rect(15, startY - 5, 265, 8, 'F');
                doc.setTextColor(255, 255, 255);
                doc.setFontSize(8);
                doc.setFont(undefined, 'bold');
                for (var i = 0; i < headers.length; i++) {
                    doc.text(headers[i], headerX[i], startY);
                }

                startY += 10;
                doc.setTextColor(0, 0, 0);
                doc.setFont(undefined, 'normal');

                // --- FILTERED TABLE ROWS ---
                var tableData = [];
                $('#dataTable tbody tr').each(function() {
                    var row = [];
                    var room = $(this).find('td:nth-child(3)').text().trim();
                    if (room === selectedRoom) {
                        $(this).find('td').each(function(index) {
                            if (index > 0 && index < 10) {
                                row.push($(this).text().trim().replace(/\s+/g, ' '));
                            }
                        });
                        if (row.length > 0) tableData.push(row);
                    }
                });

                // --- PAGINATION ---
                var rowHeight = 6;
                var pageHeight = 210;
                var maxRowsPerPage = Math.floor((pageHeight - startY - 20) / rowHeight);
                
                for (var i = 0; i < tableData.length; i++) {
                    if (i > 0 && i % maxRowsPerPage === 0) {
                        doc.addPage();
                        startY = 20;
                        doc.setFillColor(52, 73, 94);
                        doc.rect(15, startY - 5, 265, 8, 'F');
                        doc.setTextColor(255, 255, 255);
                        doc.setFont(undefined, 'bold');
                        for (var j = 0; j < headers.length; j++) {
                            doc.text(headers[j], headerX[j], startY);
                        }
                        startY += 10;
                        doc.setTextColor(0, 0, 0);
                        doc.setFont(undefined, 'normal');
                    }

                    var row = tableData[i];
                    var currentY = startY + (i % maxRowsPerPage) * rowHeight;
                    if (i % 2 === 1) {
                        doc.setFillColor(248, 249, 250);
                        doc.rect(15, currentY - 3, 265, rowHeight, 'F');
                    }

                    for (var j = 0; j < Math.min(row.length, headerX.length); j++) {
                        var text = row[j].toString();
                        if (text.length > 15) text = text.substring(0, 12) + '...';
                        doc.text(text, headerX[j], currentY);
                    }
                }

                // --- FOOTER ---
                var totalPages = doc.internal.getNumberOfPages();
                for (var i = 1; i <= totalPages; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.text('Page ' + i + ' of ' + totalPages, 250, 200);
                    doc.text('Generated by MONICOMLAB System', 20, 200);
                }

                doc.save(filename);
                Swal.close();
                Swal.fire({
                    title: 'Success!',
                    text: 'PDF report for ' + selectedRoom + ' has been downloaded successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            };

            img.onerror = function() {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Logo image not found or failed to load.',
                    icon: 'error'
                });
            };
        }
    });
}


</script>

<!-- Include jsPDF library for PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
// Make jsPDF available globally
window.jsPDF = window.jspdf.jsPDF;
</script>




</body>

</html>
<?php
}
else
{
    header('location:../../index.php');
}?>