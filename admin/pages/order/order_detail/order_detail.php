<?php
$currentPage = 'orderdetail';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Detail</title>
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="order_detail.css" />
    <style>
        .bg-white-box {
            background: #fff !important;
            color: #222;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .section-title {
            color: #222 !important;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Order details</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="background: #fff; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th scope="col" class="text-center" style="width:40px;"><input type="checkbox"></th>
                                        <th scope="col">PRODUCTS</th>
                                        <th scope="col" class="text-center">PRICE</th>
                                        <th scope="col" class="text-center">QTY</th>
                                        <th scope="col" class="text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center"><input type="checkbox"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png" alt="Abbey Road" width="40" class="rounded me-2">
                                                <div>
                                                    <div style="font-weight:600;">The Beatles - Abbey Road</div>
                                                    <small class="text-muted">Vinyl, 180g, 2019 Remaster</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">650,000₫</td>
                                        <td class="text-center">1</td>
                                        <td class="text-center">650,000₫</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><input type="checkbox"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png" alt="Kind of Blue" width="40" class="rounded me-2">
                                                <div>
                                                    <div style="font-weight:600;">Miles Davis - Kind of Blue</div>
                                                    <small class="text-muted">Vinyl, 180g, Jazz Classic</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">720,000₫</td>
                                        <td class="text-center">2</td>
                                        <td class="text-center">1,440,000₫</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><input type="checkbox"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png" alt="The Wall" width="40" class="rounded me-2">
                                                <div>
                                                    <div style="font-weight:600;">Pink Floyd - The Wall</div>
                                                    <small class="text-muted">Vinyl, 2LP, Gatefold</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">800,000₫</td>
                                        <td class="text-center">1</td>
                                        <td class="text-center">800,000₫</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column align-items-end mt-4">
                            <div>Subtotal: <b>2,890,000₫</b></div>
                            <div>Discount: <b>0₫</b></div>
                            <div>Tax: <b>20,000₫</b></div>
                            <div>Total: <b>2,910,000₫</b></div>
                        </div>
                    </div>
                    <!-- Shipping Activity -->
                    <div class="bg-white-box rounded p-4">
                        <h5 class="mb-3 section-title">Shipping activity</h5>
                        <ul class="timeline">
                            <li class="timeline-item active">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Order was placed (Order ID: #10001)</b>
                                    <div>Your order for vinyl records has been placed successfully</div>
                                    <small class="text-muted">Monday 09:00 AM</small>
                                </div>
                            </li>
                            <li class="timeline-item active">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Pick-up</b>
                                    <div>Pick-up scheduled with courier</div>
                                    <small class="text-muted">Monday 15:00 PM</small>
                                </div>
                            </li>
                            <li class="timeline-item active">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Dispatched</b>
                                    <div>Records picked up by courier</div>
                                    <small class="text-muted">Tuesday 10:00 AM</small>
                                </div>
                            </li>
                            <li class="timeline-item active">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Package arrived</b>
                                    <div>Arrived at Ho Chi Minh City sorting center</div>
                                    <small class="text-muted">Tuesday 18:00 PM</small>
                                </div>
                            </li>
                            <li class="timeline-item active">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Dispatched for delivery</b>
                                    <div>Out for delivery to customer</div>
                                    <small class="text-muted">Wednesday 08:00 AM</small>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <span class="timeline-point"></span>
                                <div class="timeline-content">
                                    <b>Delivery</b>
                                    <div>Expected delivery by today</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- Customer & Address Details -->
                <div class="col-lg-4">
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Customer details</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="avatar" class="rounded-circle me-2" width="40">
                            <div>
                                <div><b>Nguyen Van A</b></div>
                                <div class="text-muted small">Customer ID: #20001</div>
                                <div class="text-success small"><i class="fa fa-check-circle"></i> 5 Orders</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="fw-bold">Contact info</div>
                            <div>Email: <a href="mailto:nguyenvana@gmail.com">nguyenvana@gmail.com</a></div>
                            <div>Mobile: <a href="tel:+84901234567">+84 901 234 567</a></div>
                        </div>
                    </div>
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Shipping address</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div>123 Le Loi, District 1<br>Ho Chi Minh City<br>Vietnam</div>
                    </div>
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Billing address</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div>123 Le Loi, District 1<br>Ho Chi Minh City<br>Vietnam</div>
                        <div class="mt-2">
                            <div class="fw-bold">Visa</div>
                            <div>Card Number: ******1234</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html>
