<?php
$currentPage = 'complaints';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Ensure SuperAdmin
if (!isSuperAdmin()) {
    header("Location: /WEB_MXH/login.php");
    exit;
}

// Handle Resolution Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $complaintId = $_POST['complaint_id'];
    $note = $_POST['resolution_note'] ?? '';
    $status = '';
    
    if ($_POST['action'] === 'resolve_buyer') {
        $status = 'resolved_buyer';
    } elseif ($_POST['action'] === 'resolve_freelancer') {
        $status = 'resolved_freelancer';
    } elseif ($_POST['action'] === 'reject') {
        $status = 'rejected';
    }
    
    if ($status) {
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, resolution_note = ? WHERE complaint_id = ?");
        $stmt->bind_param("ssi", $status, $note, $complaintId);
        $stmt->execute();
    }
    
    header("Location: index.php");
    exit;
}

// Fetch Complaints
// Mock Data for UI Demo
$complaints = [
    [
        'complaint_id' => 1,
        'complaint_code' => 'CP-2023-001',
        'project_id' => 101,
        'title' => 'Freelancer không giao bài đúng hạn',
        'role_id' => 2, // Buyer
        'full_name' => 'Nguyễn Văn A',
        'status' => 'pending',
        'description' => 'Đã quá hạn 3 ngày nhưng tôi chưa nhận được phản hồi từ freelancer. Tôi yêu cầu hoàn tiền hoặc giao bài ngay lập tức.',
        'resolution_note' => ''
    ],
    [
        'complaint_id' => 2,
        'complaint_code' => 'CP-2023-002',
        'project_id' => 105,
        'title' => 'Sản phẩm không đúng mô tả',
        'role_id' => 2, // Buyer
        'full_name' => 'Trần Thị B',
        'status' => 'pending',
        'description' => 'Logo thiết kế không giống style đã cam kết trong hợp đồng. Yêu cầu sửa lại.',
        'resolution_note' => ''
    ],
    [
        'complaint_id' => 3,
        'complaint_code' => 'CP-2023-003',
        'project_id' => 110,
        'title' => 'Khách hàng không thanh toán',
        'role_id' => 1, // Freelancer
        'full_name' => 'Lê Văn C',
        'status' => 'resolved_buyer',
        'description' => 'Tôi đã giao file đầy đủ nhưng khách hàng kiếm cớ không thanh toán số tiền còn lại.',
        'resolution_note' => 'Đã kiểm tra log chat, freelancer chưa hoàn thành yêu cầu chỉnh sửa cuối cùng. Yêu cầu freelancer hoàn thiện.'
    ]
];

/* Database logic commented out for UI Demo
$checkTable = $conn->query("SHOW TABLES LIKE 'complaints'");
if ($checkTable && $checkTable->num_rows > 0) {
    // Join with users to get complainer info
    $sql = "SELECT c.*, u.full_name, u.role_id 
            FROM complaints c 
            LEFT JOIN users u ON c.user_id = u.user_id 
            ORDER BY c.created_at DESC";
    $result = $conn->query($sql);
    if ($result) {
        $complaints = $result->fetch_all(MYSQLI_ASSOC);
    }
}
*/

function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return '<span class="badge bg-warning text-dark">Chưa xử lý</span>';
        case 'resolved_buyer': return '<span class="badge bg-success">Thắng: Người mua</span>';
        case 'resolved_freelancer': return '<span class="badge bg-primary">Thắng: Freelancer</span>';
        case 'rejected': return '<span class="badge bg-danger">Đã từ chối</span>';
        default: return '<span class="badge bg-secondary">Unknown</span>';
    }
}

function getSource($roleId) {
    return ($roleId == 1) ? 'Freelancer' : 'Người mua'; // Simplified assumption
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quản lý khiếu nại - UniWork SuperAdmin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- CSS Includes -->
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
        .action-btn { cursor: pointer; font-size: 1.1rem; color: #EB1616; border: none; background: transparent; }
        .action-btn:hover { color: #a50e0e; }
        .modal-header { border-bottom: 1px solid #444; }
        .modal-footer { border-top: 1px solid #444; }
        .form-control-dark { background-color: #000; color: white; border: 1px solid #444; }
        .form-control-dark:focus { background-color: #222; color: white; border-color: #EB1616; }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Sidebar -->
        <?php include '../../includes/sidebar.php'; ?>

        <div class="content">
            <!-- Navbar -->
            <?php include '../../includes/navbar.php'; ?>

            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Quản lý khiếu nại</h6>
                    </div>

                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-white">
                                    <th scope="col">Mã khiếu nại</th>
                                    <th scope="col">ID dự án</th>
                                    <th scope="col">Tiêu đề</th>
                                    <th scope="col">Nguồn</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($complaints) > 0): ?>
                                    <?php foreach($complaints as $complaint): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($complaint['complaint_code']) ?></td>
                                        <td>#<?= htmlspecialchars($complaint['project_id']) ?></td>
                                        <td><?= htmlspecialchars($complaint['title']) ?></td>
                                        <td><?= getSource($complaint['role_id']) ?> <br> <small class="text-muted"><?= htmlspecialchars($complaint['full_name']) ?></small></td>
                                        <td><?= getStatusBadge($complaint['status']) ?></td>
                                        <td>
                                            <button type="button" class="action-btn" data-bs-toggle="modal" data-bs-target="#resolveModal<?= $complaint['complaint_id'] ?>">
                                                <i class="fa fa-gavel"></i> Xử lý
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Resolution Modal -->
                                    <div class="modal fade" id="resolveModal<?= $complaint['complaint_id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content bg-secondary text-white">
                                                <form action="" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Xử lý khiếu nại: <?= htmlspecialchars($complaint['complaint_code']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Nội dung khiếu nại:</label>
                                                            <div class="p-3 bg-dark rounded border border-secondary mb-2">
                                                                <?= nl2br(htmlspecialchars($complaint['description'])) ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if($complaint['status'] == 'pending'): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Phản hồi / Ghi chú xử lý:</label>
                                                                <textarea class="form-control" name="resolution_note" rows="4" placeholder="Nhập lý do xử lý hoặc phản hồi..." required></textarea>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Kết quả xử lý:</label>
                                                                <div class="p-3 bg-dark rounded border border-secondary">
                                                                    <?= nl2br(htmlspecialchars($complaint['resolution_note'])) ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <?php if($complaint['status'] == 'pending'): ?>
                                                            <button type="submit" name="action" value="resolve_buyer" class="btn btn-success" onclick="return confirm('Xác nhận xử lý thắng cho Người mua?')">
                                                                <i class="fa fa-user me-2"></i>Thắng: Người mua
                                                            </button>
                                                            <button type="submit" name="action" value="resolve_freelancer" class="btn btn-primary" onclick="return confirm('Xác nhận xử lý thắng cho Freelancer?')">
                                                                <i class="fa fa-id-badge me-2"></i>Thắng: Freelancer
                                                            </button>
                                                            <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Xác nhận từ chối xử lý?')">
                                                                <i class="fa fa-times-circle me-2"></i>Từ chối xử lý
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                        <?php endif; ?>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">Không có khiếu nại nào</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/lib/chart/chart.min.js"></script>
    <script src="/WEB_MXH/admin/lib/easing/easing.min.js"></script>
    <script src="/WEB_MXH/admin/lib/waypoints/waypoints.min.js"></script>
    <script src="/WEB_MXH/admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>
</html>

