<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Refund List</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link href="refund_list.css" rel="stylesheet">
</head>
<body>
<?php 
    $currentPage = 'refund';
    include __DIR__.'/../../dashboard/sidebar.php';
?>
<div class="container-fluid position-relative d-flex p-0">
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="main-content">
            <div class="controls-container">
                <div class="search-bar">
                    <input type="text" placeholder="Search Refund">
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
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>REFUND ID</th>
                            <th>ORDER ID</th>
                            <th>CUSTOMER</th>
                            <th>PRODUCT(S)</th>
                            <th>TOTAL REFUND</th>
                            <th>STATUS</th>
                            <th>REFUND AT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>#REF001</td>
                            <td>#ORD123</td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">NT</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Nguyen Thanh</span>
                                        <span class="customer-email">thanh.nguyen@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>CD Album "The Best of ABBA"</td>
                            <td>350,000₫</td>
                            <td><span class="status-badge completed">Completed</span></td>
                            <td>2024-03-15 14:30</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item"><i class="fa fa-eye"></i> View Details</a>
                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Print</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>#REF002</td>
                            <td>#ORD124</td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">TL</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Tran Linh</span>
                                        <span class="customer-email">linh.tran@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>CD Album "Greatest Hits - Queen"</td>
                            <td>450,000₫</td>
                            <td><span class="status-badge pending">Pending</span></td>
                            <td>2024-03-15 15:45</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item"><i class="fa fa-eye"></i> View Details</a>
                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Print</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>#REF003</td>
                            <td>#ORD125</td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">PH</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Pham Hoang</span>
                                        <span class="customer-email">hoang.pham@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>CD Album "Thriller - Michael Jackson"</td>
                            <td>400,000₫</td>
                            <td><span class="status-badge processing">Processing</span></td>
                            <td>2024-03-15 16:20</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item"><i class="fa fa-eye"></i> View Details</a>
                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Print</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>#REF004</td>
                            <td>#ORD126</td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">LV</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Le Van</span>
                                        <span class="customer-email">van.le@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>CD Album "Back in Black - AC/DC"</td>
                            <td>380,000₫</td>
                            <td><span class="status-badge rejected">Rejected</span></td>
                            <td>2024-03-15 17:10</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item"><i class="fa fa-eye"></i> View Details</a>
                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Print</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>#REF005</td>
                            <td>#ORD127</td>
                            <td>
                                <div class="customer-cell">
                                    <div class="customer-avatar">NH</div>
                                    <div class="customer-info">
                                        <span class="customer-name">Nguyen Hoa</span>
                                        <span class="customer-email">hoa.nguyen@email.com</span>
                                    </div>
                                </div>
                            </td>
                            <td>CD Album "The Dark Side of the Moon - Pink Floyd"</td>
                            <td>420,000₫</td>
                            <td><span class="status-badge completed">Completed</span></td>
                            <td>2024-03-15 18:05</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn"><i class="fa fa-ellipsis-v"></i></button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item"><i class="fa fa-eye"></i> View Details</a>
                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Print</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="entries-info">Showing 1 to 5 of 5 entries</div>
                <div class="pagination-controls">
                    <button class="page-btn" disabled><i class="fa fa-angle-double-left"></i></button>
                    <button class="page-btn" disabled><i class="fa fa-angle-left"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn" disabled><i class="fa fa-angle-right"></i></button>
                    <button class="page-btn" disabled><i class="fa fa-angle-double-right"></i></button>
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