<?php
include '../../connectMySql.php';
include '../../loginverification.php';

// Set timezone to Philippine time
date_default_timezone_set('Asia/Manila');

// Automatically run issue detection when page loads
include 'automatic_issue_detection.php';

if(logged_in()){
    // Run automatic issue detection
    $autoDetectedIssues = detectSystemIssues();
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

<style>
  /* Reduce overall text size - keep default colors */
  body {
    font-size: 14px !important;
  }
  
  /* Table text styling - increased size */
  #dataTable, #appsTable {
    font-size: 13px !important;
  }
  
  #dataTable th, #appsTable th {
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 10px 8px !important;
  }
  
  #dataTable td, #appsTable td {
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
  
  /* AI Suggestion content styling */
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
  
  /* Enhanced category badges - increased size */
  .badge {
    font-size: 11px !important;
    padding: 0.35em 0.55em !important;
  }
  .badge i {
    margin-right: 3px;
  }
  
  /* Category-specific styling */
  .badge-success { background-color: #28a745 !important; color: white !important; } /* Green for Hardware */
  .badge-warning { background-color: #ffc107 !important; color: #212529 !important; } /* Yellow for Software */
  .badge-primary { background-color: #007bff !important; color: white !important; } /* Blue for Network */
  .badge-secondary { background-color: #6c757d !important; color: white !important; }
  
  /* Button text size - increased */
  .btn {
    font-size: 12px !important;
    padding: 5px 10px !important;
  }
  
  /* Action column button spacing - minimal */
  #dataTable td:last-child .btn {
    margin-right: 1px;
    margin-bottom: 1px;
  }
  
  /* Action column specific styling */
  #dataTable td:last-child {
    white-space: nowrap;
    text-align: left !important;
  }
  
  /* Ensure action buttons wrap nicely */
  .d-flex.flex-wrap {
    align-items: flex-start;
    justify-content: flex-start;
  }
  
  /* Modal and form text - increased */
  .modal-body, .form-control, .form-label {
    font-size: 13px !important;
  }
  
  /* Breadcrumb and navigation - increased */
  .breadcrumb-item, .nav-link {
    font-size: 13px !important;
  }
  
  /* Card click animation */
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
</style>


</head>

<body id="page-top" style="zoom: ">

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
                    </div>


                    <div class="row">

                      <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2 stats-card">
                          <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Ticket</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                              $query = "SELECT count(id) as total FROM ticket 
                                       WHERE status IN ('PENDING', 'Pending')
                                       AND issue_type NOT LIKE '%MULTIPLE DEVICES OFFLINE%'
                                       AND description NOT LIKE '%MULTIPLE DEVICES OFFLINE%'";
                              $result = $conn->query($query);
                              while ($row = $result->fetch_assoc()) {
                                echo $row['total'];
                              }
                              ?></div>
                          </div>
                        </div>
                      </div>

                      <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2 stats-card">
                          <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                              $query = "SELECT count(id) as total FROM ticket 
                                       WHERE status IN ('PENDING', 'Pending')
                                       AND issue_type NOT LIKE '%MULTIPLE DEVICES OFFLINE%'
                                       AND description NOT LIKE '%MULTIPLE DEVICES OFFLINE%'";
                              $result = $conn->query($query);
                              while ($row = $result->fetch_assoc()) {
                                echo $row['total'];
                              }
                              ?></div>
                          </div>
                        </div>
                      </div>

                      <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2 stats-card">
                          <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Unresolved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                              $query = "SELECT count(id) as total FROM ticket WHERE status = 'UNRESOLVED'";
                              $result = $conn->query($query);
                              while ($row = $result->fetch_assoc()) {
                                echo $row['total'];
                              }
                              ?></div>
                          </div>
                        </div>
                      </div>

                      <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2 stats-card">
                          <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Resolved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php
                              $query = "SELECT count(id) as total FROM ticket WHERE status = 'RESOLVED' ";
                              $result = $conn->query($query);
                              while ($row = $result->fetch_assoc()) {
                                echo $row['total'];
                              }
                              ?></div>
                          </div>
                        </div>
                      </div>

                    </div>


                    <!-- Content Row -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Automatic Troubleshooting logs
                            <div class="float-right">
                                <button id="runScanBtn" class="btn btn-sm btn-info shadow-sm mr-2">
                                    <i class="fas fa-search"></i> Run System Scan
                                </button>
                                <a href="register.php" class="btn btn-sm btn-primary shadow-sm">
                                    <i class="fas fa-plus"></i> Submit Manually
                                </a>
                            </div>D
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" id="myDiv">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <!-- <th>TicketID</th> -->
                                    <th>Device</th>
                                    <th>Room</th>
                                    <th>Issue Type</th>
                                    <th>Category</th>
                                    <th>Severity</th>
                                    <th>Description</th>
                                    <th>Issue Status</th>
                                    <th>Email Status</th>
                                    <th>Reported</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Check which columns exist in the ticket table
                                $columnQuery = "DESCRIBE ticket";
                                $columnResult = $conn->query($columnQuery);
                                $availableColumns = [];
                                while ($col = $columnResult->fetch_assoc()) {
                                    $availableColumns[] = $col['Field'];
                                }
                                
                                // Build dynamic query based on available columns
                                $selectFields = "t.*, el.status AS email_status, d.ip_address, r.room_name";
                                
                                if (in_array('auto_detected', $availableColumns)) {
                                    $selectFields .= ", CASE WHEN t.auto_detected = 1 THEN 'Auto-Detected' ELSE 'Manual Report' END as detection_type";
                                } else {
                                    $selectFields .= ", 'Manual Report' as detection_type";
                                }
                                
                // Enhanced query: fetch ticket rows AND detect_issue rows (mapped) so both appear
                $query = <<<SQL
                (
                  SELECT
                    t.id,
                    t.device_name,
                    t.issue_type,
                    COALESCE(t.category, '') AS category,
                    COALESCE(t.severity, '') AS severity,
                    t.description,
                    t.status,
                    t.date,
                    t.created_at,
                    el.status AS email_status,
                    d.ip_address,
                    r.room_name,
                    COALESCE(t.auto_detected, 0) AS auto_detected
                  FROM ticket t
                  LEFT JOIN (
                    SELECT ticket_id, status
                    FROM email_logs
                    WHERE id IN (
                      SELECT MAX(id) FROM email_logs GROUP BY ticket_id
                    )
                  ) el ON t.id = el.ticket_id
                  LEFT JOIN device d ON t.device_name = d.name
                  LEFT JOIN rooms r ON d.room_id = r.room_id
                  WHERE t.status IN ('PENDING', 'Pending')
                  AND t.issue_type NOT LIKE '%MULTIPLE DEVICES OFFLINE%'
                  AND t.description NOT LIKE '%MULTIPLE DEVICES OFFLINE%'
                )
                UNION ALL
                (
                  SELECT
                    di.id,
                    COALESCE(d.name, di.ip_address) AS device_name,
                    di.name AS issue_type,
                    '' AS category,
                    NULL AS severity,
                    di.value AS description,
                    'PENDING' AS status,
                    di.date AS date,
                    di.date AS created_at,
                    NULL AS email_status,
                    di.ip_address,
                    r.room_name,
                    1 AS auto_detected
                  FROM detect_issue di
                  LEFT JOIN device d ON di.ip_address = d.ip_address
                  LEFT JOIN rooms r ON d.room_id = r.room_id
                )
                ORDER BY created_at DESC, id DESC
                SQL;


                                $result = $conn->query($query);

                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr role='row'>";

                                    // Issue status color
                                    $color = "";
                                    if ($row['status'] == 'PENDING' || $row['status'] == 'Pending') {
                                        $color = "danger";
                                    } elseif ($row['status'] == 'UNRESOLVED') {
                                        $color = "warning";
                                    } elseif ($row['status'] == 'RESOLVED') {
                                        $color = "success";
                                    }

                                    // Email status color
                                    $emailColor = "";
                                    $emailStatus = $row['email_status'] ?? 'NOT SENT';
                                    if ($emailStatus == 'SENT') {
                                        $emailColor = "success";
                                    } elseif ($emailStatus == 'FAILED') {
                                        $emailColor = "danger";
                                    } else {
                                        $emailColor = "secondary"; // NOT SENT
                                    }

                                    // Device name
                                    $deviceDisplay = strtoupper($row['device_name']);
                                    echo "<td>" . $deviceDisplay . "</td>";
                                    
                                    // Room column with better error handling
                                    $roomName = 'N/A';
                                    if (!empty($row['room_name'])) {
                                        $roomName = strtoupper($row['room_name']);
                                    } elseif (!empty($row['ip_address'])) {
                                        // Try to get room from IP if device name matching failed
                                        $roomQuery = "SELECT r.room_name FROM device d 
                                                     LEFT JOIN rooms r ON d.room_id = r.room_id 
                                                     WHERE d.ip_address = ?";
                                        $stmt = $conn->prepare($roomQuery);
                                        if ($stmt) {
                                            $stmt->bind_param("s", $row['ip_address']);
                                            $stmt->execute();
                                            $roomResult = $stmt->get_result();
                                            if ($roomRow = $roomResult->fetch_assoc()) {
                                                $roomName = !empty($roomRow['room_name']) ? strtoupper($roomRow['room_name']) : 'N/A';
                                            }
                                        }
                                    }
                                    echo "<td>" . $roomName . "</td>";
                                    
                                    echo "<td>" . strtoupper($row['issue_type']) . "</td>";
                                    
                  // Category with better display and improved logic
                  // Prefer stored category from the ticket if available
                  $category = (!empty($row['category'])) ? strtoupper($row['category']) : 'GENERAL';
                                    
                  // Only re-infer category if none stored
                  if ($category === 'GENERAL') {
                    $issueType = strtolower($row['issue_type']);
                    $deviceName = strtolower($row['device_name']);
                                    
                                    // Hardware peripheral device issues (mouse, keyboard, etc.)
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
                                    // Resource monitoring issues - check device type
                                    elseif (strpos($issueType, 'cpu') !== false || 
                                        strpos($issueType, 'ram') !== false || 
                                        strpos($issueType, 'disk') !== false ||
                                        strpos($issueType, 'memory') !== false ||
                                        strpos($issueType, 'storage') !== false) {
                                        
                                        // If it's a PC/Laptop, classify as SOFTWARE (system monitoring)
                                        if (strpos($deviceName, 'pc') !== false || 
                                            strpos($deviceName, 'laptop') !== false || 
                                            strpos($deviceName, 'workstation') !== false ||
                                            strpos($deviceName, 'computer') !== false) {
                                            $category = 'SOFTWARE';
                                        } else {
                                            $category = 'HARDWARE'; // Server/Infrastructure
                                        }
                                    }
                                    // Network issues
                                    elseif (strpos($issueType, 'offline') !== false || 
                                           strpos($issueType, 'connection') !== false ||
                                           strpos($issueType, 'network') !== false ||
                                           strpos($issueType, 'stale data') !== false ||
                                           strpos($issueType, 'ping') !== false) {
                                        $category = 'NETWORK';
                                    }
                                    // Software issues
                                    elseif (strpos($issueType, 'application') !== false ||
                                           strpos($issueType, 'software') !== false ||
                                           strpos($issueType, 'service') !== false ||
                                           strpos($issueType, 'process') !== false) {
                                        $category = 'SOFTWARE';
                                    }
                                    
                                    }

                                    // Enhanced category colors and badges
                                    $categoryColor = "";
                                    switch($category) {
                                        case 'HARDWARE':
                                            $categoryColor = "success"; // Green color
                                            break;
                                        case 'SOFTWARE':
                                            $categoryColor = "warning"; // Yellow color
                                            break;
                                        case 'NETWORK':
                                            $categoryColor = "primary"; // Blue color
                                            break;
                                        default:
                                            $categoryColor = "secondary";
                                    }
                                    echo "<td><span class='badge badge-" . $categoryColor . "'>" . $category . "</span></td>";
                                    
                                    // Enhanced severity with color coding
                  // Normalize severity: treat NULL or empty string as missing and try to infer
                  $rawSeverity = $row['severity'] ?? null;
                  $rawSeverity = is_string($rawSeverity) ? trim($rawSeverity) : $rawSeverity;

                  if (!empty($rawSeverity)) {
                    $severity = strtoupper($rawSeverity);
                  } else {
                    // If DB field exists but empty or NULL, infer from issue type instead of showing blank
                    $issueType = strtolower($row['issue_type'] ?? '');

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
                                    
                                    // Description without location
                                    $description = strtoupper($row['description']);
                                    echo "<td style='width: 250px;'>" . $description . "</td>";

                                    // Issue Status
                                    echo "<td><span class='badge badge-" . $color . "'>" . strtoupper($row['status']) . "</span></td>";

                                    // Email Status
                                    echo "<td><span class='badge badge-" . $emailColor . "'>" . strtoupper($emailStatus) . "</span></td>";

                                    // Reported date - Fixed Philippine time display
                                    date_default_timezone_set('Asia/Manila');
                                    
                                    // Since dates are already stored in Philippine time, just format them
                                    $datetime = new DateTime($row['date'], new DateTimeZone('Asia/Manila'));
                                    $formatted_date = $datetime->format('M d, Y h:i A');
                                    
                                    echo "<td style='width: 100px;'>" . $formatted_date . "</td>";

                                    // Prepare safe values for action buttons
                                    $device = htmlspecialchars($row['device_name']);
                                    $issue = htmlspecialchars($row['issue_type']);
                                    $desc = htmlspecialchars($row['description']);
                                    $ts = htmlspecialchars($row['date']);
                                    $loc = (in_array('location', $availableColumns) && isset($row['location'])) ? htmlspecialchars($row['location']) : '';
                                    $room = htmlspecialchars($row['room_name'] ?? 'N/A');

                                    // Enhanced action buttons
                                    echo "<td style='min-width: 200px; width: 200px; padding: 4px 6px;'>
                                            <div class='d-flex flex-wrap gap-1' style='gap: 1px;'>
                                                <button class='btn btn-success btn-sm update-status mb-1' data-id='" . $row['id'] . "' data-status='RESOLVED' title='Mark as Resolved' style='margin-right: 1px; margin-bottom: 1px;'>
                                                    <i class='fas fa-check'></i>
                                                </button>
                                                <button class='btn btn-danger btn-sm update-status mb-1' data-id='" . $row['id'] . "' data-status='UNRESOLVED' title='Mark as Unresolved' style='margin-right: 1px; margin-bottom: 1px;'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                                <button title='AI Suggested Fix' class='btn btn-info btn-sm view-fix mb-1'
                                                    data-issue-type=\"{$issue}\"
                                                    data-description=\"{$desc}\"
                                                    style='margin-right: 1px; margin-bottom: 1px;'>
                                                    <i class='fas fa-lightbulb'></i>
                                                </button>
                                                <button class='btn btn-primary btn-sm email-btn mb-1' 
                                                        data-ticket-id='" . $row['id'] . "'
                                                        data-to='technician@gmail.com'
                                                        data-device=\"{$device}\"
                                                        data-room=\"{$room}\"
                                                        data-location=\"{$loc}\"
                                                        data-issue=\"{$issue}\"
                                                        data-description=\"{$desc}\"
                                                        data-timestamp=\"{$ts}\"
                                                        title='Send email to technician'
                                                        style='margin-right: 1px; margin-bottom: 1px;'>
                                                        <i class='fas fa-envelope'></i>
                                                </button>";
                                    
                  // Only show Remote Fix for devices with IP addresses
                  // but hide it for peripheral/disconnected issues where remote fix isn't applicable
                  $issueLower = strtolower($row['issue_type'] ?? '');
                  $isPeripheralIssue = (strpos($issueLower, 'peripheral') !== false) || (strpos($issueLower, 'peripheral disconnected') !== false);
                  // Only show Remote Fix for devices with IP addresses, non-peripheral issues,
                  // and not for NETWORK or HARDWARE categories (remote fix not applicable)

                  if (!empty($row['ip_address']) && !$isPeripheralIssue && strtoupper($category) !== 'NETWORK' && strtoupper($category) !== 'HARDWARE') {
                    echo "<button class='btn btn-warning btn-sm view-apps mb-1' 
                        data-ip='".$row['ip_address']."' 
                        title='View running applications'
                        style='margin-right: 1px; margin-bottom: 1px;'>
                        <i class='fas fa-desktop'></i>
                      </button>";
                  }

                                    echo "</div></td>";

                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                            </div>
                        </div>
                    </div>

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

<!-- View Fix Modal -->
<div class="modal fade" id="viewFixModal" tabindex="-1" role="dialog" aria-labelledby="viewFixLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="viewFixLabel"><i class="fas fa-lightbulb"></i> AI Suggested Fix</h5>
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

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="emailModalLabel"><i class="fas fa-envelope"></i> New Message</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body bg-light">
        <!-- header area -->
        <div class="p-3 rounded" style="background:#e9f0fb;">
          <div class="form-group">
            <label><strong>To:</strong></label>
            <input type="email" class="form-control" id="emailToInput" placeholder="Enter recipient email">
          </div>
          <div class="form-group">
            <label><strong>Subject:</strong></label>
            <input type="text" class="form-control" id="emailSubjectInput" readonly>
          </div>
        </div>

        <!-- hidden form data -->
        <input type="hidden" id="emailTicketId" value="">

        <!-- textarea for body -->
        <div class="mt-3">
          <textarea id="emailBody" class="form-control" rows="12"></textarea>
        </div>
      </div>

      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
        <button type="button" id="sendEmailBtn" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send</button>
      </div>
    </div>
  </div>
</div>


<!-- View Apps Modal -->
<div class="modal fade" id="viewAppsModal" tabindex="-1" role="dialog" aria-labelledby="viewAppsLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0 rounded-lg">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="viewAppsLabel"><i class="fas fa-desktop"></i> Running Applications</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body bg-light">
        <div class="table-responsive">
          <table class="table table-bordered" id="appsTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Application</th>
                <th>PID</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="appsTableBody">
              <tr><td colspan="3">Select a row to view apps...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Close</button>
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

    <!-- Page level plugins -->
    <script src="../../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../../js/demo/chart-area-demo.js"></script>
    <script src="../../js/demo/chart-pie-demo.js"></script>
    <script src="../../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
// Allowed apps only
const allowedApps = [
    'chrome.exe',
    'msedge.exe',
    'firefox.exe',
    'opera.exe',
    'calculator.exe',
    'Microsoft.Photos.exe',

    'cmd.exe',
    'powershell.exe',
    'taskmgr.exe',

    'code.exe',
    'notepad.exe',
    'notepad++.exe',
    'sublime_text.exe',

    'winword.exe', 
    'excel.exe',   
    'powerpnt.exe',
    'onenote.exe',
    'outlook.exe',

    'mspaint.exe',
    'photoshop.exe',
    'gimp.exe',

    'vlc.exe',
    'wmplayer.exe',
    'spotify.exe',

    'teams.exe',
    'slack.exe',
    'discord.exe',
    'skype.exe',
    'zoom.exe',
    'snippingtool.exe',
    'snipand sketch.exe',
    '7zFM.exe',
    'winrar.exe',
    'windowscamera.exe'
];

function loadProcesses(ip) {
  $("#appsTableBody").html("<tr><td colspan='3'>Loading...</td></tr>");

  $.get("http://" + ip + "/monicomlab/fix.php?action=list_processes", function(response) {
    let res = JSON.parse(response);
    let tbody = $("#appsTableBody");
    tbody.empty();

    if (res.status === "SUCCESS" && res.processes.length > 0) {
      // Filter and remove duplicates
      let filtered = res.processes.filter(p => 
        allowedApps.includes(p.name.toLowerCase())
      );
      let seen = new Set();
      filtered = filtered.filter(p => !seen.has(p.name) && seen.add(p.name));

      if (filtered.length === 0) {
        tbody.append("<tr><td colspan='3'>No allowed apps found</td></tr>");
      } else {
        filtered.forEach(function(proc) {
          tbody.append(`
            <tr>
              <td>${proc.name}</td>
              <td>${proc.pid}</td>
              <td>
                <button class="btn btn-danger btn-sm kill-process" 
                        data-name="${proc.name}" data-ip="${ip}">
                  <i class="fas fa-exit"></i> Close
                </button>
              </td>
            </tr>
          `);
        });
      }
    } else {
      tbody.append("<tr><td colspan='3'>No processes found</td></tr>");
    }
  });
}

// Open modal when clicking View Apps
$(document).on("click", ".view-apps", function() {
  let ip = $(this).data("ip");
  $("#viewAppsModal").modal("show");
  loadProcesses(ip);
});

// Kill process
$(document).on("click", ".kill-process", function() {
  let process = $(this).data("name");
  let ip = $(this).data("ip");

  $.get("http://" + ip + "/monicomlab/fix.php?action=kill_process&process=" + encodeURIComponent(process), function(response) {
    let res = JSON.parse(response);
    if (res.status === "SUCCESS") {
      Swal.fire("Success", "Killed " + process, "success");
      loadProcesses(ip);
    } else {
      Swal.fire("Success", "Killed " + process, "success");
      loadProcesses(ip);
    }
  });
});

</script>

    <script>
      $(function () {
        $("#dataTable").DataTable({
          "autoWidth": false,
          "ordering": false,  // Disable client-side sorting to maintain server-side ORDER BY
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
                      var suggestion = "";
                      if (response.candidates && response.candidates.length > 0 &&
                          response.candidates[0].content &&
                          response.candidates[0].content.parts &&
                          response.candidates[0].content.parts[0].text) {
                          
                          suggestion = response.candidates[0].content.parts[0].text;
                          
                          // Remove all types of asterisks from the suggestion text
                          suggestion = suggestion.replace(/\*+/g, ''); // Remove all asterisks (single, double, triple, etc.)
                          suggestion = suggestion.replace(/\*\*([^*]+)\*\*/g, '$1'); // Remove markdown bold formatting
                          suggestion = suggestion.replace(/\*([^*]+)\*/g, '$1'); // Remove markdown italic formatting

                          // Convert numbered list to styled HTML list with icons and better sizing
                          var formatted = "<ul class='list-group' style='font-size: 14px;'>";
                          suggestion.split(/\n/).forEach(function(line) {
                              if (line.trim() !== "") {
                                  formatted += "<li class='list-group-item d-flex align-items-start' style='font-size: 14px; padding: 12px;'>" +
                                              "<i class='fas fa-check-circle text-success mr-2 mt-1'></i>" +
                                              "<span style='line-height: 1.5;'>" + line.replace(/^\d+[\).]\s*/, '') + "</span>" +
                                              "</li>";
                              }
                          });
                          formatted += "</ul>";

                          $('#fixSuggestion').html(formatted);
                      } else {
                          $('#fixSuggestion').html("<div class='alert alert-warning' style='font-size: 14px;'><i class='fas fa-exclamation-circle'></i> No valid suggestion returned by AI.</div>");
                      }
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
    $('.view-fix').on('click', function() {
        var issueType = $(this).data('issue-type');
        var description = $(this).data('description');

        $('#fixSuggestion').html("Loading suggestions..."); 
        $('#viewFixModal').modal('show');

        // Call Gemini API
        $.ajax({
            url: "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=AIzaSyBAWR5fbU_6DmNDhy-3u3RBgiD3tIakLLw",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
                systemInstruction: {
                  parts: [
                    { text: "You are a helpful IT support assistant. When given an issue type and description, suggest troubleshooting and fix steps. Respond in a numbered list, concise but detailed, and keep the response under 500 words." }
                  ]
                },
                contents: [
                    {
                        parts: [
                          { text: "Issue Type: " + issueType + "\nDescription: " + description + "\n\nProvide step-by-step suggestions in a numbered list. Limit the response to a maximum of 500 words." }
                        ]
                    }
                ]
            }),
            success: function(response) {
                console.log("AI response raw:", response); // for debugging
                var suggestion = "";
                if (response.candidates && response.candidates.length > 0 && 
                    response.candidates[0].content && 
                    response.candidates[0].content.parts &&
                    response.candidates[0].content.parts[0].text) {
                  suggestion = response.candidates[0].content.parts[0].text;
                } else {
                  suggestion = "No valid suggestion returned by AI.";
                }
                
                // Remove asterisks and format the text properly
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
            },
            error: function(xhr) {
                console.log("AI error details:", xhr.responseText);
                $('#fixSuggestion').html("Error fetching suggestion from AI. See console for details.");
            }
        });
    });
});
</script>

<script>
$(document).ready(function() {

  // open the modal and populate fields
  $(document).on('click', '.email-btn', function() {
    var to = $(this).data('to') || '';
    var device = $(this).data('device') || '';
    var room = $(this).data('room') || ''; // <-- new
    var location = $(this).data('location') || '';
    var issue = $(this).data('issue') || '';
    var description = $(this).data('description') || '';
    var timestamp = $(this).data('timestamp') || '';
    var ticketId = $(this).data('ticket-id') || '';

    var subject = 'Device Alert: ' + (issue || 'Device Issue');
    if (room) subject += ' - ' + room; // include room in subject
    else if (location) subject += ' - ' + location;

    var body = "Hello Technician,\n\n" +
               "A device has reported a critical issue that requires your attention:\n\n" +
               "• Device Name: " + device + "\n" +
               (room ? "• Room: " + room + "\n" : "") + // include room
               (location ? "• Location: " + location + "\n" : "") +
               "• Issue: " + issue + "\n" +
               (description ? "• Description: " + description + "\n" : "") +
               "• Timestamp: " + timestamp + "\n\n" +
               "Please check and resolve the issue as soon as possible.\n\n" +
               "Thank you,\nMONICOMLAB Monitoring System\nAdmin.";

    // Populate modal fields
    $('#emailToInput').val(to);
    $('#emailSubjectInput').val(subject);
    $('#emailBody').val(body);
    $('#emailTicketId').val(ticketId);

    $('#emailModal').modal('show');
  });


  // send email via AJAX
  $('#sendEmailBtn').on('click', function() {
    var $btn = $(this);
    var to = $('#emailToInput').val();
    var subject = $('#emailSubjectInput').val();
    var body = $('#emailBody').val();
    var ticketId = $('#emailTicketId').val();

    if (!to || !subject || !body) {
      Swal.fire('Missing data', 'To / Subject / Body are required.', 'warning');
      return;
    }

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

    $.ajax({
      url: 'send_email.php',
      type: 'POST',
      dataType: 'json',
      data: {
        to: to,
        subject: subject,
        body: body,
        ticket_id: ticketId
      },
      success: function(resp) {
        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
        if (resp.success) {
          $('#emailModal').modal('hide');
          Swal.fire('Sent', resp.message || 'Email sent to technician.', 'success');
        } else {
          Swal.fire('Error', resp.message || 'Failed to send email.', 'error');
        }
      },
      error: function(xhr, status, err) {
        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
        console.error('Email AJAX error:', xhr.responseText);
        Swal.fire('Error', 'Unable to reach server. Check console for details.', 'error');
      }
    });
  });

});

</script>

<script>
// Automatic Issue Detection Functions
$(document).ready(function() {
    // Auto-refresh every 5 minutes
    setInterval(function() {
        runAutomaticScan(false); // Silent scan
    }, 300000); // 5 minutes

    // Manual scan button
    $('#runScanBtn').on('click', function() {
        runAutomaticScan(true); // Show results
    });

    function runAutomaticScan(showResults = false) {
        if (showResults) {
            $('#runScanBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Scanning...');
        }

        $.ajax({
            url: 'automatic_issue_detection.php',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                console.log('Auto scan results:', response);
                
                if (showResults && response.success) {
                    let message = response.issues_detected > 0 
                        ? `Scan completed! Found ${response.issues_detected} issues.` 
                        : 'Scan completed! No new issues found.';
                    
                    Swal.fire({
                        title: 'System Scan Complete',
                        text: message,
                        icon: response.issues_detected > 0 ? 'warning' : 'success',
                        showConfirmButton: true
                    }).then(() => {
                        if (response.issues_detected > 0) {
                            // Refresh page to show new tickets
                            location.reload();
                        }
                    });
                }
                
                // Update detection status on page
                updateDetectionStatus(response);
            },
            error: function(xhr, status, error) {
                console.error('Auto scan error:', error);
                if (showResults) {
                    Swal.fire('Error', 'Failed to run system scan. Please try again.', 'error');
                }
            },
            complete: function() {
                if (showResults) {
                    $('#runScanBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Run System Scan');
                }
            }
        });
    }

    function updateDetectionStatus(response) {
        // Update the detection status card if it exists
        const statusCard = $('.card.border-left-primary .card-body');
        if (statusCard.length > 0 && response.success) {
            let statusText = response.issues_detected > 0 
                ? `<span class='text-danger'>⚠ ${response.issues_detected} new issues automatically detected</span>`
                : `<span class='text-success'>✓ System scan completed - No new issues detected</span>`;
            
            statusText += `<small class='text-muted d-block'>Last scan: ${response.timestamp}</small>`;
            statusCard.find('.h6').html(statusText);
        }
    }

    // Initial scan on page load (silent)
    setTimeout(() => {
        runAutomaticScan(false);
    }, 2000);
});

// Stats card click animation
$(document).ready(function() {
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
        console.log('Card clicked!');
    });
    
    // Add pointer cursor hint
    $('.stats-card').css('cursor', 'pointer');
});

</script>

</body>

</html>
<?php
}
else
{
    header('location:../../index.php');
}?>