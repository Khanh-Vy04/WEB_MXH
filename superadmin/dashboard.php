<?php
session_start();
require_once '../includes/session.php';
require_once '../config/database.php';

// Check Superadmin Role
requireSuperAdmin();

$currentPage = 'dashboard';

// Basic Stats Logic
$sql_users = "SELECT COUNT(*) as count FROM users WHERE role_id = 2";
$total_users = $conn->query($sql_users)->fetch_assoc()['count'];

$sql_freelancers = "SELECT COUNT(*) as count FROM users WHERE role_id = 1";
$total_freelancers = $conn->query($sql_freelancers)->fetch_assoc()['count'];

$sql_orders = "SELECT COUNT(*) as count FROM orders";
$total_orders = $conn->query($sql_orders)->fetch_assoc()['count'];

$sql_revenue = "SELECT SUM(final_amount) as revenue FROM orders";
$total_revenue = $conn->query($sql_revenue)->fetch_assoc()['revenue'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>UniWork SuperAdmin - Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- CSS Includes (reused from complaints layout) -->
    <link href="/WEB_MXH/user/assets/logo/favicon.png" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">

    <style>
    /* Reused layout styles (keeps dashboard content intact while applying same layout) */
    .action-btn { 
        cursor: pointer; 
        font-size: 1.1rem; 
        color: #412D3B; 
        border: none; 
        background: transparent; 
    }
    .action-btn:hover { color: #412D3B; }

    .modal-header { border-bottom: 1px solid #dee2e6; background-color: #ffffff; }
    .modal-footer { border-top: 1px solid #dee2e6; background-color: #ffffff; }

    .modal-content .form-control-dark {
        background-color: #fff !important;
        color: #212529 !important;
        border: 1px solid #ced4da !important;
    }
    .modal-content .form-control-dark:focus {
        border-color: #412D3B !important;
        box-shadow: 0 0 0 0.2rem rgba(65,45,59,0.15) !important;
    }

    /* Override specific dark cards to use light background for readability */
    .container-fluid .card.bg-dark.text-white {
        background-color: #ffffff !important;
        color: #212529 !important;
        border: 1px solid #e9ecef !important;
    }
    .container-fluid .card.bg-dark.text-white .card-body {
        background-color: transparent !important;
    }
    .container-fluid .card.bg-dark.text-white .card-title,
    .container-fluid .card.bg-dark.text-white .card-text,
    .container-fluid .card.bg-dark.text-white a,
    .container-fluid .card.bg-dark.text-white i {
        color: #212529 !important;
    }
    .container-fluid .card.bg-dark.text-white a.btn-outline-primary {
        border-color: #412D3B !important;
        color: #412D3B !important;
    }
    .container-fluid .card.bg-dark.text-white .card-title i {
        color: #412D3B !important;
    }

    /* Manage button (card) — use brand instead of red on hover/focus */
    .container-fluid .card .btn-outline-primary {
        color: #ac93a4ff !important;
        border-color: #ac93a4ff !important;
        background-color: transparent !important;
    }
    .container-fluid .card .btn-outline-primary:hover,
    .container-fluid .card .btn-outline-primary:focus {
        background-color: #ac93a4ff !important;
        color: #fff !important;
        border-color: #ac93a4ff !important;
        box-shadow: none !important;
        transform: translateY(-1px);
    }

    /* ensure icons/titles inside those cards follow brand */
    .container-fluid .card .card-title .fa,
    .container-fluid .card .card-title i {
        color: #412D3B !important;
    }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <?php include 'includes/sidebar.php'; ?>


        <!-- Content Start -->
        <div class="content">
            <?php include 'includes/navbar.php'; ?>

            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-users fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Users</p>
                                <h6 class="mb-0"><?php echo $total_users; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-user-tie fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Freelancers</p>
                                <h6 class="mb-0"><?php echo $total_freelancers; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-line fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Orders</p>
                                <h6 class="mb-0"><?php echo $total_orders; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-pie fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Revenue</p>
                                <h6 class="mb-0"><?php echo number_format($total_revenue); ?>đ</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sale & Revenue End -->

            <!-- Recent Sales Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">System Overview</h6>
                    </div>
                    <p>Welcome to the SuperAdmin Dashboard.</p>
                    <div class="row mt-4">
                         <div class="col-md-4">
                            <div class="card bg-dark text-white mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary"><i class="fa fa-user-shield fa-2x mb-3"></i><br>Users Management</h5>
                                    <p class="card-text">Manage Freelancers and End Users.</p>
                                    <a href="pages/users/index.php" class="btn btn-outline-primary">Manage</a>
                                </div>
                            </div>
                         </div>
                         <div class="col-md-4">
                            <div class="card bg-dark text-white mb-3">
                                <div class="card-body text-center">
                                    <h5 class="card-title text-primary"><i class="fa fa-images fa-2x mb-3"></i><br>Banner Management</h5>
                                    <p class="card-text">Update site banners.</p>
                                    <a href="pages/banners/index.php" class="btn btn-outline-primary">Manage</a>
                                </div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
            <!-- Recent Sales End -->

            <?php include 'includes/footer.php'; ?>
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/lib/chart/chart.min.js"></script>
    <script src="/WEB_MXH/admin/lib/easing/easing.min.js"></script>
    <script src="/WEB_MXH/admin/lib/waypoints/waypoints.min.js"></script>
    <script src="/WEB_MXH/admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>

</html>
