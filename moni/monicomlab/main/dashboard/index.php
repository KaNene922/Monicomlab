<?php
include '../../connectMySql.php';
include '../../loginverification.php';
if(logged_in()){
$cpuUsage = shell_exec('powershell -Command "Get-Counter -Counter \'\\Processor(_Total)\\% Processor Time\' | Select-Object -ExpandProperty CounterSamples | Select-Object -ExpandProperty CookedValue"');
$cpuUsage = round(floatval(trim($cpuUsage)));

// Memory Usage
$mem = shell_exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object -Property TotalVisibleMemorySize,FreePhysicalMemory | ConvertTo-Json"');
$memData = json_decode($mem, true);
$totalMem = $memData['TotalVisibleMemorySize'] ?? 1; // prevent division by zero
$freeMem = $memData['FreePhysicalMemory'] ?? 0;
$usedMem = $totalMem - $freeMem;
$memUsage = round(($usedMem / $totalMem) * 100);

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
    <link rel="icon" type="image/x-icon" href="../../img/logo3.png" />

    <!-- Custom fonts for this template-->
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <script src="../../js/html2canvas.min.js"></script>
    <link href="../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../js/sweetalert2.all.js"></script>
    <script src="../../js/sweetalert2.css"></script>
    <script src="../../js/sweetalert2.js"></script>

<style>
  /* Dashboard card click animation */
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
  /* Ensure top charts have equal height */
  #monthlyLineChartTop, #devicesBarChartTop { height: 300px !important; max-height: 300px; width: 100% !important; }
  /* Equalize chart card heights and force canvases to fill card body */
  .card.chart-equal { height: 380px; display: flex; flex-direction: column; }
  .card.chart-equal .card-body { flex: 1 1 auto; padding: 0.5rem; }
  .card.chart-equal canvas { height: 100% !important; width: 100% !important; display: block; }
  .card.chart-equal.stats-card { cursor: pointer; }
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
                        <h1 class="h3 mb-0 text-gray-800"></h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
    <!-- CPU Usage -->
    <div class="col-xl-6 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2 stats-card">
        <div class="card-body">
          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">CPU Usage</div>
          <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $cpuUsage ?>%  GHZ</div>
        </div>
      </div>
    </div>

    <!-- RAM Usage -->
    <div class="col-xl-6 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2 stats-card">
        <div class="card-body">
          <div class="text-xs font-weight-bold text-info text-uppercase mb-1">RAM Usage</div>
          <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $memUsage ?> %</div>
        </div>
      </div>
    </div>

                    <!-- Inserted: Analytics Charts (Monthly + Devices) -->
                    <div class="col-12 mb-4">
                      <div class="row">
                        <div class="col-xl-6 col-lg-6 mb-4">
                          <div class="card shadow chart-equal stats-card">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                              <div>
                                <h6 class="m-0 font-weight-bold text-primary">Monthly Report</h6>
                                <small class="text-muted">Year: <?= date('Y') ?></small>
                                
                              </div>
                              <div class="text-right">
                                <small id="monthlyUpdateTop" class="text-muted live-last" data-timestamp="">Last: --:--:--</small>
                              </div>
                            </div>
                            <div class="card-body">
                              <canvas id="monthlyLineChartTop" style="width:100%;"></canvas>
                            </div>
                          </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 mb-4">
                          <div class="card shadow chart-equal stats-card">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                              <div>
                                <h6 class="m-0 font-weight-bold text-primary">Devices & Counts</h6>
                                
                              </div>
                              <div class="text-right">
                                <small id="devicesUpdateTop" class="text-muted live-last" data-timestamp="">Last: --:--:--</small>
                              </div>
                            </div>
                            <div class="card-body">
                              <canvas id="devicesBarChartTop" style="width:100%;"></canvas>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
  </div>

  <!-- Alerts -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Recent Alerts</h6>
      <a href="../alert">View All</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>Device</th>
              <th>Room</th>
              <th>IP Address</th>
              <th>Device Name</th>
              <th>Status</th>
              <th>Last Detected</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "SELECT cd.*, d.device, r.room_name 
                     FROM connected_devices cd 
                     LEFT JOIN device d ON cd.ip_address = d.ip_address 
                     LEFT JOIN rooms r ON d.room_id = r.room_id 
                     WHERE cd.status != 'connected' OR cd.status IS NULL
                     ORDER BY cd.detected_at DESC 
                     LIMIT 5";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $statusClass = '';
                $statusText = '';
                
                if (strtolower($row['status']) == 'connected') {
                  $statusClass = 'success';
                  $statusText = 'Connected';
                } else {
                  $statusClass = 'danger';
                  $statusText = 'Disconnected';
                }
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['device'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['room_name'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                echo "<td>" . htmlspecialchars($row['friendly_name']) . "</td>";
                echo "<td><span class='badge badge-" . $statusClass . "'>" . $statusText . "</span></td>";
                echo "<td>" . htmlspecialchars($row['detected_at'] ?? 'Never') . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='6' class='text-center text-muted'>No devices found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Logs -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">Recent Logs</h6>
      <a href="../logs">View All</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Device Name</th>
                                            <th>Room</th>
                                            <th>Issue Type</th>
                                            <th>Status</th>
                                            <th>Reported On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $condition = "";

                                        if(isset($_GET['type'])){
                                          $condition = "where status = '".$_GET['type']."'";
                                        } else {
                                          $condition = "WHERE issue_type != 'MULTIPLE DEVICES OFFLINE'";
                                        }

                                        $query = "SELECT t.*, r.room_name 
                                                 FROM ticket t 
                                                 LEFT JOIN device d ON (t.device_name = d.device 
                                                                      OR t.device_name = d.name
                                                                      OR UPPER(t.device_name) = UPPER(d.device)
                                                                      OR UPPER(t.device_name) = UPPER(d.name))
                                                 LEFT JOIN rooms r ON d.room_id = r.room_id 
                                                 $condition ORDER BY t.date DESC LIMIT 5";
                                        $result = $conn->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr role='row'>";

                                            $color = "";
                                            if($row['status']=='PENDING'){
                                                $color = "danger";
                                            }
                                            else if($row['status']=='UNRESOLVED'){
                                                $color = "warning";
                                            }
                                            else if($row['status']=='RESOLVED'){
                                                $color = "success";
                                            }
                                            echo "<td>" . strtoupper($row['device_name']) . "</td>";
                                            echo "<td>" . strtoupper($row['room_name'] ?? 'N/A') . "</td>";
                                            echo "<td>" . strtoupper($row['issue_type']) . "</td>";
                                            echo "<td><span class='badge badge-".$color."'>" . strtoupper($row['status']) . "</span></td>";
                                            echo "<td>" . strtoupper($row['date']) . "</td>";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>

    document.getElementById('confirmButton').addEventListener('click', function () {
      Swal.fire({
        title: 'Are you sure?',
        text: "This will update the status to 0!",
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: 'update_status.php',
            type: 'POST',
            data: { status: 0 }, 
            success: function (response) {
              Swal.fire(
                'Updated!',
                'Wait for the data!',
                'success'
              );
            },
            error: function () {
              Swal.fire(
                'Error!',
                'There was an error updating the status.',
                'error'
              );
            }
          });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
          Swal.fire(
            'Cancelled',
            'No changes were made.',
            'error'
          );
        }
      });
    });

    // Dashboard stats card click animation
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
        console.log('Dashboard card clicked!');
    });
    
    // Add pointer cursor hint
    $('.stats-card').css('cursor', 'pointer');
</script>

<script>
// Top charts near Recent Alerts
(function(){
  if(!document.getElementById('monthlyLineChartTop')) return;
  const mCtx = document.getElementById('monthlyLineChartTop').getContext('2d');
  const dCtx = document.getElementById('devicesBarChartTop').getContext('2d');

  if(window.Chart && window.ChartDataLabels){ try{ Chart.register(window.ChartDataLabels); }catch(e){} }

  const gradient = (ctx) => { const g = ctx.createLinearGradient(0,0,0,300); g.addColorStop(0,'rgba(78,115,223,0.25)'); g.addColorStop(1,'rgba(78,115,223,0)'); return g; };

  const topMonthly = new Chart(mCtx, {
    type:'line', data:{ labels:[], datasets:[{ label:'Issues (0)', data:[], backgroundColor:gradient(mCtx), borderColor:'rgba(78,115,223,1)', fill:true, tension:0.35, pointRadius:5 }]},
  options:{ responsive:true, maintainAspectRatio:false, layout:{ padding:{ left:16, right:16, top:22, bottom:16 } }, plugins:{ legend:{display:true, position:'top', labels:{ boxWidth:12, font:{ size:12 } } } }, scales:{ x:{ grid:{display:false}, ticks:{ font:{ size:12 } } }, y:{ min:0, beginAtZero:true, ticks:{ stepSize:1, callback: function(v){ return (Math.abs(v-Math.round(v))<1e-9) ? String(Math.round(v)) : ''; }, font:{ size:12 } }, grid:{ color:'rgba(200,200,200,0.08)' } } } }
  });

  const topDevices = new Chart(dCtx, {
    type:'bar', data:{ labels:[], datasets:[{ label:'Devices (0)', data:[], backgroundColor:'rgba(54,185,204,0.95)', borderRadius:10, barThickness:20 }]},
  options:{ indexAxis:'y', responsive:true, maintainAspectRatio:false, layout:{ padding:{ left:16, right:16, top:22, bottom:16 } }, plugins:{ legend:{display:true, position:'top', labels:{ boxWidth:12, font:{ size:12 } } }, datalabels:{ formatter:v=>Math.round(v) } }, scales:{ x:{ min:0, beginAtZero:true, ticks:{ stepSize:1, callback:function(v){ return (Math.abs(v-Math.round(v))<1e-9) ? String(Math.round(v)) : ''; }, font:{ size:12 } }, grid:{ color:'rgba(200,200,200,0.08)' } }, y:{ grid:{display:false}, ticks:{ font:{ size:12 } } } } }
  });

  async function fetchTop(){
    try{
      const year = new Date().getFullYear();
      const res = await fetch('fetch_dashboard_stats.php?year=' + year); if(!res.ok) throw new Error('fetch failed'); const json = await res.json();
      const months = json.monthly.map(m=>m.month); const counts = json.monthly.map(m=>parseInt(m.count,10)||0);
    topMonthly.data.labels = months; topMonthly.data.datasets[0].data = counts;
  const maxM = counts.length?Math.max(...counts):1; const stepM = maxM<=10?1:Math.ceil(maxM/5);
  topMonthly.options.scales.y.min = 0; topMonthly.options.scales.y.max = Math.ceil(maxM/stepM)*stepM; topMonthly.options.scales.y.ticks.stepSize = stepM; topMonthly.options.scales.y.ticks.callback = function(v){ return (Math.abs(v-Math.round(v))<1e-9) ? String(Math.round(v)) : ''; };
  // set total issues (used for legend)
  const totalIssues = counts.reduce((s,n)=>s+(Number.isFinite(n)?n:0),0);
  // update legend label to include total
  if(topMonthly.data && topMonthly.data.datasets && topMonthly.data.datasets[0]){
    topMonthly.data.datasets[0].label = 'Issues (' + totalIssues + ')';
  }
  topMonthly.update();
  // seed the live timestamp using server time (or client time if server not provided)
  const nowIso = new Date().toISOString();
  const monthlyEl = document.getElementById('monthlyUpdateTop');
  if(monthlyEl) { monthlyEl.dataset.timestamp = nowIso; if(window.__setLiveLast) window.__setLiveLast('monthlyUpdateTop', nowIso); }

      const types = json.devices.map(d=>d.type); const dcounts = json.devices.map(d=>parseInt(d.count,10)||0);
    topDevices.data.labels = types; topDevices.data.datasets[0].data = dcounts;
  const maxD = dcounts.length?Math.max(...dcounts):1; const stepD = maxD<=10?1:Math.ceil(maxD/5);
  topDevices.options.scales.x.min = 0; topDevices.options.scales.x.max = Math.ceil(maxD/stepD)*stepD; topDevices.options.scales.x.ticks.stepSize = stepD; topDevices.options.scales.x.ticks.callback = function(v){ return (Math.abs(v-Math.round(v))<1e-9) ? String(Math.round(v)) : ''; };
  // set total devices (sum of counts, used for legend)
  const totalDevices = dcounts.reduce((s,n)=>s+(Number.isFinite(n)?n:0),0);
  if(topDevices.data && topDevices.data.datasets && topDevices.data.datasets[0]){
    topDevices.data.datasets[0].label = 'Devices (' + totalDevices + ')';
  }
  topDevices.update();
  const devicesEl = document.getElementById('devicesUpdateTop');
  if(devicesEl) { devicesEl.dataset.timestamp = nowIso; if(window.__setLiveLast) window.__setLiveLast('devicesUpdateTop', nowIso); }
    }catch(err){ console.error(err); }
  }

  fetchTop(); setInterval(fetchTop,30000);
})();
</script>

<script>
// Live 'Last:' timestamp updater for elements with class .live-last
(function(){
  const els = () => Array.from(document.querySelectorAll('.live-last'));
  // Initialize display from data-timestamp or leave as --:--:--
  function formatTime(d){ return d.toLocaleTimeString(); }

  // Keep internal Date objects per element
  const state = new Map();

  function seedFromDataAttr(el){
    const ts = el.dataset.timestamp;
    if(!ts) return null;
    const d = new Date(ts);
    if(isNaN(d.getTime())) return null;
    return d;
  }

  function refreshStates(){
    els().forEach(el => {
      if(!state.has(el)){
        const d = seedFromDataAttr(el) || new Date();
        state.set(el, d);
        el.innerText = 'Last: ' + formatTime(d);
      }
    });
  }

  // called by fetchTop to update/replace the seed for an element
  window.__setLiveLast = function(id, isoString){
    const el = document.getElementById(id);
    if(!el) return;
    el.dataset.timestamp = isoString;
    const d = new Date(isoString);
    if(!isNaN(d.getTime())){
      state.set(el, d);
      el.innerText = 'Last: ' + formatTime(d);
    }
  };

  // tick every second and update elements
  setInterval(()=>{
    // increment each stored date by 1 second
    for(const [el, d] of state.entries()){
      d.setSeconds(d.getSeconds() + 1);
      el.innerText = 'Last: ' + formatTime(d);
    }
    // also pick up any new elements added later
    els().forEach(el=>{ if(!state.has(el)){ const d = seedFromDataAttr(el) || new Date(); state.set(el,d); el.innerText='Last: '+formatTime(d); } });
  }, 1000);

  // initial pass
  refreshStates();
})();
</script>

</body>

</html>
<?php
}
else
{
    header('location:../../index.php');
}?>