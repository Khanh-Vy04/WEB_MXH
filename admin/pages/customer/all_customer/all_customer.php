<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Customer List - AuraDisc Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/customer/all_customer/all_customer.css" rel="stylesheet">
</head>
<body>
<?php 
    $currentPage = 'customer';
    include __DIR__.'/../../dashboard/sidebar.php';
?>
<div class="container-fluid position-relative d-flex p-0">
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="main-content">
            <div class="controls-container">
                <div class="search-bar">
                    <input type="text" placeholder="Search Order">
                </div>
                <div class="right-controls">
                    <div class="entries-dropdown">
                        <select>
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                    <button class="export-btn"><i class="fa fa-download"></i> Export</button>
                    <button class="btn-add-customer"><i class="fa fa-plus"></i> Add Customer</button>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>CUSTOMER</th>
                            <th>CUSTOMER ID</th>
                            <th>PHONE</th>
                            <th>ADDRESS</th>
                            <th>TOTAL ORDERS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">ZA</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Zeke Arton</span>
                                        <span class="customer-email">zarton8@weibo.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>#USR895280</td>
                            <td>+84 912 345 678</td>
                            <td>123 Main St, District 1, Ho Chi Minh City</td>
                            <td>539</td>
                            <td><button class="action-btn"><i class="fa fa-ellipsis-v"></i></button></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">ZR</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Zed Rawe</span>
                                        <span class="customer-email">zrawe1@va.gov</span>
                                    </div>
                                </div>
                            </td>
                            <td>#USR343593</td>
                            <td>+84 923 456 789</td>
                            <td>45 Nguyen Hue, District 1, Ho Chi Minh City</td>
                            <td>473</td>
                            <td><button class="action-btn"><i class="fa fa-ellipsis-v"></i></button></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">YL</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Yank Luddy</span>
                                        <span class="customer-email">yluddy22@fema.gov</span>
                                    </div>
                                </div>
                            </td>
                            <td>#USR586615</td>
                            <td>+84 934 567 890</td>
                            <td>78 Le Loi, District 1, Ho Chi Minh City</td>
                            <td>462</td>
                            <td><button class="action-btn"><i class="fa fa-ellipsis-v"></i></button></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">VT</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Valenka Turbill</span>
                                        <span class="customer-email">vturbill2h@nbcnews.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>#USR179914</td>
                            <td>+84 945 678 901</td>
                            <td>92 Dong Khoi, District 1, Ho Chi Minh City</td>
                            <td>550</td>
                            <td><button class="action-btn"><i class="fa fa-ellipsis-v"></i></button></td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">TV</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Thomasine Vasentsov</span>
                                        <span class="customer-email">tvasentsov1u@bloglovin.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>#USR988015</td>
                            <td>+84 956 789 012</td>
                            <td>156 Pasteur, District 3, Ho Chi Minh City</td>
                            <td>752</td>
                            <td><button class="action-btn"><i class="fa fa-ellipsis-v"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="entries-info">Showing 1 to 10 of 100 entries</div>
                <div class="pagination-controls">
                    <button class="page-btn" disabled><i class="fa fa-angle-double-left"></i></button>
                    <button class="page-btn" disabled><i class="fa fa-angle-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <span class="page-btn disabled">...</span>
                    <button class="page-btn">8</button>
                    <button class="page-btn">9</button>
                    <button class="page-btn">10</button>
                    <button class="page-btn"><i class="fa fa-angle-right"></i></button>
                    <button class="page-btn"><i class="fa fa-angle-double-right"></i></button>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
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
        <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    </div>
</div>
</body>
</html>
