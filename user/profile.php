<?php
// Kết nối database và khởi tạo session
require_once '../config/database.php';
require_once 'includes/session.php';

// Kiểm tra đăng nhập cho trang profile
$is_logged_in = isLoggedIn();
$current_user = getCurrentUser();

// Profile cần đăng nhập, nếu chưa đăng nhập thì hiển thị thông báo
if ($is_logged_in) {
    $user_id = $current_user['user_id'];

    // Lấy voucher đã sở hữu của user
    $sql_user_vouchers = "SELECT v.*, uv.assigned_date, uv.used_date, uv.is_used,
                                 CASE 
                                     WHEN v.is_active = 0 THEN 'inactive'
                                     WHEN v.end_date < CURDATE() THEN 'expired'
                                     WHEN uv.is_used = 1 THEN 'used'
                                     ELSE 'available'
                                 END as voucher_status
                          FROM user_vouchers uv 
                          JOIN vouchers v ON uv.voucher_id = v.voucher_id 
                          WHERE uv.user_id = ? 
                          ORDER BY uv.assigned_date DESC";

    $stmt_vouchers = $conn->prepare($sql_user_vouchers);
    $stmt_vouchers->bind_param("i", $user_id);
    $stmt_vouchers->execute();
    $result_vouchers = $stmt_vouchers->get_result();
    $user_vouchers = [];
    while ($row = $result_vouchers->fetch_assoc()) {
        $user_vouchers[] = $row;
    }
} else {
    // Nếu chưa đăng nhập
    $user_vouchers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>Thông tin cá nhân - AuraDisc</title>

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
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Profile Navigation and Content -->
    <section class="profile-section" style="padding: 80px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center" style="margin-bottom: 40px;">Thông tin cá nhân</h2>
                    
                    <?php if (!$is_logged_in): ?>
                    <!-- Yêu cầu đăng nhập -->
                    <div class="login-required" style="text-align: center; padding: 80px 30px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <i class="fa fa-lock" style="font-size: 120px; color: #dee2e6; margin-bottom: 30px; display: block;"></i>
                        <h3 style="color: #666; margin-bottom: 15px; font-size: 1.8rem;">Cần đăng nhập</h3>
                        <p style="color: #999; margin-bottom: 40px; font-size: 1.1rem;">Vui lòng đăng nhập để xem thông tin cá nhân và quản lý tài khoản</p>
                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <a href="/WEB_MXH/index.php" class="btn" style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                                <i class="fa fa-sign-in"></i>
                                Đăng nhập
                            </a>
                            <a href="index.php" class="btn" style="background: #6c757d; color: white; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                                <i class="fa fa-arrow-left"></i>
                                Về trang chủ
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    
                    <!-- Profile Navigation Tabs -->
                    <div class="profile-nav-wrapper">
                        <ul class="nav nav-tabs profile-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#vouchers" aria-controls="vouchers" role="tab" data-toggle="tab">
                                    <i class="fa fa-ticket"></i>
                                    <span>Xem voucher</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#personal-info" aria-controls="personal-info" role="tab" data-toggle="tab">
                                    <i class="fa fa-user"></i>
                                    <span>Xem thông tin cá nhân</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#invoices" aria-controls="invoices" role="tab" data-toggle="tab">
                                    <i class="fa fa-file-text-o"></i>
                                    <span>Xem hóa đơn</span>
                                </a>
                            </li>
                            <li role="presentation">
                                <a href="#favorites" aria-controls="favorites" role="tab" data-toggle="tab">
                                    <i class="fa fa-heart"></i>
                                    <span>Sản phẩm yêu thích</span>
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content profile-content">
                            <!-- Vouchers Tab -->
                            <div role="tabpanel" class="tab-pane active" id="vouchers">
                                <div class="content-wrapper">
                                    <h3>Voucher của tôi</h3>
                                    
                                    <?php if (empty($user_vouchers)): ?>
                                        <div class="empty-state">
                                            <i class="fa fa-ticket" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                                            <p>Bạn chưa có voucher nào. Hãy tham gia các sự kiện để nhận voucher miễn phí!</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="vouchers-grid">
                                            <?php foreach ($user_vouchers as $voucher): ?>
                                                <div class="voucher-card <?php echo $voucher['voucher_status']; ?>">
                                                    <div class="voucher-header">
                                                        <div class="voucher-type">
                                                            <?php echo ucfirst($voucher['discount_type']); ?>
                                                        </div>
                                                        <div class="voucher-status">
                                                            <?php 
                                                            switch($voucher['voucher_status']) {
                                                                case 'available':
                                                                    echo '<span class="status-available">Khả dụng</span>';
                                                                    break;
                                                                case 'used':
                                                                    echo '<span class="status-used">Đã sử dụng</span>';
                                                                    break;
                                                                case 'expired':
                                                                    echo '<span class="status-expired">Đã hết hạn</span>';
                                                                    break;
                                                                case 'inactive':
                                                                    echo '<span class="status-inactive">Không còn khả dụng</span>';
                                                                    break;
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="voucher-body">
                                                        <h4 class="voucher-title"><?php echo htmlspecialchars($voucher['voucher_name']); ?></h4>
                                                        <p class="voucher-description"><?php echo htmlspecialchars($voucher['description']); ?></p>
                                                        
                                                        <div class="voucher-value">
                                                            <?php if ($voucher['discount_type'] == 'percentage'): ?>
                                                                <span class="discount-value"><?php echo $voucher['discount_value']; ?>%</span>
                                                                <span class="discount-label">Giảm giá</span>
                                                            <?php else: ?>
                                                                <span class="discount-value"><?php echo number_format($voucher['discount_value'], 0, ',', '.'); ?>₫</span>
                                                                <span class="discount-label">Giảm tiền</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <?php if ($voucher['min_order_amount'] > 0): ?>
                                                            <div class="min-order">
                                                                Đơn hàng tối thiểu: <?php echo number_format($voucher['min_order_amount'], 0, ',', '.'); ?>₫
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="voucher-footer">
                                                        <div class="voucher-dates">
                                                            <small>
                                                                <?php if ($voucher['voucher_status'] == 'expired'): ?>
                                                                    <span class="date-expired">Đã hết hạn: <?php echo date('d/m/Y', strtotime($voucher['end_date'])); ?></span>
                                                                <?php else: ?>
                                                                    Hạn sử dụng: <?php echo date('d/m/Y', strtotime($voucher['end_date'])); ?>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                        <div class="voucher-code">
                                                            <code><?php echo $voucher['voucher_code']; ?></code>
                                                            <?php if ($voucher['voucher_status'] == 'available'): ?>
                                                                <button class="copy-code-btn" onclick="copyCode('<?php echo $voucher['voucher_code']; ?>')">
                                                                    <i class="fa fa-copy"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="copy-code-btn" disabled title="Voucher không thể sử dụng">
                                                                    <i class="fa fa-ban"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($voucher['voucher_status'] == 'used' && $voucher['used_date']): ?>
                                                        <div class="used-date">
                                                            <small>Đã sử dụng: <?php echo date('d/m/Y H:i', strtotime($voucher['used_date'])); ?></small>
                                                        </div>
                                                    <?php elseif ($voucher['voucher_status'] == 'inactive'): ?>
                                                        <div class="inactive-date">
                                                            <small>Voucher đã bị vô hiệu hóa bởi hệ thống</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Personal Info Tab -->
                            <div role="tabpanel" class="tab-pane" id="personal-info">
                                <div class="content-wrapper">
                                    <div class="profile-header">
                                        <h3>Thông tin cá nhân</h3>
                                        <button type="button" class="btn-edit" id="editProfileBtn" onclick="toggleEditMode()">
                                            <i class="fa fa-edit"></i> Chỉnh sửa
                                        </button>
                                    </div>
                                    
                                    <!-- View Mode -->
                                    <div id="viewMode" class="profile-info">
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <label class="info-label">Tên đăng nhập:</label>
                                                <span class="info-value"><?php echo htmlspecialchars($current_user['username'] ?? ''); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Họ và tên:</label>
                                                <span class="info-value" id="viewFullName"><?php echo htmlspecialchars($current_user['full_name'] ?? ''); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Email:</label>
                                                <span class="info-value" id="viewEmail"><?php echo htmlspecialchars($current_user['email'] ?? ''); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Giới tính:</label>
                                                <span class="info-value" id="viewGender"><?php echo htmlspecialchars($current_user['gender'] ?? 'Chưa cập nhật'); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Số điện thoại:</label>
                                                <span class="info-value" id="viewPhone"><?php echo htmlspecialchars($current_user['phone'] ?? 'Chưa cập nhật'); ?></span>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Địa chỉ:</label>
                                                <span class="info-value" id="viewAddress"><?php echo isset($current_user['address']) && $current_user['address'] ? htmlspecialchars($current_user['address']) : 'Chưa cập nhật'; ?></span>
                                            </div>
                                            
                                            <div class="info-item wallet-info">
                                                <label class="info-label">Số dư tài khoản:</label>
                                                <span class="info-value wallet-balance" id="viewBalance">
                                                    <i class="fa fa-wallet"></i>
                                                    <span id="balanceAmount">Loading...</span>
                                                </span>
                                                <button type="button" class="btn-add-balance" onclick="showAddBalanceModal()">
                                                    <i class="fa fa-plus"></i> Nạp tiền
                                                </button>
                                            </div>
                                            
                                            <div class="info-item">
                                                <label class="info-label">Ngày đăng ký:</label>
                                                <span class="info-value">
                                                    <?php 
                                                    if (isset($current_user['created_at']) && !empty($current_user['created_at'])) {
                                                        echo date('d/m/Y H:i', strtotime($current_user['created_at']));
                                                    } else {
                                                        // Nếu không có ngày đăng ký, hiển thị ngày hiện tại
                                                        echo date('d/m/Y H:i');
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Mode -->
                                    <div id="editMode" class="profile-edit" style="display: none;">
                                        <form id="profileForm" class="edit-form">
                                            <div class="form-grid">
                                                <div class="form-group">
                                                    <label for="editFullName">Họ và tên:</label>
                                                    <input type="text" id="editFullName" name="full_name" 
                                                           value="<?php echo htmlspecialchars($current_user['full_name'] ?? ''); ?>" 
                                                           class="form-control" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="editEmail">Email:</label>
                                                    <input type="email" id="editEmail" name="email" 
                                                           value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" 
                                                           class="form-control" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="editGender">Giới tính:</label>
                                                    <select id="editGender" name="gender" class="form-control" required>
                                                        <option value="Nam" <?php echo isset($current_user['gender']) && $current_user['gender'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                                        <option value="Nữ" <?php echo isset($current_user['gender']) && $current_user['gender'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                                        <option value="Khác" <?php echo isset($current_user['gender']) && $current_user['gender'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="editPhone">Số điện thoại:</label>
                                                    <input type="tel" id="editPhone" name="phone" 
                                                           value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>" 
                                                           class="form-control" required>
                                                </div>
                                                
                                                <div class="form-group" style="grid-column: 1 / -1;">
                                                    <label for="editAddress">Địa chỉ:</label>
                                                    <textarea id="editAddress" name="address" 
                                                              class="form-control" rows="3" 
                                                              placeholder="Nhập địa chỉ chi tiết..."><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="form-actions">
                                                <button type="button" class="btn-cancel" onclick="cancelEdit()">
                                                    <i class="fa fa-times"></i> Hủy
                                                </button>
                                                <button type="submit" class="btn-save">
                                                    <i class="fa fa-save"></i> Lưu thay đổi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoices Tab -->
                            <div role="tabpanel" class="tab-pane" id="invoices">
                                <div class="content-wrapper">
                                    <div class="profile-header">
                                        <h3>Hóa đơn đã mua</h3>
                                        <div class="invoice-stats">
                                            <span id="totalInvoices">0</span> đơn hàng
                                        </div>
                                    </div>
                                    
                                    <div class="invoice-filters">
                                                                <select id="invoiceStatusFilter" onchange="filterInvoices()">
                            <option value="">Tất cả trạng thái</option>
                            <option value="0">Đặt hàng</option>
                            <option value="1">Xử lý</option>
                            <option value="2">Vận chuyển</option>
                            <option value="3">Hoàn thành</option>
                            <option value="-1">Đã hủy</option>
                        </select>
                                        
                                        <input type="date" id="invoiceDateFilter" onchange="filterInvoices()">
                                        
                                        <button class="btn-refresh" onclick="loadInvoices()">
                                            <i class="fa fa-refresh"></i> Làm mới
                                        </button>
                                    </div>
                                    
                                    <div id="invoicesContainer" class="invoices-container">
                                        <div class="loading-spinner">
                                            <i class="fa fa-spinner fa-spin"></i> Đang tải hóa đơn...
                                        </div>
                                    </div>
                                    
                                    <div id="invoicesPagination" class="pagination-wrapper"></div>
                                </div>
                            </div>

                            <!-- Favorites Tab -->
                            <div role="tabpanel" class="tab-pane" id="favorites">
                                <div class="content-wrapper">
                                    <div class="profile-header">
                                        <h3>Sản phẩm yêu thích</h3>
                                        <div class="favorites-stats">
                                            <span id="totalFavorites">0</span> sản phẩm
                                        </div>
                                    </div>
                                    
                                    <div class="favorites-filters">
                                        <select id="favoritesTypeFilter" onchange="filterFavorites()">
                                            <option value="">Tất cả loại</option>
                                            <option value="product">Album nhạc</option>
                                            <option value="accessory">Phụ kiện</option>
                                        </select>
                                        
                                        <button class="btn-refresh" onclick="loadFavorites()">
                                            <i class="fa fa-refresh"></i> Làm mới
                                        </button>
                                    </div>
                                    
                                    <div id="favoritesContainer" class="favorites-container">
                                        <div class="loading-spinner">
                                            <i class="fa fa-spinner fa-spin"></i> Đang tải sản phẩm yêu thích...
                                        </div>
                                    </div>
                                    
                                    <div id="favoritesPagination" class="pagination-wrapper"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Balance Modal -->
    <div class="modal-backdrop" id="modalBackdrop" onclick="hideAddBalanceModal()"></div>
    <div class="add-balance-modal" id="addBalanceModal">
        <div class="modal-header">
            <h4><i class="fa fa-wallet"></i> Nạp tiền vào tài khoản</h4>
            <button class="modal-close" onclick="hideAddBalanceModal()">×</button>
        </div>
        
        <!-- Payment notice -->
        <div class="payment-notice">
            <div class="notice-icon">
                <i class="fa fa-info-circle"></i>
            </div>
            <div class="notice-text">
                <strong>Thanh toán an toàn</strong><br>
                Giao dịch được xử lý qua cổng thanh toán bảo mật. Bạn sẽ được chuyển đến trang thanh toán trong tab mới.
            </div>
        </div>
        
        <div class="amount-options">
            <div class="amount-btn" onclick="selectAmount(50000)">
                <span class="amount-value">50.000₫</span>
                <span class="amount-label">Cơ bản</span>
            </div>
            <div class="amount-btn" onclick="selectAmount(100000)">
                <span class="amount-value">100.000₫</span>
                <span class="amount-label">Phổ biến</span>
            </div>
            <div class="amount-btn" onclick="selectAmount(200000)">
                <span class="amount-value">200.000₫</span>
                <span class="amount-label">Tiết kiệm</span>
            </div>
            <div class="amount-btn" onclick="selectAmount(500000)">
                <span class="amount-value">500.000₫</span>
                <span class="amount-label">Ưu đãi</span>
            </div>
            <div class="amount-btn" onclick="selectAmount(1000000)">
                <span class="amount-value">1.000.000₫</span>
                <span class="amount-label">VIP</span>
            </div>
            <div class="amount-btn" onclick="selectAmount(2000000)">
                <span class="amount-value">2.000.000₫</span>
                <span class="amount-label">Premium</span>
            </div>
        </div>
        
        <div class="custom-amount">
            <label for="customAmountInput">Hoặc nhập số tiền khác:</label>
            <input type="number" id="customAmountInput" placeholder="Nhập số tiền (VND)" min="1000" step="1000">
            <small class="amount-hint">Số tiền tối thiểu: 1.000₫</small>
        </div>
        
        <div class="payment-methods">
            <div class="payment-method-header">
                <i class="fa fa-credit-card"></i>
                <span>Phương thức thanh toán</span>
            </div>
            <div class="payment-options">
                <div class="payment-option active">
                    <i class="fa fa-university"></i>
                    <span>Ngân hàng / Ví điện tử</span>
                </div>
            </div>
            <div class="connection-test">
                <button type="button" class="btn-test-connection" onclick="testConnectionManual()">
                    <i class="fa fa-wifi"></i> Kiểm tra kết nối
                </button>
                <span id="connectionStatus"></span>
            </div>
        </div>
        
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="hideAddBalanceModal()">
                <i class="fa fa-times"></i> Hủy
            </button>
            <button class="btn-modal-confirm" onclick="confirmAddBalance()">
                <i class="fa fa-credit-card"></i> Thanh toán ngay
            </button>
        </div>
    </div>

    <!-- Custom CSS for Profile Tabs -->
    <style>
        .profile-nav-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 20px;
        }

        .profile-tabs {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 30px;
        }

        .profile-tabs li {
            margin-bottom: -2px;
        }

        .profile-tabs li a {
            text-decoration: none;
            border: none !important;
            border-radius: 8px 8px 0 0;
            color: #666;
            padding: 15px 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            background: #f8f9fa;
        }

        .profile-tabs li a:hover,
        .profile-tabs li a:focus {
            background: #412d3b;
            color: #deccca;
            border: none !important;
        }

        .profile-tabs li.active a,
        .profile-tabs li.active a:hover,
        .profile-tabs li.active a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .profile-tabs > li.active > a,
        .profile-tabs > li.active > a:hover,
        .profile-tabs > li.active > a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .profile-tabs li a i {
            font-size: 18px;
        }

        .profile-tabs li a span {
            font-weight: 500;
        }

        .profile-content {
            padding-top: 20px;
        }

        .content-wrapper {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            min-height: 400px;
        }

        .content-wrapper h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #deccca;
            display: inline-block;
            font-size: 24px;
        }

        /* Voucher Cards Styles */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .vouchers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .voucher-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
            border-left: 5px solid #ff6b35;
        }

        .voucher-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .voucher-card.used {
            opacity: 0.7;
            border-left-color: #999;
        }

        .voucher-card.expired {
            opacity: 0.6;
            border-left-color: #dc3545;
        }

        .voucher-card.inactive {
            opacity: 0.5;
            border-left-color: #6c757d;
        }

        .voucher-header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .voucher-card.used .voucher-header {
            background: linear-gradient(135deg, #999, #777);
        }

        .voucher-card.expired .voucher-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .voucher-card.inactive .voucher-header {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .voucher-type {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .status-available {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-used {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-expired {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-inactive {
            background: rgba(255,255,255,0.2);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .voucher-body {
            padding: 20px;
        }

        .voucher-title {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }

        .voucher-description {
            color: #666;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
        }

        .voucher-value {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 10px;
        }

        .discount-value {
            font-size: 24px;
            font-weight: 700;
            color: #ff6b35;
        }

        .discount-label {
            color: #666;
            font-size: 14px;
        }

        .min-order {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }

        .voucher-footer {
            border-top: 1px solid #f0f0f0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .voucher-dates {
            color: #888;
        }

        .voucher-code {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .voucher-code code {
            background: #f8f9fa;
            color: #333;
            padding: 6px 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            border: 1px solid #ddd;
        }

        .copy-code-btn {
            background:#deccca;
            color: white;
            border: none;
            padding: 6px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .copy-code-btn:hover {
            background: #deccca;
        }

        .copy-code-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .copy-code-btn:disabled:hover {
            background: #ccc;
        }

        .date-expired {
            color: #deccca;
            font-weight: 600;
        }

        .used-date {
            background: #f8f9fa;
            padding: 10px 20px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
        }

        .inactive-date {
            background: #f8f9fa;
            padding: 10px 20px;
            text-align: center;
            color: #6c757d;
            border-top: 1px solid #eee;
            font-style: italic;
        }

        /* Profile Info Styles */
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-edit {
            background: #deccca;
            color: #412d3b;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .btn-edit:hover {
            background: #412d3b;
            color: #deccca;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            background: white;
            padding: 12px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 6px;
            color: #555;
            font-size: 16px;
        }

        /* Edit Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 12px 15px;
            border: 2px solid #f0f0f0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        /* Address textarea styles */
        .form-control[rows] {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        .btn-save {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .btn-save:hover {
            background: #218838;
        }

        /* Wallet Styles */
        .wallet-info {
            background: linear-gradient(135deg, #412D3B 0%, rgb(255, 236, 234) 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .wallet-info::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: rotate(45deg);
        }

        .wallet-info .info-label {
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
            position: relative;
            z-index: 2;
        }

        .wallet-balance {
            display: flex !important;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }

        .wallet-balance i {
            font-size: 24px;
            color: #ffd700;
        }

        .btn-add-balance {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            position: relative;
            z-index: 2;
            font-size: 14px;
        }

        .btn-add-balance:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }

        /* Add Balance Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            display: none;
        }

        .add-balance-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            z-index: 1060;
            display: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h4 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #999;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: #333;
        }

        .payment-notice {
            display: flex;
            gap: 12px;
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .notice-icon {
            color: #1976d2;
            font-size: 20px;
            margin-top: 2px;
        }

        .notice-text {
            color: #1565c0;
            font-size: 14px;
            line-height: 1.4;
        }

        .amount-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .amount-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            padding: 15px 10px;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
            overflow: hidden;
        }

        .amount-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s ease;
        }

        .amount-btn:hover::before {
            left: 100%;
        }

        .amount-btn:hover {
            background: #deccca;
            color: #412d3b;
            border-color: #deccca;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
        }

        .amount-btn.active {
            background: #deccca;
            color: #412d3b;
            border-color: #deccca;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.3);
        }

        .amount-value {
            font-weight: 700;
            font-size: 16px;
        }

        .amount-label {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-hint {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            font-style: italic;
        }

        .payment-methods {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .payment-method-header {
            background: #f8f9fa;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #e9ecef;
        }

        .payment-options {
            padding: 15px;
        }

        .payment-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
        }

        .payment-option.active {
            border-color: #412d3b;
            background: #deccca;
            color: #412d3b;
        }

        .payment-option i {
            font-size: 16px;
        }

        .custom-amount {
            margin-bottom: 25px;
        }

        .custom-amount label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .custom-amount input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
        }

        .custom-amount input:focus {
            outline: none;
            border-color: #deccca;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-modal-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-modal-cancel:hover {
            background: #5a6268;
        }

        .btn-modal-confirm {
            background: #deccca;
            color: #412d3b;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-modal-confirm:hover {
            background: #412d3b;        
            color: #deccca;
        }
        
        /* Connection test styles */
        .connection-test {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-test-connection {
            background: #f8f9fa;
            border: 1px solid #ddd;
            color: #666;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .btn-test-connection:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .btn-test-connection:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        #connectionStatus {
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-testing {
            color: #007bff;
        }
        
        .status-success {
            color: #28a745;
        }
        
        .status-warning {
            color: #ffc107;
        }
        
        .status-error {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .profile-tabs li a {
                padding: 12px 15px;
                font-size: 14px;
            }
            
            .profile-tabs li a span {
                display: none;
            }
            
            .profile-tabs li a i {
                font-size: 18px;
            }

            .vouchers-grid {
                grid-template-columns: 1fr;
            }

            .voucher-footer {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }

            .amount-options {
                grid-template-columns: repeat(2, 1fr);
            }

            .add-balance-modal {
                padding: 20px;
                width: 95%;
            }
        }

        /* Invoice Styles */
        .invoice-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .invoice-filters select,
        .invoice-filters input {
            padding: 10px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            min-width: 150px;
        }

        .invoice-filters select:focus,
        .invoice-filters input:focus {
            outline: none;
            border-color: #deccca;
            box-shadow: 0 0 0 3px rgba(255, 57, 57, 0.1);
        }

        .btn-refresh {
            padding: 10px 20px;
            background: #deccca;
            color: #412d3b;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-refresh:hover {
            background: #412d3b;
            color: #deccca;
            box-shadow: 0 4px 12px rgba(65, 45, 59, 0.2);
        }

        .btn-refresh i {
            font-size: 16px;
        }

        .invoice-stats {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .invoices-container {
            min-height: 400px;
        }

        .invoice-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .invoice-id {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .invoice-date {
            color: #666;
            font-size: 14px;
        }

        .invoice-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-0 { background: #e3f2fd; color: #1976d2; }
        .status-1 { background: #fff3e0; color: #f57c00; }
        .status-2 { background: #e8f5e8; color: #388e3c; }
        .status-3 { background: #e8f5e8; color: #2e7d32; }
        .status--1 { background: #ffebee; color: #d32f2f; }

        .invoice-body {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            align-items: center;
        }

        .invoice-info {
            display: flex;
            flex-direction: column;
        }

        .invoice-label {
            font-size: 12px;
            color: #888;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .invoice-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .invoice-total {
            text-align: right;
        }

        .invoice-total {
            font-size: 18px;
            color: #deccca;
        }
        .invoice-value {
            font-size: 18px;
            color: #412d3b;
        }

        .invoice-items-preview {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .items-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .item-preview {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
        }

        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        .no-invoices {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .no-invoices i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ddd;
        }

        .pagination-wrapper {
            margin-top: 30px;
            text-align: center;
        }

        .pagination {
            display: inline-flex;
            gap: 5px;
        }

        .page-btn {
            padding: 8px 12px;
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            cursor: pointer;
            color: #cc8889;
            text-decoration: none;
        }

        .page-btn:hover,
        .page-btn.active {
            background: #deccca;
            border-color: #cc8889;
            color: #412d3b;
        }

        .page-btn.disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .invoice-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .invoice-filters select,
            .invoice-filters input {
                min-width: auto;
            }

            .invoice-body {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .invoice-total {
                text-align: left;
            }
        }

        /* Favorites Styles */
        .favorites-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .favorites-filters select {
            padding: 10px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            min-width: 150px;
        }

        .favorites-filters select:focus {
            outline: none;
            border-color: #deccca;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .favorites-stats {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .favorites-container {
            min-height: 400px;
        }

        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .favorite-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }

        .favorite-card:hover {
            border-color: #ff6b35;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.15);
        }

        .favorite-image-container {
            position: relative;
            overflow: hidden;
        }

        .favorite-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .favorite-card:hover .favorite-image {
            transform: scale(1.05);
        }

        .favorite-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #deccca;
            color: #412d3b;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .favorite-info {
            padding: 20px;
        }

        .favorite-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }

        .favorite-price {
            color: #412d3b;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .favorite-stock {
            font-size: 0.9rem;
            margin-bottom: 15px;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 500;
        }

        .favorite-in-stock {
            background: #d4edda;
            color: #155724;
        }

        .favorite-out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }

        .favorite-actions {
            display: flex;
            gap: 8px;
        }

        .btn-favorite-action {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-add-to-cart-fav {
            background: #deccca;
            color: #412d3b;
        }

        .btn-add-to-cart-fav:hover {
            background: #412d3b;
            color: #deccca;
        }

        .btn-remove-favorite {
            background: #6c757d;
            color: white;
        }

        .btn-remove-favorite:hover {
            background: #5a6268;
        }

        .btn-view-detail {
            background: white;
            color: #412d3b;
            border: 2px solid #deccca;
        }

        .btn-view-detail:hover {
            background: #412d3b;
            color: #deccca;
        }

        .favorite-added-date {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
        }

        .no-favorites {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .no-favorites i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .favorites-filters {
                flex-direction: column;
                align-items: stretch;
            }

            .favorites-filters select {
                min-width: auto;
            }

            .favorites-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }

            .favorite-actions {
                flex-direction: column;
            }
        }

        .profile-section h2 {
            font-size: 32px;
            font-weight: 600;
            color: #412d3b;
            margin-bottom: 40px;
        }

        .profile-tabs {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 30px;
        }

        .profile-tabs li {
            margin-bottom: -2px;
        }

        .profile-tabs li a {
            text-decoration: none;
            border: none !important;
            border-radius: 8px 8px 0 0;
            color: #666;
            padding: 15px 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            background: #f8f9fa;
        }

        .profile-tabs li a:hover,
        .profile-tabs li a:focus {
            background: #412d3b;
            color: #deccca;
            border: none !important;
        }

        .profile-tabs li.active a,
        .profile-tabs li.active a:hover,
        .profile-tabs li.active a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .profile-tabs > li.active > a,
        .profile-tabs > li.active > a:hover,
        .profile-tabs > li.active > a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            background: #deccca !important;
            color: #412d3b !important;
            border: none !important;
        }

        .profile-tabs li a i {
            font-size: 18px;
        }

        .profile-tabs li a span {
            font-weight: 500;
        }
    </style>

    <!-- Scripts -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/custom.js"></script>

    <script>
        // Function to copy voucher code
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(function() {
                // Show success message
                alert('Đã copy mã voucher: ' + code);
            }).catch(function(err) {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = code;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Đã copy mã voucher: ' + code);
            });
        }

        // Toggle between view and edit mode
        function toggleEditMode() {
            document.getElementById('viewMode').style.display = 'none';
            document.getElementById('editMode').style.display = 'block';
            document.getElementById('editProfileBtn').style.display = 'none';
        }

        // Cancel edit mode
        function cancelEdit() {
            document.getElementById('viewMode').style.display = 'block';
            document.getElementById('editMode').style.display = 'none';
            document.getElementById('editProfileBtn').style.display = 'flex';
            
            // Reset form values to original
            document.getElementById('editFullName').value = document.getElementById('viewFullName').textContent;
            document.getElementById('editEmail').value = document.getElementById('viewEmail').textContent;
            document.getElementById('editGender').value = document.getElementById('viewGender').textContent;
            document.getElementById('editPhone').value = document.getElementById('viewPhone').textContent;
            document.getElementById('editAddress').value = document.getElementById('viewAddress').textContent === 'Chưa cập nhật' ? '' : document.getElementById('viewAddress').textContent;
        }

        // Wallet Functions
        let selectedAmount = 0;

        // Load user balance when page loads
        function loadUserBalance() {
            console.log('🚀 Loading user balance...');
            
            $.ajax({
                url: 'ajax/wallet_handler.php',
                method: 'POST',
                data: {
                    action: 'get_balance'
                },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Balance loaded:', data);
                    if (data.success) {
                        document.getElementById('balanceAmount').textContent = data.formatted_balance;
                    } else {
                        console.log('❌ Balance error:', data.message);
                        document.getElementById('balanceAmount').textContent = '0₫';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    console.error('Status:', status);
                    document.getElementById('balanceAmount').textContent = 'Lỗi';
                }
            });
        }

        // Show add balance modal
        function showAddBalanceModal() {
            document.getElementById('modalBackdrop').style.display = 'block';
            document.getElementById('addBalanceModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Hide add balance modal
        function hideAddBalanceModal() {
            document.getElementById('modalBackdrop').style.display = 'none';
            document.getElementById('addBalanceModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset form
            selectedAmount = 0;
            document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('customAmountInput').value = '';
        }

        // Select amount button
        function selectAmount(amount) {
            selectedAmount = amount;
            
            // Update button states
            document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Clear custom input
            document.getElementById('customAmountInput').value = '';
        }

        // Confirm add balance
        function confirmAddBalance() {
            let amount = selectedAmount;
            
            // Check if custom amount is used
            const customAmount = document.getElementById('customAmountInput').value;
            if (customAmount && parseInt(customAmount) > 0) {
                amount = parseInt(customAmount);
            }
            
            if (amount <= 0) {
                alert('Vui lòng chọn hoặc nhập số tiền hợp lệ!');
                return;
            }
            
            if (amount < 1000) {
                alert('Số tiền nạp tối thiểu là 1.000₫');
                return;
            }
            
            // Show loading
            const confirmBtn = document.querySelector('.btn-modal-confirm');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang tạo đơn thanh toán...';
            confirmBtn.disabled = true;
            
            // Step 1: Create payment order
            console.log('🔄 Creating payment order for amount:', amount);
            
            // Test connection first, then create payment order
            testAPIConnection()
                .then(() => {
                    console.log('✅ API connection test successful');
                    return createPaymentOrderFetch(amount, confirmBtn, originalText);
                })
                .catch(error => {
                    console.warn('🔄 Fetch failed, trying XMLHttpRequest...', error);
                    
                    // Update button to show we're trying alternative method
                    confirmBtn.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Đang thử phương pháp khác...';
                    
                    // Wait a bit then try XHR
                    setTimeout(() => {
                        createPaymentOrderXHR(amount, confirmBtn, originalText)
                            .catch(xhrError => {
                                console.warn('🔄 XHR also failed, trying PHP proxy...', xhrError);
                                confirmBtn.innerHTML = '<i class="fa fa-server fa-spin"></i> Đang thử proxy...';
                                
                                setTimeout(() => {
                                    createPaymentOrderProxy(amount, confirmBtn, originalText);
                                }, 1000);
                            });
                    }, 1000);
                });
        }
        
        // Test API connection (skip for now, directly try payment)
        function testAPIConnection() {
            return new Promise((resolve, reject) => {
                console.log('🌐 Skipping health check, testing direct API...');
                resolve(); // Always continue without health check
            });
        }
        
        // Create payment order using fetch API
        function createPaymentOrderFetch(amount, confirmBtn, originalText) {
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
                        orderInfo: 'Nạp tiền AuraDisc'
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
                    console.log('✅ Fetch Payment order created:', data);
                    
                    if (data.orderId && data.paymentUrl) {
                        const orderId = data.orderId;
                        confirmBtn.innerHTML = '<i class="fa fa-credit-card"></i> Đang chờ thanh toán...';
                        const paymentWindow = window.open(data.paymentUrl, '_blank');
                        checkPaymentStatus(orderId, amount, confirmBtn, originalText, paymentWindow);
                        resolve(data);
                    } else {
                        throw new Error('Không nhận được thông tin thanh toán từ server. Response: ' + JSON.stringify(data));
                    }
                })
                .catch(error => {
                    console.error('❌ Fetch Payment order error:', error);
                    reject(error);
                });
            });
        }
        
        // Create payment order using XMLHttpRequest (fallback)
        function createPaymentOrderXHR(amount, confirmBtn, originalText) {
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
                    console.log('📡 XHR Response text:', xhr.responseText);
                    
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('✅ XHR Payment order created:', data);
                            
                            if (data.orderId && data.paymentUrl) {
                                const orderId = data.orderId;
                                confirmBtn.innerHTML = '<i class="fa fa-credit-card"></i> Đang chờ thanh toán...';
                                const paymentWindow = window.open(data.paymentUrl, '_blank');
                                checkPaymentStatus(orderId, amount, confirmBtn, originalText, paymentWindow);
                                resolve(data);
                            } else {
                                reject(new Error('Không nhận được thông tin thanh toán từ server. Response: ' + JSON.stringify(data)));
                            }
                        } catch (parseError) {
                            console.error('❌ JSON parse error:', parseError);
                            reject(parseError);
                        }
                    } else {
                        console.error('❌ XHR HTTP error:', xhr.status, xhr.statusText);
                        
                        let errorMessage = 'Không thể kết nối đến server thanh toán';
                        
                        if (xhr.status === 0) {
                            errorMessage = 'Không thể kết nối đến server thanh toán. Vui lòng kiểm tra kết nối mạng.';
                        } else if (xhr.status >= 500) {
                            errorMessage = 'Server thanh toán đang gặp sự cố. Vui lòng thử lại sau.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Không tìm thấy API thanh toán. Vui lòng liên hệ hỗ trợ.';
                        } else {
                            errorMessage = `Lỗi server thanh toán (${xhr.status}): ${xhr.statusText}`;
                        }
                        
                        reject(new Error(errorMessage));
                    }
                }
            };
            
            xhr.onerror = function() {
                console.error('❌ XHR Network error');
                reject(new Error('Lỗi mạng khi kết nối đến server thanh toán'));
            };
            
            xhr.ontimeout = function() {
                console.error('❌ XHR Timeout');
                reject(new Error('Hết thời gian chờ kết nối đến server thanh toán'));
            };
            
            xhr.timeout = 30000; // 30 seconds timeout
            
            const requestData = JSON.stringify({
                amount: amount,
                orderInfo: 'Nạp tiền AuraDisc'
            });
            
            console.log('📤 XHR Sending request:', requestData);
            
            try {
                xhr.send(requestData);
            } catch (sendError) {
                console.error('❌ XHR Send error:', sendError);
                reject(sendError);
            }
            
            }); // End of Promise
        }
        
        // Create payment order using PHP proxy (final fallback)
        function createPaymentOrderProxy(amount, confirmBtn, originalText) {
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
                    orderInfo: 'Nạp tiền AuraDisc'
                })
            })
            .then(response => {
                console.log('📡 Proxy Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('✅ Proxy Payment order created:', data);
                
                if (data.error) {
                    throw new Error('Proxy Error: ' + data.error);
                }
                
                if (data.orderId && data.paymentUrl) {
                    const orderId = data.orderId;
                    confirmBtn.innerHTML = '<i class="fa fa-credit-card"></i> Đang chờ thanh toán...';
                    const paymentWindow = window.open(data.paymentUrl, '_blank');
                    checkPaymentStatusProxy(orderId, amount, confirmBtn, originalText, paymentWindow);
                } else {
                    throw new Error('Không nhận được thông tin thanh toán từ proxy. Response: ' + JSON.stringify(data));
                }
            })
            .catch(error => {
                console.error('❌ All payment methods failed:', error);
                
                let errorMessage = 'Tất cả phương pháp thanh toán đều thất bại. ';
                
                if (error.message.includes('Proxy Error')) {
                    errorMessage += 'Lỗi từ server proxy: ' + error.message;
                } else {
                    errorMessage += 'Vui lòng kiểm tra kết nối mạng và thử lại sau.';
                }
                
                alert('❌ ' + errorMessage);
                
                // Reset button
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            });
        }
        
        // Check payment status using proxy
        function checkPaymentStatusProxy(orderId, amount, confirmBtn, originalText, paymentWindow) {
            console.log('🔍 Checking payment status via proxy for order:', orderId);
            
            const maxAttempts = 60;
            let attempts = 0;
            
            const checkInterval = setInterval(() => {
                attempts++;
                
                const remainingTime = Math.max(0, maxAttempts - attempts);
                confirmBtn.innerHTML = `<i class="fa fa-clock-o"></i> Kiểm tra thanh toán... (${remainingTime}s)`;
                
                fetch('ajax/payment_proxy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'check_status',
                        orderId: orderId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('💳 Payment status via proxy:', data);
                    
                    if (data.error) {
                        console.error('❌ Proxy status check error:', data.error);
                        return;
                    }
                    
                    if (data.status === 'PAID') {
                        clearInterval(checkInterval);
                        
                        if (paymentWindow && !paymentWindow.closed) {
                            paymentWindow.close();
                        }
                        
                        confirmBtn.innerHTML = '<i class="fa fa-check"></i> Đang cập nhật số dư...';
                        updateUserBalance(amount, orderId, confirmBtn, originalText);
                        
                    } else if (data.status === 'UNPAID') {
                        console.log('⏳ Payment still pending via proxy...');
                        
                        if (paymentWindow && paymentWindow.closed) {
                            clearInterval(checkInterval);
                            confirmBtn.innerHTML = originalText;
                            confirmBtn.disabled = false;
                            alert('⚠️ Bạn đã đóng cửa sổ thanh toán. Vui lòng thử lại nếu muốn nạp tiền.');
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ Proxy status check error:', error);
                });
                
                if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    
                    if (paymentWindow && !paymentWindow.closed) {
                        paymentWindow.close();
                    }
                    
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.disabled = false;
                    
                    alert('⏰ Hết thời gian chờ thanh toán. Vui lòng thử lại hoặc kiểm tra lại giao dịch.');
                }
                
            }, 5000);
        }
        
        // Manual connection test
        function testConnectionManual() {
            const statusEl = document.getElementById('connectionStatus');
            const testBtn = document.querySelector('.btn-test-connection');
            
            statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang kiểm tra...';
            statusEl.className = 'status-testing';
            testBtn.disabled = true;
            
            // Test both fetch and XHR
            Promise.all([
                testFetchConnection(),
                testXHRConnection()
            ])
            .then(results => {
                const [fetchResult, xhrResult] = results;
                
                if (fetchResult.success || xhrResult.success) {
                    statusEl.innerHTML = '<i class="fa fa-check-circle"></i> Kết nối tốt';
                    statusEl.className = 'status-success';
                } else {
                    statusEl.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Có vấn đề kết nối';
                    statusEl.className = 'status-warning';
                }
                
                console.log('📊 Connection test results:', { fetchResult, xhrResult });
            })
            .catch(error => {
                statusEl.innerHTML = '<i class="fa fa-times-circle"></i> Lỗi kết nối';
                statusEl.className = 'status-error';
                console.error('❌ Connection test error:', error);
            })
            .finally(() => {
                testBtn.disabled = false;
                
                // Clear status after 5 seconds
                setTimeout(() => {
                    statusEl.innerHTML = '';
                    statusEl.className = '';
                }, 5000);
            });
        }
        
        function testFetchConnection() {
            return new Promise((resolve) => {
                // Test with a simple OPTIONS request to check CORS
                fetch('https://duc-spring.ngodat0103.live/demo/api/app/order', {
                    method: 'OPTIONS',
                    mode: 'cors',
                    headers: {
                        'Access-Control-Request-Method': 'POST',
                        'Access-Control-Request-Headers': 'Content-Type',
                    },
                })
                .then(response => {
                    console.log('📡 Fetch OPTIONS test:', response.status, response.headers);
                    resolve({ method: 'fetch', success: response.status < 400, status: response.status });
                })
                .catch(error => {
                    console.log('❌ Fetch test error:', error);
                    resolve({ method: 'fetch', success: false, error: error.message });
                });
            });
        }
        
        function testXHRConnection() {
            return new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('OPTIONS', 'https://duc-spring.ngodat0103.live/demo/api/app/order', true);
                xhr.timeout = 10000;
                xhr.setRequestHeader('Access-Control-Request-Method', 'POST');
                xhr.setRequestHeader('Access-Control-Request-Headers', 'Content-Type');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        console.log('📡 XHR OPTIONS test:', xhr.status, xhr.getAllResponseHeaders());
                        resolve({ method: 'xhr', success: xhr.status < 400, status: xhr.status });
                    }
                };
                
                xhr.onerror = function() {
                    console.log('❌ XHR test error');
                    resolve({ method: 'xhr', success: false, error: 'Network error' });
                };
                
                xhr.ontimeout = function() {
                    console.log('❌ XHR test timeout');
                    resolve({ method: 'xhr', success: false, error: 'Timeout' });
                };
                
                try {
                    xhr.send();
                } catch (error) {
                    console.log('❌ XHR send error:', error);
                    resolve({ method: 'xhr', success: false, error: error.message });
                }
            });
        }

        // Check payment status function
        function checkPaymentStatus(orderId, amount, confirmBtn, originalText, paymentWindow) {
            console.log('🔍 Checking payment status for order:', orderId);
            
            const maxAttempts = 60; // Kiểm tra trong 5 phút (60 lần x 5 giây)
            let attempts = 0;
            
            const checkInterval = setInterval(() => {
                attempts++;
                
                // Update button text with countdown
                const remainingTime = Math.max(0, maxAttempts - attempts);
                confirmBtn.innerHTML = `<i class="fa fa-clock-o"></i> Kiểm tra thanh toán... (${remainingTime}s)`;
                
                fetch(`https://duc-spring.ngodat0103.live/demo/api/app/order/${orderId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                        'Origin': window.location.origin,
                    },
                    mode: 'cors',
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('💳 Payment status:', data);
                    
                    if (data.status === 'PAID') {
                        // Payment successful
                        clearInterval(checkInterval);
                        
                        // Close payment window if still open
                        if (paymentWindow && !paymentWindow.closed) {
                            paymentWindow.close();
                        }
                        
                        confirmBtn.innerHTML = '<i class="fa fa-check"></i> Đang cập nhật số dư...';
                        
                        // Update balance in database
                        updateUserBalance(amount, orderId, confirmBtn, originalText);
                        
                    } else if (data.status === 'UNPAID') {
                        // Still unpaid, continue checking
                        console.log('⏳ Payment still pending...');
                        
                        // Check if payment window is closed (user canceled)
                        if (paymentWindow && paymentWindow.closed) {
                            clearInterval(checkInterval);
                            confirmBtn.innerHTML = originalText;
                            confirmBtn.disabled = false;
                            alert('⚠️ Bạn đã đóng cửa sổ thanh toán. Vui lòng thử lại nếu muốn nạp tiền.');
                        }
                        
                    } else {
                        // Unknown status
                        console.log('❓ Unknown payment status:', data.status);
                    }
                })
                .catch(error => {
                    console.error('❌ Status check error, trying proxy:', error);
                    
                    // Fallback to proxy for status check
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
                        console.log('💳 Payment status via proxy fallback:', data);
                        
                        if (!data.error && data.status === 'PAID') {
                            clearInterval(checkInterval);
                            
                            if (paymentWindow && !paymentWindow.closed) {
                                paymentWindow.close();
                            }
                            
                            confirmBtn.innerHTML = '<i class="fa fa-check"></i> Đang cập nhật số dư...';
                            updateUserBalance(amount, orderId, confirmBtn, originalText);
                        }
                    })
                    .catch(proxyError => {
                        console.error('❌ Proxy status check also failed:', proxyError);
                    });
                });
                
                // Timeout after max attempts
                if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    
                    // Close payment window if still open
                    if (paymentWindow && !paymentWindow.closed) {
                        paymentWindow.close();
                    }
                    
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.disabled = false;
                    
                    alert('⏰ Hết thời gian chờ thanh toán. Vui lòng thử lại hoặc kiểm tra lại giao dịch.');
                }
                
            }, 5000); // Check every 5 seconds
        }

        // Update user balance in database
        function updateUserBalance(amount, orderId, confirmBtn, originalText) {
            $.ajax({
                url: 'ajax/wallet_handler.php',
                method: 'POST',
                data: {
                    action: 'add_balance',
                    amount: amount,
                    description: `Nạp tiền qua cổng thanh toán - Order: ${orderId}`,
                    order_id: orderId
                },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Balance updated:', data);
                    
                    if (data.success) {
                        // Update balance display
                        document.getElementById('balanceAmount').textContent = data.formatted_balance;
                        
                        // Hide modal
                        hideAddBalanceModal();
                        
                        // Show success message
                        alert('🎉 Nạp tiền thành công!\n' + 
                              '💰 Số tiền: ' + formatCurrency(amount) + '\n' +
                              '💳 Mã giao dịch: ' + orderId + '\n' +
                              '📊 Số dư mới: ' + data.formatted_balance);
                        
                        // Reload balance to make sure it's up to date
                        loadUserBalance();
                        
                    } else {
                        console.log('❌ Balance update error:', data.message);
                        alert('⚠️ Thanh toán thành công nhưng có lỗi khi cập nhật số dư. Vui lòng liên hệ hỗ trợ.\nMã giao dịch: ' + orderId);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ Balance update AJAX error:', error);
                    alert('⚠️ Thanh toán thành công nhưng có lỗi khi cập nhật số dư. Vui lòng liên hệ hỗ trợ.\nMã giao dịch: ' + orderId);
                },
                complete: function() {
                    // Reset button
                    confirmBtn.innerHTML = originalText;
                    confirmBtn.disabled = false;
                }
            });
        }

        // Invoice Functions
        let currentPage = 1;
        let currentFilters = {};
        
        // Favorites Functions
        let currentFavoritesPage = 1;
        let currentFavoritesFilters = {};

        function loadInvoices(page = 1) {
            console.log('🚀 Loading invoices...');
            currentPage = page;
            
            const container = document.getElementById('invoicesContainer');
            container.innerHTML = '<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i> Đang tải hóa đơn...</div>';
            
            const filters = {
                status: document.getElementById('invoiceStatusFilter').value,
                date: document.getElementById('invoiceDateFilter').value,
                page: page
            };
            currentFilters = filters;
            
            $.ajax({
                url: 'ajax/invoice_handler.php',
                method: 'POST',
                data: {
                    action: 'get_invoices',
                    ...filters
                },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Response received:', data);
                    
                    // If this is test data, just show success message
                    if (data.test) {
                        container.innerHTML = '<div class="no-invoices"><i class="fa fa-check"></i><p>✅ ' + data.message + '</p></div>';
                        return;
                    }
                    
                    // Normal invoice processing
                    if (data.success) {
                        displayInvoices(data.invoices, data.pagination);
                        document.getElementById('totalInvoices').textContent = data.total;
                    } else {
                        container.innerHTML = '<div class="no-invoices"><i class="fa fa-file-text-o"></i><p>' + data.message + '</p></div>';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    container.innerHTML = '<div class="no-invoices"><i class="fa fa-exclamation-triangle"></i><p>Có lỗi xảy ra khi tải hóa đơn!</p></div>';
                }
            });
        }

        function displayInvoices(invoices, pagination) {
            const container = document.getElementById('invoicesContainer');
            
            if (invoices.length === 0) {
                container.innerHTML = '<div class="no-invoices"><i class="fa fa-file-text-o"></i><p>Chưa có hóa đơn nào!</p></div>';
                return;
            }
            
            let html = '';
            invoices.forEach(invoice => {
                const statusNames = {
                    '0': 'Đặt hàng',
                    '1': 'Xử lý', 
                    '2': 'Vận chuyển',
                    '3': 'Hoàn thành',
                    '-1': 'Đã hủy'
                };
                
                // Đảm bảo status là string
                const statusKey = String(invoice.status);
                
                html += `
                    <div class="invoice-card">
                        <div class="invoice-header">
                            <div>
                                <div class="invoice-id">#${invoice.id}</div>
                                <div class="invoice-date">${invoice.created_at}</div>
                            </div>
                            <div class="invoice-status status-${statusKey}">
                                ${statusNames[statusKey] || 'Không xác định'}
                            </div>
                        </div>
                        
                        <div class="invoice-body">
                            <div class="invoice-info">
                                <div class="invoice-label">Số sản phẩm</div>
                                <div class="invoice-value">${invoice.total_items} món</div>
                            </div>
                            
                            <div class="invoice-info">
                                <div class="invoice-label">Phương thức thanh toán</div>
                                <div class="invoice-value">${invoice.payment_method}</div>
                            </div>
                            
                            <div class="invoice-info invoice-total">
                                <div class="invoice-label">Tổng tiền</div>
                                <div class="invoice-value">${formatCurrency(invoice.total_amount)}</div>
                            </div>
                        </div>
                        
                        ${invoice.items_preview ? `
                        <div class="invoice-items-preview">
                            <div class="items-preview">
                                ${invoice.items_preview.split(',').map(item => `<span class="item-preview">${item.trim()}</span>`).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Update pagination
            displayPagination(pagination);
        }

        function displayPagination(pagination) {
            const paginationContainer = document.getElementById('invoicesPagination');
            
            if (pagination.total_pages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let html = '<div class="pagination">';
            
            // Previous button
            if (pagination.current_page > 1) {
                html += `<a href="#" class="page-btn" onclick="loadInvoices(${pagination.current_page - 1}); return false;">‹</a>`;
            } else {
                html += `<span class="page-btn disabled">‹</span>`;
            }
            
            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page) {
                    html += `<span class="page-btn active">${i}</span>`;
                } else {
                    html += `<a href="#" class="page-btn" onclick="loadInvoices(${i}); return false;">${i}</a>`;
                }
            }
            
            // Next button
            if (pagination.current_page < pagination.total_pages) {
                html += `<a href="#" class="page-btn" onclick="loadInvoices(${pagination.current_page + 1}); return false;">›</a>`;
            } else {
                html += `<span class="page-btn disabled">›</span>`;
            }
            
            html += '</div>';
            paginationContainer.innerHTML = html;
        }

        function filterInvoices() {
            loadInvoices(1);
        }



        function formatCurrency(amount) {
            // Chuyển đổi string thành number nếu cần
            const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
            return new Intl.NumberFormat('vi-VN', { 
                style: 'currency', 
                currency: 'VND',
                minimumFractionDigits: 0
            }).format(numAmount);
        }

        // Favorites Functions
        function loadFavorites(page = 1) {
            console.log('🚀 Loading favorites...');
            currentFavoritesPage = page;
            
            const container = document.getElementById('favoritesContainer');
            container.innerHTML = '<div class="loading-spinner"><i class="fa fa-spinner fa-spin"></i> Đang tải sản phẩm yêu thích...</div>';
            
            const filters = {
                item_type: document.getElementById('favoritesTypeFilter').value,
                page: page
            };
            currentFavoritesFilters = filters;
            
            $.ajax({
                url: 'ajax/wishlist_handler.php',
                method: 'POST',
                data: {
                    action: 'get_wishlist',
                    ...filters
                },
                dataType: 'json',
                success: function(data) {
                    console.log('✅ Favorites response received:', data);
                    
                    if (data.success) {
                        displayFavorites(data.wishlist_items, data.pagination);
                        document.getElementById('totalFavorites').textContent = data.total;
                    } else {
                        container.innerHTML = '<div class="no-favorites"><i class="fa fa-heart-o"></i><p>' + data.message + '</p></div>';
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    container.innerHTML = '<div class="no-favorites"><i class="fa fa-exclamation-triangle"></i><p>Có lỗi xảy ra khi tải danh sách yêu thích!</p></div>';
                }
            });
        }

        function displayFavorites(favorites, pagination) {
            const container = document.getElementById('favoritesContainer');
            
            if (favorites.length === 0) {
                container.innerHTML = '<div class="no-favorites"><i class="fa fa-heart-o"></i><p>Chưa có sản phẩm yêu thích nào!</p><small>Hãy thêm sản phẩm vào danh sách yêu thích để dễ dàng tìm lại sau này.</small></div>';
                return;
            }
            
            let html = '<div class="favorites-grid">';
            favorites.forEach(favorite => {
                const typeText = favorite.item_type === 'product' ? 'Album' : 'Phụ kiện';
                const stockClass = favorite.stock > 0 ? 'favorite-in-stock' : 'favorite-out-of-stock';
                const stockText = favorite.stock > 0 ? `Còn ${favorite.stock} sản phẩm` : 'Hết hàng';
                
                html += `
                    <div class="favorite-card">
                        <div class="favorite-image-container">
                            <img src="${favorite.image_url || 'https://via.placeholder.com/280x200?text=No+Image'}" 
                                 alt="${favorite.item_name}" 
                                 class="favorite-image"
                                 onerror="this.src='https://via.placeholder.com/280x200?text=No+Image'">
                            <div class="favorite-type-badge">${typeText}</div>
                            <div class="favorite-added-date">${favorite.added_at_formatted}</div>
                        </div>
                        
                        <div class="favorite-info">
                            <h4 class="favorite-name">${favorite.item_name}</h4>
                            <div class="favorite-price">${favorite.price_formatted}</div>
                            <div class="favorite-stock ${stockClass}">${stockText}</div>
                            
                            <div class="favorite-actions">
                                ${favorite.stock > 0 ? 
                                    `<button class="btn-favorite-action btn-add-to-cart-fav" onclick="addToCartFromFavorites(${favorite.product_id}, '${favorite.item_type}')">
                                        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>` : 
                                    `<button class="btn-favorite-action btn-add-to-cart-fav" disabled style="opacity: 0.6; cursor: not-allowed;">
                                        <i class="fa fa-ban"></i> Hết hàng
                                    </button>`
                                }
                                <button class="btn-favorite-action btn-view-detail" onclick="viewProductDetail(${favorite.product_id}, '${favorite.item_type}')">
                                    <i class="fa fa-eye"></i> Xem
                                </button>
                                <button class="btn-favorite-action btn-remove-favorite" onclick="removeFromFavorites(${favorite.product_id}, '${favorite.item_type}')">
                                    <i class="fa fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
            
            // Update pagination
            displayFavoritesPagination(pagination);
        }

        function displayFavoritesPagination(pagination) {
            const paginationContainer = document.getElementById('favoritesPagination');
            
            if (pagination.total_pages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }
            
            let html = '<div class="pagination">';
            
            // Previous button
            if (pagination.current_page > 1) {
                html += `<a href="#" class="page-btn" onclick="loadFavorites(${pagination.current_page - 1}); return false;">‹</a>`;
            } else {
                html += `<span class="page-btn disabled">‹</span>`;
            }
            
            // Page numbers
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page) {
                    html += `<span class="page-btn active">${i}</span>`;
                } else {
                    html += `<a href="#" class="page-btn" onclick="loadFavorites(${i}); return false;">${i}</a>`;
                }
            }
            
            // Next button
            if (pagination.current_page < pagination.total_pages) {
                html += `<a href="#" class="page-btn" onclick="loadFavorites(${pagination.current_page + 1}); return false;">›</a>`;
            } else {
                html += `<span class="page-btn disabled">›</span>`;
            }
            
            html += '</div>';
            paginationContainer.innerHTML = html;
        }

        function filterFavorites() {
            loadFavorites(1);
        }

        function removeFromFavorites(productId, itemType) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                return;
            }
            
            $.ajax({
                url: 'ajax/wishlist_handler.php',
                method: 'POST',
                data: {
                    action: 'remove_from_wishlist',
                    item_id: productId,
                    item_type: itemType
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Reload current page
                        loadFavorites(currentFavoritesPage);
                        // Show success message
                        alert('✅ ' + data.message);
                    } else {
                        alert('❌ ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    alert('❌ Có lỗi xảy ra khi xóa sản phẩm!');
                }
            });
        }

        function addToCartFromFavorites(productId, itemType) {
            $.ajax({
                url: 'ajax/cart_handler.php',
                method: 'POST',
                data: {
                    action: 'add_to_cart',
                    item_id: productId,
                    item_type: itemType,
                    quantity: 1
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Update cart badge if it exists
                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge(data.total_items);
                        } else if (document.getElementById('cart-badge')) {
                            document.getElementById('cart-badge').textContent = data.total_items;
                        }
                        
                        alert('✅ ' + data.message);
                    } else {
                        alert('❌ ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', error);
                    alert('❌ Có lỗi xảy ra khi thêm vào giỏ hàng!');
                }
            });
        }

        function viewProductDetail(productId, itemType) {
            // Mở trang chi tiết sản phẩm trong tab mới
            const url = `product-detail.php?type=${itemType}&id=${productId}`;
            window.open(url, '_blank');
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            // Load balance on page load
            loadUserBalance();
            
            // Load invoices when invoices tab is activated
            $('a[href="#invoices"]').on('shown.bs.tab', function() {
                loadInvoices(1);
            });
            
            // Load favorites when favorites tab is activated
            $('a[href="#favorites"]').on('shown.bs.tab', function() {
                loadFavorites(1);
            });
            
            // Handle custom amount input
            document.getElementById('customAmountInput').addEventListener('input', function() {
                if (this.value) {
                    selectedAmount = 0;
                    document.querySelectorAll('.amount-btn').forEach(btn => btn.classList.remove('active'));
                }
            });
            
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'update_profile');
                
                // Show loading state
                const saveBtn = document.querySelector('.btn-save');
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
                saveBtn.disabled = true;
                
                fetch('ajax/update_profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update view mode with new values
                        document.getElementById('viewFullName').textContent = formData.get('full_name');
                        document.getElementById('viewEmail').textContent = formData.get('email');
                        document.getElementById('viewGender').textContent = formData.get('gender');
                        document.getElementById('viewPhone').textContent = formData.get('phone');
                        const addressValue = formData.get('address');
                        document.getElementById('viewAddress').textContent = addressValue ? addressValue : 'Chưa cập nhật';
                        
                        // Switch back to view mode
                        document.getElementById('viewMode').style.display = 'block';
                        document.getElementById('editMode').style.display = 'none';
                        document.getElementById('editProfileBtn').style.display = 'flex';
                        
                        alert('Cập nhật thông tin thành công!');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật thông tin!');
                })
                .finally(() => {
                    // Reset button state
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                });
            });
        });
    </script>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Include Chat Widget -->
    <?php include 'includes/chat-widget.php'; ?>

    <!-- Chat Widget CSS -->
    <link rel="stylesheet" href="includes/chat-widget.css">

    <!-- Chat Widget JS -->
    <script src="includes/chat-widget.js"></script>
</body>
</html>
<?php endif; ?>
