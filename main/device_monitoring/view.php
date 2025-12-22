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

    <title>MONICOMLAB </title>
    <link rel="icon" type="image/x-icon" href="../../img/logo1.png"/>

    <!-- Custom fonts for this template-->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">


    <!-- Custom styles for this template-->
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 5px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .sync-animation {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .last-updated {
            font-size: 14px;
            color: #6c757d;
        }
        
        .no-data-message {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
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
                            <h1 class="h3 mb-0 text-gray-800">Device Monitoring</h1>
                            <div>
                                <a href="index.php" class=" btn btn-sm btn-danger shadow-sm"><i class="fas fa-arrow-left"></i> Back</a>
                                <button id="refresh-toggle" class="btn btn-sm btn-info shadow-sm"><i class="fas fa-sync-alt"></i> Auto Refresh: ON</button>
                            </div>
                        </div>

                        <div class="row">

                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-primary">View apps</h6>
                                        <div class="small text-muted"><?= $_GET['device'].' - '. $_GET['name'].' '.$_GET['ip_address']?></div>
                                        <div class="small text-muted">Last Checked: <?=$_GET['date']?></div>
                                    </div>
                                    <div class="last-updated" id="last-update-time">
                                        <i class="fas fa-sync-alt"></i> Last updated: --:--:--
                                    </div>
                                </div>
                                <div class="card-body position-relative">
                                    <div id="loading-overlay" class="loading-overlay">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-dark">Loading application data...</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Application</th>
                                                    <th>Window Title</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="apps-table-body">
                                                <!-- Data will be loaded via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-primary">Connected Devices</h6>
                                        <div class="small text-muted"><?= $_GET['ip_address'] ?></div>
                                    </div>
                                    <div class="last-updated" id="devices-last-update-time">
                                        <i class="fas fa-sync-alt"></i> Last updated: --:--:--
                                    </div>
                                </div>
                                <div class="card-body position-relative">
                                    <div id="devices-loading-overlay" class="loading-overlay">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-dark">Loading connected devices...</p>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Device Class</th>
                                                    <th>Friendly Name</th>
                                                    <th>Status</th>
                                                    <th>Last Detected</th>
                                                </tr>
                                            </thead>
                                            <tbody id="devices-table-body">
                                                <!-- Data will be loaded via JavaScript -->
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
      // Auto-refresh functionality
      let refreshInterval;
      const refreshRate = 5000; // 5 seconds
      let autoRefreshEnabled = true;
      let hasData = false;
      
      // Function to update the last update time
      function updateLastUpdateTime() {
          const now = new Date();
          const timeString = now.toLocaleTimeString();
          $('#last-update-time').html('<i class="fas fa-sync-alt"></i> Last updated: ' + timeString);
      }
      
      // Function to show loading animation
      function showLoading() {
          $('#loading-overlay').show();
          $('#last-update-time').html('<i class="fas fa-sync-alt sync-animation"></i> Loading...');
          // Add animation to refresh button when loading
          $('#refresh-toggle i').addClass('sync-animation');
      }
      
      // Function to hide loading animation
      function hideLoading() {
          // Only hide loading if we have data
          if (hasData) {
              $('#loading-overlay').hide();
              $('#refresh-toggle i').removeClass('sync-animation');
          }
      }
      
      // Function to fetch and update application data
      function fetchApplicationData() {
          showLoading();
          
          $.ajax({
              url: 'fetch_application_data.php?ip_address=<?= $_GET["ip_address"] ?>',
              type: 'GET',
              dataType: 'json',
              success: function(data) {
                  updateApplicationsDisplay(data);
                  updateLastUpdateTime();
                  hideLoading();
              },
              error: function(xhr, status, error) {
                  console.error('Error fetching application data:', error);
                  $('#last-update-time').html('<i class="fas fa-exclamation-triangle text-danger"></i> Update failed');
                  
                  // Show error message in table but keep loading visible
                  $('#apps-table-body').html('<tr><td colspan="3" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load data. Please try again.</td></tr>');
                  
                  // Keep loading visible since we don't have valid data
                  hasData = false;
                  $('#refresh-toggle i').removeClass('sync-animation');
              }
          });
      }
      
      // Function to update the applications display
      function updateApplicationsDisplay(apps) {
          let html = '';
          
          if (apps && apps.length > 0) {
              hasData = true;
              apps.forEach(app => {
                  const status = app.status.toLowerCase();
                  const iconClass = (status == "running") ? "text-success" : "text-danger";
                  
                  html += `<tr>`;
                  html += `<td>${app.application}</td>`;
                  html += `<td>${app.window_title}</td>`;
                  html += `<td><span class="${iconClass}"><i class="fas fa-circle"></i> ${app.status}</span></td>`;
                  html += `</tr>`;
              });
          } else {
              hasData = false;
              html = `<tr><td colspan="3" class="text-center text-muted"><i class="fas fa-info-circle"></i> No applications found</td></tr>`;
          }
          
          $('#apps-table-body').html(html);
      }

          // Function to update the last update time for devices
function updateDevicesLastUpdateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    $('#devices-last-update-time').html('<i class="fas fa-sync-alt"></i> Last updated: ' + timeString);
}

// Fetch devices data
function fetchDevicesData() {
    $('#devices-loading-overlay').show();
    $('#devices-last-update-time').html('<i class="fas fa-sync-alt sync-animation"></i> Loading...');

    $.ajax({
        url: 'fetch_devices_data.php?ip_address=<?= $_GET["ip_address"] ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            updateDevicesDisplay(data);
            updateDevicesLastUpdateTime();
            $('#devices-loading-overlay').hide();
        },
        error: function(xhr, status, error) {
            console.error('Error fetching devices data:', error);
            $('#devices-table-body').html('<tr><td colspan="4" class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Failed to load devices</td></tr>');
            $('#devices-loading-overlay').hide();
            $('#devices-last-update-time').html('<i class="fas fa-exclamation-triangle text-danger"></i> Update failed');
        }
    });
}

// Update devices display
function updateDevicesDisplay(devices) {
    let html = '';

    if (devices && devices.length > 0) {
        devices.forEach(dev => {
            const status = dev.status.toLowerCase();
            const iconClass = (status == "connected") ? "text-success" :
                              (status == "disconnected" || status == "removed") ? "text-danger" : "text-muted";

            html += `<tr>`;
            html += `<td>${dev.device_class}</td>`;
            html += `<td>${dev.friendly_name}</td>`;
            html += `<td><span class="${iconClass}"><i class="fas fa-circle"></i> ${dev.status}</span></td>`;
            html += `<td>${dev.detected_at}</td>`;
            html += `</tr>`;
        });
    } else {
        html = `<tr><td colspan="4" class="text-center text-muted"><i class="fas fa-info-circle"></i> No devices found</td></tr>`;
    }

    $('#devices-table-body').html(html);
}
      
      // Function to start auto-refresh
      function startAutoRefresh() {
          clearInterval(refreshInterval);
          refreshInterval = setInterval(fetchApplicationData, refreshRate);
          $('#refresh-toggle').html('<i class="fas fa-sync-alt"></i> Auto Refresh: ON');
          $('#refresh-toggle').removeClass('btn-secondary').addClass('btn-info');
          autoRefreshEnabled = true;
      }
      
      // Function to stop auto-refresh
      function stopAutoRefresh() {
          clearInterval(refreshInterval);
          $('#refresh-toggle').html('<i class="fas fa-sync-alt"></i> Auto Refresh: OFF');
          $('#refresh-toggle').removeClass('btn-info').addClass('btn-secondary');
          $('#refresh-toggle i').removeClass('sync-animation');
          autoRefreshEnabled = false;
      }
      
      // Manual refresh function
      function manualRefresh() {
          showLoading();
          fetchApplicationData();
      }
      
      // Document ready
      $(document).ready(function() {
          // Initial data load
          fetchApplicationData();
          fetchDevicesData();
          
          // Set up auto-refresh
          startAutoRefresh();

        // Refresh devices on the same interval as apps
        refreshInterval = setInterval(() => {
            fetchApplicationData();
            fetchDevicesData();
        }, refreshRate);
          
          // Toggle auto-refresh
          $('#refresh-toggle').click(function() {
              if (autoRefreshEnabled) {
                  stopAutoRefresh();
              } else {
                  startAutoRefresh();
                  // Trigger immediate refresh when turning on
                  manualRefresh();
              }
          });
          
          // Allow manual refresh by clicking on the last-updated text
          $('#last-update-time').click(function() {
              manualRefresh();
          });
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