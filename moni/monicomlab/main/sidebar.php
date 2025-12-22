 <!-- Sidebar -->
        <style>
            .bg-gradient-custom {
                background: linear-gradient(135deg, #081B3CF7, #081B3CF7);
            }
            
            /* Fixed sidebar positioning */
            #accordionSidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                height: 1400vh;
                overflow-y: auto;
                z-index: 1000;
                width: 224px;
            }
            
            /* Adjust main content wrapper */
            #wrapper {
                margin-left: 224px !important;
            }
            
            /* Custom scrollbar styling for sidebar */
            #accordionSidebar::-webkit-scrollbar {
                width: 4px;
            }
            
            #accordionSidebar::-webkit-scrollbar-track {
                background: rgba(255, 255, 255, 0.1);
            }
            
            #accordionSidebar::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.3);
                border-radius: 3px;
            }
            
            /* Mobile responsiveness */
            @media (max-width: 768px) {
                #wrapper {
                    margin-left: 0 !important;
                }
                #accordionSidebar {
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                }
                body.sidebar-toggled #accordionSidebar {
                    transform: translateX(0);
                }
            }


        </style>
        <ul class="navbar-nav bg-gradient-custom sidebar sidebar-dark accordion " id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../profile/">
            <?php if($_SESSION['image'] == ''){ ?>
            <i class="fas fa-user-circle fa-5x mt-5"></i>
            <?php } else { ?>
            <img src="../../image/<?= $_SESSION['image']; ?>" style="width:130px; height:100px; border-radius:50%; object-fit:cover;" class="mt-5">
            <?php } ?>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="../dashboard/">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Dashboard</span></a>
            </li>


            <hr class="sidebar-divider">

            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link" href="../device_monitoring/">
                    <i class="fas fa-fw fa-laptop"></i>
                    <span>Device Monitoring</span></a>
            </li>
            <hr class="sidebar-divider">


            <!-- Nav Item - Pages Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link" href="../troubleshoot/">
                    <i class="fas fa-fw fa-cubes"></i>
                    <span>Troubleshooting</span></a>
            </li>
            <hr class="sidebar-divider">


            <li class="nav-item">
                <a class="nav-link" href="../logs/">
                    <i class="fas fa-fw fa-history"></i>
                    <span>Logs</span></a>
            </li>
            <hr class="sidebar-divider">

            <li class="nav-item">
                <a class="nav-link" href="../logs?view=issues">
                    <i class="fas fa-fw fa-bell"></i>
                    <span>Alert</span></a>
            </li>
            <hr class="sidebar-divider">

            <li class="nav-item">
                <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </li>
            <hr class="sidebar-divider">

        </ul>
        <!-- End of Sidebar -->