<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Kiểm tra đăng nhập cho giỏ hàng
$is_logged_in = isLoggedIn();
$current_user = getCurrentUser();
$user_id = $current_user['user_id'] ?? 0;
$cart_count = 0;

// Lấy cart count nếu đã đăng nhập
if ($is_logged_in) {
function getCartItemCount() {
    global $conn, $user_id;
    $sql = "SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}
$cart_count = getCartItemCount();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Giỏ hàng - AuraDisc</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="assets/logo/favicon.png"/>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    
    <!-- Linear Icons -->
    <link rel="stylesheet" href="assets/css/linearicons.css">
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Bootsnav -->
    <link rel="stylesheet" href="assets/css/bootsnav.css">
    
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">

    <style>
        /* Cart Page Styles */
        .cart-section {
            padding: 80px 0 60px;
            background: #f8f9fa;
            min-height: 70vh;
        }

        .cart-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        .cart-header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .cart-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .cart-content {
            padding: 30px;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th {
            background: #f8f9fa;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .cart-table td {
            padding: 25px 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
        }

        .cart-item-info {
            padding-left: 15px;
        }

        .cart-item-name {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .cart-item-type {
            color: #666;
            font-size: 0.9rem;
            text-transform: capitalize;
        }

        .cart-item-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ff6b35;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            width: 35px;
            height: 35px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background: #ff6b35;
            color: white;
            border-color: #ff6b35;
        }

        .quantity-btn:disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px;
            font-weight: 600;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .cart-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-top: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            font-weight: 700;
            font-size: 1.3rem;
            color: #ff6b35;
        }

        .cart-actions {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-continue {
            background: #6c757d;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-continue:hover {
            background: #5a6268;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .btn-checkout {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-checkout:disabled,
        .btn-checkout.disabled {
            background: #ccc !important;
            cursor: not-allowed !important;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Checkbox Styles */
        #select-all-checkbox,
        .item-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ff6b35;
        }

        #select-all-checkbox:indeterminate {
            opacity: 0.7;
        }

        .selected-row {
            background: rgba(255, 107, 53, 0.05);
        }

        .empty-cart {
            text-align: center;
            padding: 80px 30px;
        }

        .empty-cart i {
            font-size: 120px;
            color: #dee2e6;
            margin-bottom: 30px;
            display: block;
        }

        .empty-cart h3 {
            color: #666;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .empty-cart p {
            color: #999;
            margin-bottom: 40px;
            font-size: 1.1rem;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-spinner {
            text-align: center;
            color: #ff6b35;
        }

        .loading-spinner i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        /* Toast styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transform: translateX(400px);
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .toast-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .toast-content i {
            font-size: 1.2rem;
        }
        
        .toast-message {
            flex: 1;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .cart-table {
                font-size: 14px;
            }
            
            .cart-table th,
            .cart-table td {
                padding: 15px 10px;
            }
            
            .cart-item-image {
                width: 60px;
                height: 60px;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .btn-continue,
            .btn-checkout {
                width: 100%;
                justify-content: center;
            }
            
            .cart-title {
                font-size: 2rem;
            }
        }
        
        /* Cart Page Navigation Override - Force Cart Dropdown to Show */
        .cart-dropdown {
            position: relative !important;
        }
        
        .cart-dropdown .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            z-index: 99999 !important;
            display: none !important;
            min-width: 350px !important;
            background: white !important;
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
            margin-top: 5px !important;
        }
        
        .cart-dropdown:hover .dropdown-menu,
        .cart-dropdown.show .dropdown-menu {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure cart dropdown appears above all cart page elements */
        .cart-dropdown .dropdown-menu * {
            z-index: inherit !important;
        }
        
        /* Voucher Section Styles */
        .voucher-section {
            border-top: 1px solid #dee2e6 !important;
            padding-top: 15px !important;
            margin-top: 10px !important;
        }
        
        .voucher-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-voucher {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-voucher:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }
        
        .discount-row {
            color: #dc3545 !important;
            font-weight: 600 !important;
        }
        
        /* Voucher Modal Styles */
        .voucher-section-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ff6b35;
        }
        
        .voucher-list {
            display: grid;
            gap: 15px;
        }
        
        .voucher-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .voucher-card.available {
            border-color: #28a745;
            background: #f8fff9;
        }
        
        .voucher-card.available:hover {
            border-color: #ff6b35;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
            transform: translateY(-2px);
        }
        
        .voucher-card.unavailable {
            border-color: #dc3545;
            background: #fff5f5;
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .voucher-card.selected {
            border-color: #ff6b35;
            background: #fff4f0;
        }
        
        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .voucher-code {
            font-weight: 700;
            font-size: 18px;
            color: #ff6b35;
        }
        
        .voucher-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .voucher-status.available {
            background: #d4edda;
            color: #155724;
        }

        /* Checkout Popup Styles */
        .checkout-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .checkout-popup.show {
            display: flex;
        }

        .checkout-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.3s ease;
        }

        .checkout-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }

        .checkout-header h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .checkout-body {
            padding: 30px;
        }

        .checkout-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .checkout-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .checkout-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkout-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .checkout-item:last-child {
            margin-bottom: 0;
        }

        .checkout-item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .checkout-item-info {
            flex: 1;
        }

        .checkout-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .checkout-item-details {
            color: #666;
            font-size: 0.9rem;
        }

        .checkout-item-price {
            font-weight: 600;
            color: #ff6b35;
            font-size: 1.1rem;
        }

        .checkout-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .voucher-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
        }

        .voucher-code-display {
            font-weight: 600;
            color: #0066cc;
            font-size: 1.1rem;
        }

        .checkout-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .checkout-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-confirm-payment {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-confirm-payment:hover {
            background: linear-gradient(135deg, #218838, #1ea087);
            transform: translateY(-2px);
        }

        .btn-cancel-payment {
            background: #6c757d;
            color: white;
        }

        .btn-cancel-payment:hover {
            background: #545b62;
        }

        .payment-processing {
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #28a745;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .voucher-status.expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .voucher-status.used {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .voucher-discount {
            font-size: 24px;
            font-weight: 700;
            color: #dc3545;
            margin: 10px 0;
        }
        
        .voucher-details {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .voucher-expiry {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
        }

        /* Payment Method Selection Styles */
        .payment-method-options {
            display: grid;
            gap: 15px;
            margin-top: 15px;
        }

        .payment-method-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            background: white;
        }

        .payment-method-card:hover {
            border-color: #ff6b35;
            background: #fff8f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
        }

        .payment-method-card.selected {
            border-color: #ff6b35;
            background: #fff8f6;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }

        .payment-method-card.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f8f9fa;
        }

        .payment-method-card.disabled:hover {
            border-color: #e9ecef;
            background: #f8f9fa;
            transform: none;
            box-shadow: none;
        }

        .payment-method-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .payment-method-card[data-method="vnpay"] .payment-method-icon {
            background: linear-gradient(135deg, #1e88e5, #1976d2);
        }

        .payment-method-card[data-method="cash"] .payment-method-icon {
            background: linear-gradient(135deg, #43a047, #388e3c);
        }

        .payment-method-info {
            flex: 1;
        }

        .payment-method-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .payment-method-desc {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 3px;
        }

        .payment-method-balance {
            color: #ff6b35;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 8px;
        }

        .payment-method-check {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #ff6b35;
            font-size: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .payment-method-card.selected .payment-method-check {
            opacity: 1;
        }

        .wallet-insufficient {
            color: #dc3545 !important;
        }

        .wallet-sufficient {
            color: #28a745 !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php 
    $base_path = '/WEB_MXH/user/';
    include 'includes/navigation.php'; 
    ?>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <div class="cart-container">
                <div class="cart-header">
                    <h1 class="cart-title">
                        <i class="fa fa-shopping-cart"></i>
                        Giỏ hàng của bạn
                    </h1>
                </div>

                <div class="cart-content">
                    <div id="loading-overlay" class="loading-overlay" style="display: none;">
                        <div class="loading-spinner">
                            <i class="fa fa-spinner fa-spin"></i>
                            <div>Đang tải giỏ hàng...</div>
                        </div>
                    </div>

                    <?php if (!$is_logged_in): ?>
                    <div class="empty-cart">
                        <i class="fa fa-lock"></i>
                        <h3>Cần đăng nhập</h3>
                        <p>Vui lòng đăng nhập để xem và quản lý giỏ hàng của bạn</p>
                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <a href="/WEB_MXH/index.php" class="btn-continue">
                                <i class="fa fa-sign-in"></i>
                                Đăng nhập
                            </a>
                            <a href="index.php" class="btn-checkout">
                                <i class="fa fa-arrow-left"></i>
                                Về trang chủ
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <div id="cart-items-container">
                        <!-- Cart items will be loaded here -->
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Voucher Modal -->
    <div class="modal fade" id="voucherModal" tabindex="-1" role="dialog" aria-labelledby="voucherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="voucherModalLabel">
                        <i class="fa fa-ticket"></i> Chọn voucher giảm giá
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="voucher-loading" style="text-align: center; padding: 30px; display: none;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Đang tải vouchers...</p>
                    </div>
                    
                    <div id="voucher-content">
                        <!-- Available vouchers -->
                        <div class="voucher-section-modal">
                            <h6 class="voucher-section-title">Vouchers khả dụng</h6>
                            <div id="available-vouchers" class="voucher-list">
                                <!-- Available vouchers will be loaded here -->
                            </div>
                        </div>
                        
                        <!-- Unavailable vouchers -->
                        <div class="voucher-section-modal" style="margin-top: 30px;">
                            <h6 class="voucher-section-title">Vouchers không khả dụng</h6>
                            <div id="unavailable-vouchers" class="voucher-list">
                                <!-- Unavailable vouchers will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-danger" id="remove-voucher-btn" onclick="removeSelectedVoucher()" style="display: none;">
                        Bỏ chọn voucher
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Popup -->
    <div id="checkoutPopup" class="checkout-popup">
        <div class="checkout-content">
            <div class="checkout-header">
                <h3><i class="fa fa-credit-card"></i> Xác nhận thanh toán</h3>
            </div>
            <div class="checkout-body" id="checkoutBody">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Scripts - Load jQuery first -->
    <script src="assets/js/jquery.js"></script>
    
    <!-- Check if jQuery loaded -->
    <script>
        if (typeof jQuery === 'undefined') {
            console.error('❌ jQuery not loaded!');
            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
        } else {
            console.log('✅ jQuery loaded successfully');
        }
    </script>
    
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>

    <script>
        let cartData = {
            items: [],
            total: 0
        };

        $(document).ready(function() {
            console.log('🛒 Cart page JavaScript ready');
            console.log('🔍 Cart page: Found cart dropdown elements:', $('.cart-dropdown').length);
            
            // Force navigation dropdown to work on cart page
            if ($('.cart-dropdown').length > 0) {
                console.log('🎯 Cart page: Ensuring cart dropdown works');
                
                $('.cart-dropdown').off('mouseenter mouseleave').hover(
                    function() {
                        console.log('🖱️ Cart page: HOVER ENTER on cart dropdown');
                        $(this).addClass('show');
                        $('#cart-dropdown-menu').addClass('show').css('display', 'block');
                    },
                    function() {
                        console.log('🖱️ Cart page: HOVER LEAVE on cart dropdown');
                        setTimeout(() => {
                            if (!$('.cart-dropdown:hover').length && !$('#cart-dropdown-menu:hover').length) {
                                $('.cart-dropdown').removeClass('show');
                                $('#cart-dropdown-menu').removeClass('show').css('display', 'none');
                            }
                        }, 300);
                    }
                );
            }
            
            // Chỉ load cart nếu đã đăng nhập
            <?php if ($is_logged_in): ?>
            loadCartItems();
            <?php endif; ?>
        });

        function loadCartItems() {
            console.log('🔄 Loading cart items...');
            $('#loading-overlay').show();
            
            $.ajax({
                url: 'ajax/cart_handler.php',
                type: 'POST',
                data: { action: 'get_cart' },
                dataType: 'json',
                beforeSend: function() {
                    console.log('📤 Sending AJAX request to cart_handler.php');
                },
                success: function(response) {
                    console.log('✅ Cart response received:', response);
                    $('#loading-overlay').hide();
                    
                    if (response.success) {
                        console.log('📦 Cart items:', response.cart_items);
                        console.log('💰 Total amount:', response.total_amount);
                        cartData = {
                            items: response.cart_items,
                            total: response.total_amount
                        };
                        displayCartItems();
                    } else {
                        console.error('❌ Cart response failed:', response.message);
                        showErrorMessage('Không thể tải giỏ hàng: ' + (response.message || 'Unknown error'));
                        displayEmptyCart();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🚨 AJAX error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    $('#loading-overlay').hide();
                    showErrorMessage('Lỗi kết nối khi tải giỏ hàng: ' + error);
                    displayEmptyCart();
                }
            });
        }

        function displayCartItems() {
            console.log('🎨 Displaying cart items:', cartData);
            const container = $('#cart-items-container');
            
            if (!cartData.items || cartData.items.length === 0) {
                console.log('📭 Cart is empty, showing empty state');
                displayEmptyCart();
                return;
            }
            
            console.log('📋 Building HTML for', cartData.items.length, 'items');

            let html = `
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" title="Chọn tất cả">
                            </th>
                            <th style="width: 40%;">Sản phẩm</th>
                            <th style="width: 15%;">Đơn giá</th>
                            <th style="width: 15%;">Số lượng</th>
                            <th style="width: 15%;">Thành tiền</th>
                            <th style="width: 10%;">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            cartData.items.forEach(function(item) {
                const subtotal = parseFloat(item.price) * parseInt(item.quantity);
                const imageUrl = item.image_url || 'assets/images/default-product.jpg';
                
                html += `
                    <tr data-cart-id="${item.cart_id}">
                        <td style="text-align: center;">
                            <input type="checkbox" class="item-checkbox" 
                                   value="${item.cart_id}" 
                                   data-price="${item.price}"
                                   data-quantity="${item.quantity}"
                                   data-subtotal="${subtotal}"
                                   onchange="updateSelectedTotal()" 
                                   checked>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <img src="${imageUrl}" 
                                     alt="${item.item_name}" class="cart-item-image"
                                     onerror="this.src='assets/images/default-product.jpg'">
                                <div class="cart-item-info">
                                    <div class="cart-item-name">${item.item_name}</div>
                                    <div class="cart-item-type">
                                        ${item.item_type === 'product' ? 'Album nhạc' : 'Phụ kiện'}
                                    </div>
                                    <div style="color: #999; font-size: 0.8rem;">
                                        Còn lại: ${item.stock} sản phẩm
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="cart-item-price">${formatVND(item.price)}</td>
                        <td>
                            <div class="quantity-controls">
                                <button class="quantity-btn" 
                                        onclick="updateQuantity(${item.cart_id}, ${item.quantity - 1})"
                                        ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                <input type="number" class="quantity-input" value="${item.quantity}" 
                                       min="1" max="${item.stock}" 
                                       onchange="updateQuantity(${item.cart_id}, this.value)"
                                       onkeypress="if(event.key==='Enter') updateQuantity(${item.cart_id}, this.value)">
                                <button class="quantity-btn" 
                                        onclick="updateQuantity(${item.cart_id}, ${item.quantity + 1})"
                                        ${item.quantity >= item.stock ? 'disabled' : ''}>+</button>
                            </div>
                        </td>
                        <td class="cart-item-price">${formatVND(subtotal)}</td>
                        <td>
                            <button class="remove-btn" onclick="removeItem(${item.cart_id})" 
                                    title="Xóa sản phẩm">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Chi tiết đơn hàng:</h4>
                            <div class="summary-row">
                                <span>Tổng tất cả (${cartData.items.length} sản phẩm):</span>
                                <span id="total-all-items">${formatVND(cartData.total)}</span>
                            </div>
                            <div class="summary-row" style="color: #ff6b35; font-weight: 600;">
                                <span>Đã chọn (<span id="selected-count">0</span> sản phẩm):</span>
                                <span id="selected-subtotal">0₫</span>
                            </div>
                            <div class="summary-row">
                                <span>Phí vận chuyển:</span>
                                <span style="color: #28a745;">Miễn phí</span>
                            </div>
                            <div class="summary-row voucher-section">
                                <span>Voucher giảm giá:</span>
                                <div class="voucher-controls">
                                    <span id="selected-voucher-text">Chưa chọn</span>
                                    <button type="button" class="btn-voucher" onclick="showVoucherModal()">
                                        <i class="fa fa-ticket"></i> Chọn voucher
                                    </button>
                                </div>
                            </div>
                            <div class="summary-row discount-row" id="discount-row" style="display: none;">
                                <span>Giảm giá:</span>
                                <span id="discount-amount" style="color: #dc3545;">-0₫</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-summary">
                                <div class="summary-row" style="font-size: 1.5rem; color: #ff6b35; padding: 20px; background: white; border-radius: 8px; border: 2px solid #ff6b35;">
                                    <span>Thanh toán:</span>
                                    <span id="final-total-display">0₫</span>
                                </div>
                                <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 0.9rem; color: #666;">
                                    <i class="fa fa-info-circle"></i> Chỉ thanh toán cho sản phẩm đã chọn
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="cart-actions">
                    <a href="index.php" class="btn-continue">
                        <i class="fa fa-arrow-left"></i>
                        Tiếp tục mua sắm
                    </a>
                    <button class="btn-checkout" onclick="proceedToCheckout()">
                        <i class="fa fa-credit-card"></i>
                        Thanh toán ngay
                    </button>
                </div>
            `;

            container.html(html);
            
            // Auto calculate selected total after displaying items
            setTimeout(function() {
                updateSelectedTotal();
            }, 100);
        }

        function displayEmptyCart() {
            const container = $('#cart-items-container');
            container.html(`
                <div class="empty-cart">
                    <i class="fa fa-shopping-cart"></i>
                    <h3>Giỏ hàng trống</h3>
                    <p>Bạn chưa có sản phẩm nào trong giỏ hàng.<br>Hãy khám phá và thêm những sản phẩm yêu thích!</p>
                    <a href="index.php" class="btn-continue">
                        <i class="fa fa-music"></i>
                        Khám phá âm nhạc
                    </a>
                </div>
            `);
        }

        function updateQuantity(cartId, newQuantity) {
            newQuantity = parseInt(newQuantity);
            if (newQuantity <= 0) {
                removeItem(cartId);
                return;
            }

            // Disable buttons while updating
            $(`tr[data-cart-id="${cartId}"] .quantity-btn`).prop('disabled', true);

            $.ajax({
                url: 'ajax/cart_handler.php',
                type: 'POST',
                data: {
                    action: 'update_quantity',
                    cart_id: cartId,
                    quantity: newQuantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccessMessage('Đã cập nhật số lượng');
                        loadCartItems(); // Reload cart
                        updateCartBadge();
                        // Update selected total after reload
                        setTimeout(function() {
                            updateSelectedTotal();
                        }, 200);
                    } else {
                        showErrorMessage(response.message);
                        loadCartItems(); // Reload to reset values
                    }
                },
                error: function() {
                    showErrorMessage('Lỗi khi cập nhật số lượng');
                    loadCartItems();
                },
                complete: function() {
                    // Re-enable buttons
                    $(`tr[data-cart-id="${cartId}"] .quantity-btn`).prop('disabled', false);
                }
            });
        }

        function removeItem(cartId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                return;
            }

            $.ajax({
                url: 'ajax/cart_handler.php',
                type: 'POST',
                data: {
                    action: 'remove_item',
                    cart_id: cartId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccessMessage('Đã xóa sản phẩm khỏi giỏ hàng');
                        loadCartItems();
                        updateCartBadge();
                    } else {
                        showErrorMessage(response.message);
                    }
                },
                error: function() {
                    showErrorMessage('Lỗi khi xóa sản phẩm');
                }
            });
        }

        function updateCartBadge() {
            // Update cart badge in navigation
            $.ajax({
                url: 'ajax/cart_handler.php',
                type: 'POST',
                data: { action: 'get_cart' },
                dataType: 'json',
                success: function(response) {
                    if (response.success && $('#cart-badge').length) {
                        $('#cart-badge').text(response.total_items || 0);
                    }
                }
            });
        }

        function proceedToCheckout() {
            const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            
            if (!cartData.items || cartData.items.length === 0) {
                showErrorMessage('Giỏ hàng trống, không thể thanh toán');
                return;
            }
            
            if (selectedCheckboxes.length === 0) {
                showErrorMessage('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán');
                return;
            }
            
            // Collect selected items
            const selectedItems = [];
            selectedCheckboxes.forEach(checkbox => {
                const cartId = checkbox.value;
                const item = cartData.items.find(item => item.cart_id == cartId);
                if (item) {
                    selectedItems.push(item);
                }
            });
            
            console.log('🛒 Selected items for checkout:', selectedItems);
            console.log('🎫 Selected voucher:', selectedVoucher);
            
            // Calculate final amount
            let selectedTotal = 0;
            selectedItems.forEach(item => {
                selectedTotal += parseFloat(item.price) * parseInt(item.quantity);
            });
            
            let discount = 0;
            if (selectedVoucher && selectedTotal >= selectedVoucher.min_order_amount) {
                if (selectedVoucher.discount_type === 'percentage') {
                    discount = (selectedTotal * selectedVoucher.discount_value) / 100;
                    if (selectedVoucher.max_discount_amount > 0) {
                        discount = Math.min(discount, selectedVoucher.max_discount_amount);
                    }
                } else {
                    discount = Math.min(selectedVoucher.discount_value, selectedTotal);
                }
            }
            
            const finalAmount = selectedTotal - discount;
            
            // Confirm checkout
            const confirmMessage = `
Xác nhận thanh toán:
• ${selectedItems.length} sản phẩm được chọn
• Tổng tiền: ${formatVND(selectedTotal)}
${discount > 0 ? `• Giảm giá: -${formatVND(discount)}` : ''}
• Thành tiền: ${formatVND(finalAmount)}

Bạn có muốn tiếp tục thanh toán?
            `;
            
            // Show checkout popup with detailed information
            showCheckoutPopup(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount);
        }

        // Toast notification functions
        function showSuccessMessage(message) {
            showToast(message, 'success');
        }
        
        function showErrorMessage(message) {
            showToast(message, 'error');
        }
        
        function showToast(message, type) {
            var toast = createToast(message, type);
            document.body.appendChild(toast);
            
            setTimeout(function() {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(function() {
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, type === 'error' ? 4000 : 3000);
        }
        
        function createToast(message, type) {
            var toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML = `
                <div class="toast-content">
                    <i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span class="toast-message">${message}</span>
                </div>
            `;
            return toast;
        }

        // Format VND currency
        function formatVND(amount) {
            const vndAmount = parseFloat(amount); // Giá đã là VND trong database
            return vndAmount.toLocaleString('vi-VN') + '₫';
        }

        // Voucher functions
        let selectedVoucher = null;
        
        // Current payment data
        let currentPaymentData = null;
    let selectedPaymentMethod = null;
    let userBalance = 0;

        function showVoucherModal() {
            console.log('🎫 Opening voucher modal');
            $('#voucherModal').modal('show');
            loadUserVouchers();
        }

        function loadUserVouchers() {
            console.log('🔄 Loading user vouchers');
            $('#voucher-loading').show();
            $('#voucher-content').hide();

            $.ajax({
                url: 'ajax/voucher_handler.php',
                type: 'POST',
                data: { action: 'get_user_vouchers' },
                dataType: 'json',
                success: function(response) {
                    console.log('✅ Vouchers loaded:', response);
                    $('#voucher-loading').hide();
                    $('#voucher-content').show();
                    
                    if (response.success) {
                        displayVouchers(response.available_vouchers, response.unavailable_vouchers);
                    } else {
                        alert('Không thể tải vouchers: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('🚨 Voucher loading error:', error);
                    $('#voucher-loading').hide();
                    alert('Lỗi khi tải vouchers: ' + error);
                }
            });
        }

        function displayVouchers(available, unavailable) {
            console.log('🎨 Displaying vouchers - Available:', available.length, 'Unavailable:', unavailable.length);
            
            // Display available vouchers
            let availableHTML = '';
            if (available.length === 0) {
                availableHTML = '<p class="text-muted">Bạn chưa có voucher khả dụng nào.</p>';
            } else {
                available.forEach(voucher => {
                    availableHTML += generateVoucherCard(voucher, true);
                });
            }
            $('#available-vouchers').html(availableHTML);

            // Display unavailable vouchers
            let unavailableHTML = '';
            if (unavailable.length === 0) {
                unavailableHTML = '<p class="text-muted">Không có voucher hết hạn hoặc đã sử dụng.</p>';
            } else {
                unavailable.forEach(voucher => {
                    unavailableHTML += generateVoucherCard(voucher, false);
                });
            }
            $('#unavailable-vouchers').html(unavailableHTML);
        }

        function generateVoucherCard(voucher, isAvailable) {
            const statusClass = voucher.is_used ? 'used' : (voucher.is_expired ? 'expired' : 'available');
            const statusText = voucher.is_used ? 'Đã sử dụng' : (voucher.is_expired ? 'Hết hạn' : 'Khả dụng');
            
            let discountText = '';
            if (voucher.discount_type === 'percentage') {
                discountText = `-${voucher.discount_value}%`;
                if (voucher.max_discount_amount > 0) {
                    discountText += ` (tối đa ${formatVND(voucher.max_discount_amount)})`;
                }
            } else {
                discountText = `-${formatVND(voucher.discount_value)}`;
            }

            const minOrderText = voucher.min_order_amount > 0 
                ? `Đơn hàng tối thiểu: ${formatVND(voucher.min_order_amount)}` 
                : 'Không giới hạn đơn hàng';

            const onClick = isAvailable ? `onclick="selectVoucher(${voucher.user_voucher_id}, '${voucher.voucher_code}', '${voucher.discount_type}', ${voucher.discount_value}, ${voucher.min_order_amount}, ${voucher.max_discount_amount || 0})"` : '';

            return `
                <div class="voucher-card ${isAvailable ? 'available' : 'unavailable'}" ${onClick}>
                    <div class="voucher-header">
                        <div class="voucher-code">${voucher.voucher_code}</div>
                        <div class="voucher-status ${statusClass}">${statusText}</div>
                    </div>
                    <div class="voucher-discount">${discountText}</div>
                    <div class="voucher-details">
                        <div style="font-weight: 600; margin-bottom: 5px;">${voucher.voucher_name || voucher.voucher_code}</div>
                        <div style="font-size: 12px; color: #666; margin-bottom: 8px;">${voucher.description || 'Voucher giảm giá'}</div>
                        <div>${minOrderText}</div>
                        <div>Từ: ${voucher.start_date} - Đến: ${voucher.end_date}</div>
                    </div>
                </div>
            `;
        }

        function selectVoucher(userVoucherId, code, discountType, discountValue, minOrderAmount, maxDiscountAmount) {
            console.log('🎯 Selecting voucher:', code);
            
            // Check minimum order amount
            if (minOrderAmount > 0 && cartData.total < minOrderAmount) {
                alert(`Voucher này yêu cầu đơn hàng tối thiểu ${formatVND(minOrderAmount)}. Đơn hàng hiện tại chỉ có ${formatVND(cartData.total)}`);
                return;
            }

            selectedVoucher = {
                user_voucher_id: userVoucherId,
                code: code,
                discount_type: discountType,
                discount_value: discountValue,
                min_order_amount: minOrderAmount,
                max_discount_amount: maxDiscountAmount || 0
            };

            // Update UI
            $('.voucher-card').removeClass('selected');
            $(`[onclick*="${userVoucherId}"]`).addClass('selected');
            
            updateVoucherDisplay();
            $('#remove-voucher-btn').show();
            
            console.log('✅ Voucher selected:', selectedVoucher);
        }

        function removeSelectedVoucher() {
            console.log('🗑️ Removing selected voucher');
            selectedVoucher = null;
            $('.voucher-card').removeClass('selected');
            updateVoucherDisplay();
            $('#remove-voucher-btn').hide();
        }

        function updateVoucherDisplay() {
            if (selectedVoucher) {
                const discountAmount = calculateDiscount();
                $('#selected-voucher-text').text(selectedVoucher.code);
                $('#discount-amount').text(`-${formatVND(discountAmount)}`);
                $('#discount-row').show();
                
                // Update total
                updateCartTotal();
            } else {
                $('#selected-voucher-text').text('Chưa chọn');
                $('#discount-row').hide();
                updateCartTotal();
            }
        }

        function calculateDiscount() {
            if (!selectedVoucher || !cartData.total) return 0;
            
            let discount = 0;
            if (selectedVoucher.discount_type === 'percentage') {
                discount = (cartData.total * selectedVoucher.discount_value) / 100;
                // Apply max discount limit if set
                if (selectedVoucher.max_discount_amount > 0) {
                    discount = Math.min(discount, selectedVoucher.max_discount_amount);
                }
            } else {
                discount = Math.min(selectedVoucher.discount_value, cartData.total);
            }
            
            return discount;
        }

        // Checkbox handling functions
        function toggleSelectAll() {
            console.log('🔄 Toggle select all');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateSelectedTotal();
        }

        function updateSelectedTotal() {
            console.log('📊 Updating selected total');
            const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const totalCheckboxes = document.querySelectorAll('.item-checkbox');
            
            let selectedTotal = 0;
            let selectedCount = 0;
            
            selectedCheckboxes.forEach(checkbox => {
                const subtotal = parseFloat(checkbox.dataset.subtotal);
                selectedTotal += subtotal;
                selectedCount++;
            });
            
            // Update select all checkbox state
            if (selectAllCheckbox) {
                if (selectedCount === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (selectedCount === totalCheckboxes.length) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            }
            
            // Update display
            document.getElementById('selected-count').textContent = selectedCount;
            document.getElementById('selected-subtotal').textContent = formatVND(selectedTotal);
            
            // Update payment total considering voucher
            updatePaymentTotal(selectedTotal);
            
            console.log('✅ Selected:', selectedCount, 'items, Total:', formatVND(selectedTotal));
        }

        function updatePaymentTotal(selectedTotal) {
            let discount = 0;
            
            // Apply voucher discount only if minimum order is met
            if (selectedVoucher && selectedTotal >= selectedVoucher.min_order_amount) {
                if (selectedVoucher.discount_type === 'percentage') {
                    discount = (selectedTotal * selectedVoucher.discount_value) / 100;
                    if (selectedVoucher.max_discount_amount > 0) {
                        discount = Math.min(discount, selectedVoucher.max_discount_amount);
                    }
                } else {
                    discount = Math.min(selectedVoucher.discount_value, selectedTotal);
                }
            }
            
            const finalAmount = selectedTotal - discount;
            
            // Update display
            if (selectedVoucher && discount > 0) {
                $('#discount-amount').text(`-${formatVND(discount)}`);
                $('#discount-row').show();
            } else if (selectedVoucher && selectedTotal < selectedVoucher.min_order_amount) {
                $('#discount-amount').text(`Cần tối thiểu ${formatVND(selectedVoucher.min_order_amount)}`);
                $('#discount-row').show();
            } else {
                $('#discount-row').hide();
            }
            
            $('#final-total-display').text(formatVND(finalAmount));
            
            // Update checkout button state
            const checkoutBtn = $('.btn-checkout');
            if (selectedTotal > 0) {
                checkoutBtn.prop('disabled', false).removeClass('disabled');
            } else {
                checkoutBtn.prop('disabled', true).addClass('disabled');
            }
        }

        function updateCartTotal() {
            // This function is now mainly for backward compatibility
                    // The main calculation is done in updatePaymentTotal
        updateSelectedTotal();
    }

    // Checkout Popup Functions
    function showCheckoutPopup(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        console.log('🎉 Showing checkout popup');
        
        // Store current payment data globally
        currentPaymentData = {
            selectedItems: selectedItems,
            selectedVoucher: selectedVoucher,
            selectedTotal: selectedTotal,
            discount: discount,
            finalAmount: finalAmount
        };
        
        // Build checkout content
        let checkoutHTML = `
            <div class="checkout-section">
                <div class="checkout-section-title">
                    <i class="fa fa-shopping-bag"></i> Sản phẩm đã chọn (${selectedItems.length})
                </div>
        `;
        
        selectedItems.forEach(item => {
            checkoutHTML += `
                <div class="checkout-item">
                    <img src="${item.image_url}" alt="${item.name}" class="checkout-item-image">
                    <div class="checkout-item-info">
                        <div class="checkout-item-name">${item.name}</div>
                        <div class="checkout-item-details">
                            ${item.item_type === 'product' ? 'Đĩa nhạc' : 'Phụ kiện'} • SL: ${item.quantity}
                        </div>
                    </div>
                    <div class="checkout-item-price">${formatVND(item.price * item.quantity)}</div>
                </div>
            `;
        });
        
        checkoutHTML += `</div>`;
        
        // Add voucher section if voucher is selected
        if (selectedVoucher && discount > 0) {
            checkoutHTML += `
                <div class="checkout-section">
                    <div class="checkout-section-title">
                        <i class="fa fa-ticket"></i> Voucher áp dụng
                    </div>
                    <div class="voucher-info">
                        <div class="voucher-code-display">${selectedVoucher.code}</div>
                        <div style="margin-top: 5px; color: #666; font-size: 14px;">
                            Giảm ${selectedVoucher.discount_type === 'percentage' ? selectedVoucher.discount_value + '%' : formatVND(selectedVoucher.discount_value)}
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Add summary section
        checkoutHTML += `
            <div class="checkout-section">
                <div class="checkout-section-title">
                    <i class="fa fa-calculator"></i> Tổng kết thanh toán
                </div>
                <div class="checkout-summary">
                    <div class="summary-row">
                        <span>Tạm tính (${selectedItems.length} sản phẩm):</span>
                        <span>${formatVND(selectedTotal)}</span>
                    </div>
        `;
        
        if (discount > 0) {
            checkoutHTML += `
                    <div class="summary-row" style="color: #dc3545;">
                        <span>Giảm giá voucher:</span>
                        <span>-${formatVND(discount)}</span>
                    </div>
            `;
        }
        
        checkoutHTML += `
                    <div class="summary-row">
                        <span>Thành tiền:</span>
                        <span style="color: #28a745;">${formatVND(finalAmount)}</span>
                    </div>
                </div>
            </div>
        `;
        
        // Add payment method selection
        checkoutHTML += `
            <div class="checkout-section">
                <div class="checkout-section-title">
                    <i class="fa fa-credit-card"></i> Chọn phương thức thanh toán
                </div>
                <div class="payment-method-options">
                    <div class="payment-method-card" data-method="wallet" onclick="selectPaymentMethod('wallet')">
                        <div class="payment-method-icon">
                            <i class="fa fa-wallet"></i>
                        </div>
                        <div class="payment-method-info">
                            <div class="payment-method-name">Ví AuraDisc</div>
                            <div class="payment-method-desc">Thanh toán bằng số dư trong tài khoản</div>
                            <div class="payment-method-balance" id="current-balance">Số dư: Đang tải...</div>
                        </div>
                        <div class="payment-method-check">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                    
                    <div class="payment-method-card" data-method="vnpay" onclick="selectPaymentMethod('vnpay')">
                        <div class="payment-method-icon">
                            <i class="fa fa-university"></i>
                        </div>
                        <div class="payment-method-info">
                            <div class="payment-method-name">VNPay</div>
                            <div class="payment-method-desc">Thanh toán qua ngân hàng/ví điện tử</div>
                            <div class="payment-method-desc">Hỗ trợ tất cả ngân hàng Việt Nam</div>
                        </div>
                        <div class="payment-method-check">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                    
                    <div class="payment-method-card" data-method="cash" onclick="selectPaymentMethod('cash')">
                        <div class="payment-method-icon">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="payment-method-info">
                            <div class="payment-method-name">Tiền mặt</div>
                            <div class="payment-method-desc">Thanh toán khi nhận hàng (COD)</div>
                            <div class="payment-method-desc">Miễn phí giao hàng tại TPHCM</div>
                        </div>
                        <div class="payment-method-check">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add action buttons
        checkoutHTML += `
            <div class="checkout-actions">
                <button type="button" class="checkout-btn btn-cancel-payment" onclick="hideCheckoutPopup()">
                    <i class="fa fa-times"></i> Hủy
                </button>
                <button type="button" class="checkout-btn btn-confirm-payment" id="confirm-payment-btn" onclick="processCurrentPayment()" disabled>
                    <i class="fa fa-credit-card"></i> Xác nhận thanh toán
                </button>
            </div>
        `;
        
        // Update popup content and show
        document.getElementById('checkoutBody').innerHTML = checkoutHTML;
        document.getElementById('checkoutPopup').classList.add('show');
        
        // Load user balance for wallet option
        loadUserBalanceForCheckout();
        
        // Prevent background scrolling
        document.body.style.overflow = 'hidden';
    }
    
    function hideCheckoutPopup() {
        console.log('❌ Hiding checkout popup');
        document.getElementById('checkoutPopup').classList.remove('show');
        document.body.style.overflow = 'auto';
    }
    
    function processCurrentPayment() {
        if (!currentPaymentData) {
            alert('Không có dữ liệu thanh toán');
            return;
        }
        
        const { selectedItems, selectedVoucher, selectedTotal, discount, finalAmount } = currentPaymentData;
        processPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount);
    }
    
    function processPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        console.log('💳 Processing payment...', {
            selectedItems,
            selectedVoucher, 
            selectedTotal,
            discount,
            finalAmount
        });
        
        // Show processing state
        showPaymentProcessing();
        
        // Prepare data for payment
        const paymentData = {
            action: 'process_payment',
            selected_items: JSON.stringify(selectedItems),
            selected_voucher: selectedVoucher ? JSON.stringify(selectedVoucher) : null,
            selected_total: selectedTotal,
            discount: discount,
            final_amount: finalAmount
        };
        
        // Send payment request
        $.ajax({
            url: 'ajax/payment_handler.php',
            type: 'POST',
            data: paymentData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Payment response:', response);
                hidePaymentProcessing();
                
                if (response.success) {
                    showPaymentSuccess(response);
                } else {
                    showPaymentError(response.message || 'Thanh toán thất bại');
                }
            },
            error: function(xhr, status, error) {
                console.error('🚨 Payment error:', error);
                hidePaymentProcessing();
                showPaymentError('Lỗi kết nối. Vui lòng thử lại.');
            }
        });
    }
    
    function showPaymentProcessing() {
        const processingHTML = `
            <div class="payment-processing">
                <div class="spinner"></div>
                <h4 style="margin: 0; color: #333;">Đang xử lý thanh toán...</h4>
                <p style="margin: 10px 0 0 0; color: #666;">Vui lòng không tắt trang</p>
            </div>
        `;
        document.getElementById('checkoutBody').innerHTML = processingHTML;
    }
    
    function hidePaymentProcessing() {
        // This will be handled by success/error functions
    }
    
    function showPaymentSuccess(response) {
        const successHTML = `
            <div style="text-align: center; padding: 30px;">
                <div style="color: #28a745; font-size: 4rem; margin-bottom: 20px;">
                    <i class="fa fa-check-circle"></i>
                </div>
                <h3 style="color: #28a745; margin-bottom: 15px;">Thanh toán thành công!</h3>
                <p style="font-size: 1.1rem; margin-bottom: 10px;">Mã đơn hàng: <strong>#${response.order_id}</strong></p>
                <p style="color: #666; margin-bottom: 30px;">Cảm ơn bạn đã mua hàng tại AuraDisc!</p>
                <button type="button" class="checkout-btn btn-confirm-payment" onclick="closeSuccessAndRefresh()" style="max-width: 200px;">
                    <i class="fa fa-home"></i> Tiếp tục mua hàng
                </button>
            </div>
        `;
        document.getElementById('checkoutBody').innerHTML = successHTML;
    }
    
    function showPaymentError(message) {
        const errorHTML = `
            <div style="text-align: center; padding: 30px;">
                <div style="color: #dc3545; font-size: 4rem; margin-bottom: 20px;">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
                <h3 style="color: #dc3545; margin-bottom: 15px;">Thanh toán thất bại!</h3>
                <p style="color: #666; margin-bottom: 30px;">${message}</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button type="button" class="checkout-btn btn-cancel-payment" onclick="hideCheckoutPopup()">
                        <i class="fa fa-times"></i> Đóng
                    </button>
                    <button type="button" class="checkout-btn btn-confirm-payment" onclick="location.reload()">
                        <i class="fa fa-refresh"></i> Thử lại
                    </button>
                </div>
            </div>
        `;
        document.getElementById('checkoutBody').innerHTML = errorHTML;
    }
    
    function closeSuccessAndRefresh() {
        hideCheckoutPopup();
        location.reload(); // Refresh page to show updated cart
    }
    
    // Payment method selection functions
    function selectPaymentMethod(method) {
        console.log('💳 Selected payment method:', method);
        
        // Remove selected class from all cards
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Add selected class to chosen card
        const selectedCard = document.querySelector(`[data-method="${method}"]`);
        if (selectedCard && !selectedCard.classList.contains('disabled')) {
            selectedCard.classList.add('selected');
            selectedPaymentMethod = method;
            
            // Enable confirm button
            const confirmBtn = document.getElementById('confirm-payment-btn');
            confirmBtn.disabled = false;
            
            // Update button text based on method
            const btnIcon = confirmBtn.querySelector('i');
            const btnText = confirmBtn.childNodes[1];
            
            switch(method) {
                case 'wallet':
                    btnIcon.className = 'fa fa-wallet';
                    btnText.textContent = ' Thanh toán bằng ví';
                    break;
                case 'vnpay':
                    btnIcon.className = 'fa fa-university';
                    btnText.textContent = ' Thanh toán VNPay';
                    break;
                case 'cash':
                    btnIcon.className = 'fa fa-money';
                    btnText.textContent = ' Đặt hàng COD';
                    break;
            }
        }
    }
    
    function loadUserBalanceForCheckout() {
        console.log('💰 Loading user balance for checkout...');
        
        $.ajax({
            url: 'ajax/wallet_handler.php',
            method: 'POST',
            data: { action: 'get_balance' },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    userBalance = parseFloat(data.balance) || 0;
                    const balanceDisplay = document.getElementById('current-balance');
                    
                    if (balanceDisplay) {
                        balanceDisplay.textContent = `Số dư: ${data.formatted_balance}`;
                        
                        // Check if balance is sufficient for current order
                        const finalAmount = currentPaymentData ? currentPaymentData.finalAmount : 0;
                        const walletCard = document.querySelector('[data-method="wallet"]');
                        
                        if (userBalance >= finalAmount) {
                            balanceDisplay.classList.add('wallet-sufficient');
                            balanceDisplay.classList.remove('wallet-insufficient');
                            walletCard.classList.remove('disabled');
                        } else {
                            balanceDisplay.classList.add('wallet-insufficient');
                            balanceDisplay.classList.remove('wallet-sufficient');
                            balanceDisplay.textContent += ' (Không đủ)';
                            walletCard.classList.add('disabled');
                        }
                    }
                } else {
                    console.error('❌ Failed to load balance:', data.message);
                    const balanceDisplay = document.getElementById('current-balance');
                    if (balanceDisplay) {
                        balanceDisplay.textContent = 'Lỗi tải số dư';
                        balanceDisplay.classList.add('wallet-insufficient');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX error loading balance:', error);
                const balanceDisplay = document.getElementById('current-balance');
                if (balanceDisplay) {
                    balanceDisplay.textContent = 'Lỗi kết nối';
                    balanceDisplay.classList.add('wallet-insufficient');
                }
            }
        });
    }
    
    function processCurrentPayment() {
        if (!currentPaymentData) {
            alert('Không có dữ liệu thanh toán');
            return;
        }
        
        if (!selectedPaymentMethod) {
            alert('Vui lòng chọn phương thức thanh toán');
            return;
        }
        
        const { selectedItems, selectedVoucher, selectedTotal, discount, finalAmount } = currentPaymentData;
        
        console.log('🚀 Processing payment with method:', selectedPaymentMethod);
        
        switch(selectedPaymentMethod) {
            case 'wallet':
                processWalletPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount);
                break;
            case 'vnpay':
                processVNPayPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount);
                break;
            case 'cash':
                processCashPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount);
                break;
            default:
                alert('Phương thức thanh toán không hợp lệ');
        }
    }
    
    function processWalletPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        console.log('💳 Processing wallet payment...');
        
        if (userBalance < finalAmount) {
            alert(`Số dư không đủ!\nCần: ${formatVND(finalAmount)}\nCó: ${formatVND(userBalance)}`);
            return;
        }
        
        showPaymentProcessing('Đang xử lý thanh toán qua ví...');
        
        const paymentData = {
            action: 'process_payment',
            payment_method: 'wallet',
            selected_items: JSON.stringify(selectedItems),
            selected_voucher: selectedVoucher ? JSON.stringify(selectedVoucher) : null,
            selected_total: selectedTotal,
            discount: discount,
            final_amount: finalAmount
        };
        
        $.ajax({
            url: 'ajax/payment_handler.php',
            type: 'POST',
            data: paymentData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Wallet payment response:', response);
                hidePaymentProcessing();
                
                if (response.success) {
                    showPaymentSuccess(response);
                } else {
                    showPaymentError(response.message || 'Thanh toán thất bại');
                }
            },
            error: function(xhr, status, error) {
                console.error('🚨 Wallet payment error:', error);
                hidePaymentProcessing();
                showPaymentError('Lỗi kết nối. Vui lòng thử lại.');
            }
        });
    }
    
    function processVNPayPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        console.log('🏦 Processing VNPay payment...');
        
        showPaymentProcessing('Đang tạo đơn thanh toán VNPay...');
        
        // Create VNPay order first
        createVNPayOrder(finalAmount, selectedItems, selectedVoucher, selectedTotal, discount);
    }
    
    function createVNPayOrder(amount, selectedItems, selectedVoucher, selectedTotal, discount) {
        // Try multiple methods like in profile.php
        testAPIConnection()
            .then(() => {
                console.log('✅ API connection test successful');
                return createPaymentOrderFetch(amount, selectedItems, selectedVoucher, selectedTotal, discount);
            })
            .catch(error => {
                console.warn('🔄 Fetch failed, trying XMLHttpRequest...', error);
                updatePaymentProcessing('Đang thử phương pháp khác...');
                
                setTimeout(() => {
                    createPaymentOrderXHR(amount, selectedItems, selectedVoucher, selectedTotal, discount)
                        .catch(xhrError => {
                            console.warn('🔄 XHR also failed, trying PHP proxy...', xhrError);
                            updatePaymentProcessing('Đang thử proxy...');
                            
                            setTimeout(() => {
                                createPaymentOrderProxy(amount, selectedItems, selectedVoucher, selectedTotal, discount);
                            }, 1000);
                        });
                }, 1000);
            });
    }
    
    function processCashPayment(selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        console.log('💵 Processing cash payment (COD)...');
        
        showPaymentProcessing('Đang tạo đơn hàng COD...');
        
        const paymentData = {
            action: 'process_payment',
            payment_method: 'cash',
            selected_items: JSON.stringify(selectedItems),
            selected_voucher: selectedVoucher ? JSON.stringify(selectedVoucher) : null,
            selected_total: selectedTotal,
            discount: discount,
            final_amount: finalAmount
        };
        
        $.ajax({
            url: 'ajax/payment_handler.php',
            type: 'POST',
            data: paymentData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Cash payment response:', response);
                hidePaymentProcessing();
                
                if (response.success) {
                    showPaymentSuccess(response);
                } else {
                    showPaymentError(response.message || 'Tạo đơn hàng thất bại');
                }
            },
            error: function(xhr, status, error) {
                console.error('🚨 Cash payment error:', error);
                hidePaymentProcessing();
                showPaymentError('Lỗi kết nối. Vui lòng thử lại.');
            }
        });
    }
    
    // VNPay helper functions (copied from profile.php)
    function testAPIConnection() {
        return new Promise((resolve, reject) => {
            console.log('🌐 Skipping health check, testing direct API...');
            resolve(); // Always continue without health check
        });
    }
    
    function createPaymentOrderFetch(amount, selectedItems, selectedVoucher, selectedTotal, discount) {
        return new Promise((resolve, reject) => {
            fetch('https://duc-spring.ngodat0103.live/demo/api/app/order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                    'Origin': window.location.origin,
                    'Referer': window.location.href,
                },
                mode: 'cors',
                credentials: 'omit',
                body: JSON.stringify({
                    amount: amount,
                    orderInfo: 'Thanh toán đơn hàng AuraDisc'
                })
            })
            .then(response => {
                console.log('📡 Fetch Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Fetch VNPay order created:', data);
                
                if (data.orderId && data.paymentUrl) {
                    updatePaymentProcessing('Đang chờ thanh toán VNPay...');
                    const paymentWindow = window.open(data.paymentUrl, '_blank');
                    checkVNPayStatus(data.orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount, paymentWindow);
                    resolve(data);
                } else {
                    throw new Error('Không nhận được thông tin thanh toán từ server. Response: ' + JSON.stringify(data));
                }
            })
            .catch(error => {
                console.error('❌ Fetch VNPay order error:', error);
                reject(error);
            });
        });
    }
    
    function createPaymentOrderXHR(amount, selectedItems, selectedVoucher, selectedTotal, discount) {
        return new Promise((resolve, reject) => {
            console.log('🔄 Trying XMLHttpRequest method...');
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'https://duc-spring.ngodat0103.live/demo/api/app/order', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
            xhr.setRequestHeader('Origin', window.location.origin);
            xhr.setRequestHeader('Referer', window.location.href);
            xhr.withCredentials = false;
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log('📡 XHR Response status:', xhr.status);
                    
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('✅ XHR VNPay order created:', data);
                            
                            if (data.orderId && data.paymentUrl) {
                                updatePaymentProcessing('Đang chờ thanh toán VNPay...');
                                const paymentWindow = window.open(data.paymentUrl, '_blank');
                                checkVNPayStatus(data.orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount, paymentWindow);
                                resolve(data);
                            } else {
                                reject(new Error('Không nhận được thông tin thanh toán từ server'));
                            }
                        } catch (parseError) {
                            reject(parseError);
                        }
                    } else {
                        reject(new Error(`HTTP ${xhr.status}: ${xhr.statusText}`));
                    }
                }
            };
            
            xhr.onerror = () => reject(new Error('Lỗi mạng'));
            xhr.ontimeout = () => reject(new Error('Hết thời gian chờ'));
            xhr.timeout = 30000;
            
            const requestData = JSON.stringify({
                amount: amount,
                orderInfo: 'Thanh toán đơn hàng AuraDisc'
            });
            
            xhr.send(requestData);
        });
    }
    
    function createPaymentOrderProxy(amount, selectedItems, selectedVoucher, selectedTotal, discount) {
        console.log('🔄 Trying PHP proxy method...');
        
        fetch('ajax/payment_proxy.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                action: 'create_order',
                amount: amount,
                orderInfo: 'Thanh toán đơn hàng AuraDisc'
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Proxy VNPay order created:', data);
            
            if (data.error) {
                throw new Error('Proxy Error: ' + data.error);
            }
            
            if (data.orderId && data.paymentUrl) {
                updatePaymentProcessing('Đang chờ thanh toán VNPay...');
                const paymentWindow = window.open(data.paymentUrl, '_blank');
                checkVNPayStatusProxy(data.orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount, paymentWindow);
            } else {
                throw new Error('Không nhận được thông tin thanh toán từ proxy');
            }
        })
        .catch(error => {
            console.error('❌ All VNPay methods failed:', error);
            hidePaymentProcessing();
            showPaymentError('Không thể tạo đơn thanh toán VNPay. Vui lòng thử lại sau.');
        });
    }
    
    function checkVNPayStatus(orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount, paymentWindow) {
        console.log('🔍 Checking VNPay status for order:', orderId);
        
        const maxAttempts = 60;
        let attempts = 0;
        
        const checkInterval = setInterval(() => {
            attempts++;
            updatePaymentProcessing(`Kiểm tra thanh toán VNPay... (${Math.max(0, maxAttempts - attempts)}s)`);
            
            fetch(`https://duc-spring.ngodat0103.live/demo/api/app/order/${orderId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Origin': window.location.origin,
                },
                mode: 'cors',
            })
            .then(response => response.json())
            .then(data => {
                console.log('💳 VNPay status:', data);
                
                if (data.status === 'PAID') {
                    clearInterval(checkInterval);
                    
                    if (paymentWindow && !paymentWindow.closed) {
                        paymentWindow.close();
                    }
                    
                    updatePaymentProcessing('Đang tạo đơn hàng...');
                    
                    // Create order in database
                    createOrderAfterVNPay(orderId, selectedItems, selectedVoucher, selectedTotal, discount, amount);
                    
                } else if (data.status === 'UNPAID') {
                    if (paymentWindow && paymentWindow.closed) {
                        clearInterval(checkInterval);
                        hidePaymentProcessing();
                        showPaymentError('Thanh toán bị hủy');
                    }
                }
            })
            .catch(error => {
                console.error('❌ VNPay status check error:', error);
                // Try proxy fallback
                checkVNPayStatusProxyFallback(orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount);
            });
            
            if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                if (paymentWindow && !paymentWindow.closed) {
                    paymentWindow.close();
                }
                hidePaymentProcessing();
                showPaymentError('Hết thời gian chờ thanh toán');
            }
        }, 5000);
    }
    
    function checkVNPayStatusProxy(orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount, paymentWindow) {
        console.log('🔍 Checking VNPay status via proxy for order:', orderId);
        
        const maxAttempts = 60;
        let attempts = 0;
        
        const checkInterval = setInterval(() => {
            attempts++;
            updatePaymentProcessing(`Kiểm tra thanh toán VNPay... (${Math.max(0, maxAttempts - attempts)}s)`);
            
            fetch('ajax/payment_proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'check_status',
                    orderId: orderId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('💳 VNPay status via proxy:', data);
                
                if (data.status === 'PAID') {
                    clearInterval(checkInterval);
                    
                    if (paymentWindow && !paymentWindow.closed) {
                        paymentWindow.close();
                    }
                    
                    updatePaymentProcessing('Đang tạo đơn hàng...');
                    createOrderAfterVNPay(orderId, selectedItems, selectedVoucher, selectedTotal, discount, amount);
                } else if (data.status === 'UNPAID') {
                    if (paymentWindow && paymentWindow.closed) {
                        clearInterval(checkInterval);
                        hidePaymentProcessing();
                        showPaymentError('Thanh toán bị hủy');
                    }
                }
            })
            .catch(error => {
                console.error('❌ Proxy VNPay status check error:', error);
            });
            
            if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                if (paymentWindow && !paymentWindow.closed) {
                    paymentWindow.close();
                }
                hidePaymentProcessing();
                showPaymentError('Hết thời gian chờ thanh toán');
            }
        }, 5000);
    }
    
    function checkVNPayStatusProxyFallback(orderId, amount, selectedItems, selectedVoucher, selectedTotal, discount) {
        fetch('ajax/payment_proxy.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'check_status',
                orderId: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.error && data.status === 'PAID') {
                updatePaymentProcessing('Đang tạo đơn hàng...');
                createOrderAfterVNPay(orderId, selectedItems, selectedVoucher, selectedTotal, discount, amount);
            }
        })
        .catch(error => {
            console.error('❌ Proxy fallback failed:', error);
        });
    }
    
    function createOrderAfterVNPay(vnpayOrderId, selectedItems, selectedVoucher, selectedTotal, discount, finalAmount) {
        const paymentData = {
            action: 'process_payment',
            payment_method: 'vnpay',
            vnpay_order_id: vnpayOrderId,
            selected_items: JSON.stringify(selectedItems),
            selected_voucher: selectedVoucher ? JSON.stringify(selectedVoucher) : null,
            selected_total: selectedTotal,
            discount: discount,
            final_amount: finalAmount
        };
        
        $.ajax({
            url: 'ajax/payment_handler.php',
            type: 'POST',
            data: paymentData,
            dataType: 'json',
            success: function(response) {
                console.log('✅ VNPay order creation response:', response);
                hidePaymentProcessing();
                
                if (response.success) {
                    showPaymentSuccess(response);
                } else {
                    showPaymentError(response.message || 'Lỗi tạo đơn hàng sau thanh toán');
                }
            },
            error: function(xhr, status, error) {
                console.error('🚨 VNPay order creation error:', error);
                hidePaymentProcessing();
                showPaymentError('Lỗi tạo đơn hàng. Vui lòng liên hệ hỗ trợ.');
            }
        });
    }
    
    function updatePaymentProcessing(message) {
        const processingHTML = `
            <div class="payment-processing">
                <div class="spinner"></div>
                <h4 style="margin: 0; color: #333;">${message}</h4>
                <p style="margin: 10px 0 0 0; color: #666;">Vui lòng không tắt trang</p>
            </div>
        `;
        document.getElementById('checkoutBody').innerHTML = processingHTML;
    }
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'checkoutPopup') {
            hideCheckoutPopup();
        }
    });
    </script>
</body>
</html>