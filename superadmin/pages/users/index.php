<?php
$currentPage = 'users';
require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Ensure SuperAdmin
if (!isSuperAdmin()) {
    header("Location: /WEB_MXH/login.php");
    exit;
}

// Handle Actions (Approve/Reject Freelancer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $userId = $_POST['user_id'];
        
        if ($action === 'approve_freelancer') {
            $stmt = $conn->prepare("UPDATE freelancers SET is_verified = 1 WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            // Optional: Notification logic here
        } elseif ($action === 'reject_freelancer') {
            // Delete from freelancers table and revert role to user
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("DELETE FROM freelancers WHERE user_id = ?");
                $stmt1->bind_param("i", $userId);
                $stmt1->execute();
                
                $stmt2 = $conn->prepare("UPDATE users SET role_id = 2 WHERE user_id = ?");
                $stmt2->bind_param("i", $userId);
                $stmt2->execute();
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
            }
        } elseif ($action === 'delete_user') {
            // Check if deleting self
            if ($userId != $_SESSION['user_id']) {
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
            }
        }
        
        // Redirect to avoid resubmission
        header("Location: index.php");
        exit;
    }
}

// Fetch Users for Tab 1
$where = "1=1";
$params = [];
$types = "";

// Search
$search = $_GET['search'] ?? '';
if ($search) {
    $where .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchTerm = "%$search%";
    // bind_param requires references
    $params[] = &$searchTerm;
    $params[] = &$searchTerm;
    $params[] = &$searchTerm;
    $types .= "sss";
}

// Role Filter
$roleFilter = $_GET['role'] ?? '';
if ($roleFilter === 'freelancer') {
    $where .= " AND role_id = 1";
} elseif ($roleFilter === 'user') {
    $where .= " AND role_id = 2";
} elseif ($roleFilter === 'admin') {
    $where .= " AND role_id = 3";
}

$sqlUsers = "SELECT * FROM users WHERE $where ORDER BY created_at DESC";
$stmtUsers = $conn->prepare($sqlUsers);
if (!empty($params)) {
    $stmtUsers->bind_param($types, ...$params);
}
$stmtUsers->execute();
$resultUsers = $stmtUsers->get_result();
$users = $resultUsers->fetch_all(MYSQLI_ASSOC);

// Fetch Pending Freelancers for Tab 2
$pendingFreelancers = [];
$sqlPending = "SELECT u.*, f.created_at as req_date 
               FROM users u 
               JOIN freelancers f ON u.user_id = f.user_id 
               WHERE f.is_verified = 0 
               ORDER BY f.created_at DESC";

// Check if freelancers table exists to avoid crash
$checkTable = $conn->query("SHOW TABLES LIKE 'freelancers'");
if ($checkTable && $checkTable->num_rows > 0) {
    $resultPending = $conn->query($sqlPending);
    if ($resultPending) {
        $pendingFreelancers = $resultPending->fetch_all(MYSQLI_ASSOC);
    }
}

function getRoleName($roleId) {
    switch ($roleId) {
        case 1: return 'Freelancer';
        case 2: return 'User';
        case 3: return 'Super Admin';
        default: return 'Unknown';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quản lý người dùng - UniWork SuperAdmin</title>
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
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 10px 20px;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            color: #412D3B;
            border-bottom: 2px solid #412D3B;
            background: transparent;
        }
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .filter-btn {
            border-radius: 20px;
            padding: 5px 15px;
            margin-right: 5px;
            border: 1px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }
        .filter-btn.active {
            background: #412D3B;
            color: white;
            border-color: #412D3B;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        /* icon + label inline */
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            margin: 0 5px;
            font-size: 1.05rem;
            text-decoration: none;
            color: inherit;
        }
        .action-btn small { font-size: 0.75rem; margin: 0; line-height: 1; display: inline-block; }

        /* override delete / danger color to custom dark-maroon */
        .btn-delete, .action-btn.text-danger { color: #412D3B !important; }

        /* make search input light on this page */
        input.search-input.form-control {
            background-color: #ffffff !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
        }

        /* override danger badges (e.g. pending count) to custom color */
        .badge.bg-danger, .badge.bg-danger.rounded-pill {
            background-color: #412D3B !important;
            color: #fff !important;
        }
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
                        <h6 class="mb-0">Quản lý người dùng</h6>
                    </div>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="userTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">Danh sách người dùng</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approval" type="button" role="tab">
                                Phê duyệt Freelancer 
                                <?php if(count($pendingFreelancers) > 0): ?>
                                    <span class="badge bg-danger rounded-pill ms-2"><?= count($pendingFreelancers) ?></span>
                                <?php endif; ?>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="userTabsContent">
                        
                        <!-- Tab 1: User List -->
                        <div class="tab-pane fade show active" id="list" role="tabpanel">
                            <!-- Filter & Search -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                                <div class="mb-2 mb-md-0">
                                    <a href="index.php" class="filter-btn <?= $roleFilter == '' ? 'active' : '' ?>">Tất cả</a>
                                    <a href="index.php?role=freelancer" class="filter-btn <?= $roleFilter == 'freelancer' ? 'active' : '' ?>">Freelancer</a>
                                    <a href="index.php?role=user" class="filter-btn <?= $roleFilter == 'user' ? 'active' : '' ?>">User</a>
                                    <a href="index.php?role=admin" class="filter-btn <?= $roleFilter == 'admin' ? 'active' : '' ?>">Admin</a>
                                </div>
                                <form action="index.php" method="GET" class="d-flex">
                                    <?php if($roleFilter): ?><input type="hidden" name="role" value="<?= $roleFilter ?>"><?php endif; ?>
                                    <input type="text" name="search" class="form-control me-2 search-input" placeholder="Tìm kiếm người dùng..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-white">
                                            <th scope="col">Tên người dùng</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Loại tài khoản</th>
                                            <th scope="col">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($users) > 0): ?>
                                            <?php foreach($users as $user): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['full_name'] ?: $user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= getRoleName($user['role_id']) ?></td>
                                                <td>
                                                    <a href="#" class="action-btn btn-view" title="Xem"><i class="fa fa-eye"></i><small>Xem</small></a>
                                                    <a href="#" class="action-btn btn-edit" title="Khoá"><i class="fa fa-edit"></i><small>Khoá</small></a>
                                                    <?php if($user['role_id'] != 3): // Don't allow deleting SuperAdmin ?>
                                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn khoá user này?');">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                    
                                                    </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center">Không tìm thấy người dùng nào</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Freelancer Approval -->
                        <div class="tab-pane fade" id="approval" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-white">
                                            <th scope="col">Tên người dùng</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Loại tài khoản</th>
                                            <th scope="col">Trạng thái</th>
                                            <th scope="col">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($pendingFreelancers) > 0): ?>
                                            <?php foreach($pendingFreelancers as $pUser): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($pUser['full_name']) ?></td>
                                                <td><?= htmlspecialchars($pUser['email']) ?></td>
                                                <td>User</td> <!-- Logically they applied from User -->
                                                <td><span class="status-badge status-pending">Chờ xét duyệt</span></td>
                                                <td>
                                                    <!-- View Details (Mock) -->
                                                    <a href="#" class="action-btn btn-view" title="Xem chi tiết" data-bs-toggle="modal" data-bs-target="#detailModal<?= $pUser['user_id'] ?>"><i class="fa fa-eye"></i><small>Xem</small></a>
                                                    
                                                    <!-- Approve -->
                                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Phê duyệt yêu cầu này?');">
                                                        <input type="hidden" name="action" value="approve_freelancer">
                                                        <input type="hidden" name="user_id" value="<?= $pUser['user_id'] ?>">
                                                        <button type="submit" class="border-0 bg-transparent action-btn text-success" title="Duyệt"><i class="fa fa-check-circle"></i><small>Duyệt</small></button>
                                                    </form>
                                                    
                                                    <!-- Reject -->
                                                    <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Từ chối yêu cầu này?');">
                                                        <input type="hidden" name="action" value="reject_freelancer">
                                                        <input type="hidden" name="user_id" value="<?= $pUser['user_id'] ?>">
                                                        <button type="submit" class="border-0 bg-transparent action-btn text-danger" title="Từ chối"><i class="fa fa-times-circle"></i><small>Từ chối</small></button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- Detail Modal -->
                                            <div class="modal fade" id="detailModal<?= $pUser['user_id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content bg-secondary text-white">
                                                        <div class="modal-header border-0">
                                                            <h5 class="modal-title">Chi tiết yêu cầu</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <p><strong>Tên:</strong> <?= htmlspecialchars($pUser['full_name']) ?></p>
                                                            <p><strong>Email:</strong> <?= htmlspecialchars($pUser['email']) ?></p>
                                                            <p><strong>SĐT:</strong> <?= htmlspecialchars($pUser['phone']) ?></p>
                                                            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($pUser['address']) ?></p>
                                                            <p><strong>Ngày yêu cầu:</strong> <?= htmlspecialchars($pUser['req_date']) ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center">Không có yêu cầu nào đang chờ duyệt</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
