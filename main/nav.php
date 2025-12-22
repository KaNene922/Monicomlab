                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <h4>Welcome Admin</h4>
                    <?php
                    include '../../connectMySql.php';

                    $sql = "SELECT * FROM detect_issue ORDER BY date DESC LIMIT 5";
                    $result = $conn->query($sql);

                    $issueCount = $result->num_rows;
                    ?>
                    <ul class="navbar-nav ml-auto">
                        <!-- Notification Bell -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter"><?= $issueCount ?></span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown" style="width: 300px;">
                                <h6 class="dropdown-header">Issue Notifications</h6>
                                <div id="alertList">
                                    <?php if ($issueCount > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <div class="dropdown-item d-flex align-items-start small">
                                                <div class="mr-2">
                                                    <i class="fas fa-exclamation-triangle text-<?= htmlspecialchars($row['color']) ?>"></i>
                                                </div>
                                                <div>
                                                    <div class="text-truncate font-weight-bold"><?= htmlspecialchars($row['name']) ?>-<?= htmlspecialchars($row['ip_address']) ?></div>
                                                    <div class="small text-gray-500"><?= htmlspecialchars($row['value']) ?></div>
                                                    <div class="small text-gray-400"><?= htmlspecialchars($row['date']) ?></div>
                                                </div>
                                            </div>
                                            <div class="dropdown-divider"></div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <span class="dropdown-item text-gray-500">No issues found.</span>
                                    <?php endif; ?>
                                </div>
                                <a class="dropdown-item text-center small text-gray-500" href="../logs/?view=issues">View All</a>
                            </div>
                        </li>


                    </ul>

                </nav>
                <!-- End of Topbar -->

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

       