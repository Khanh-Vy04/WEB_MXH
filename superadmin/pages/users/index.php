<?php
session_start();
require_once '../../../includes/session.php';
require_once '../../../config/database.php';

// Check Superadmin Role
requireSuperAdmin();

$currentPage = 'users';

// Filter logic
$role_filter = isset($_GET['role']) ? (int)$_GET['role'] : 0;
$where_clause = "WHERE role_id != 3"; // Don't show other superadmins by default or protect them
if ($role_filter > 0) {
    $where_clause .= " AND role_id = $role_filter";
}

// Search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $where_clause .= " AND (username LIKE '%$search%' OR email LIKE '%$search%' OR full_name LIKE '%$search%')";
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM users $where_clause";
$total_rows = $conn->query($sql_count)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'change_role') {
        $user_id = (int)$_POST['user_id'];
        $new_role = (int)$_POST['new_role'];
        // Only allow changing to Freelancer (1) or User (2)
        if (in_array($new_role, [1, 2])) {
            $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $new_role, $user_id);
            if ($stmt->execute()) {
                $success_msg = "Cập nhật vai trò thành công!";
                // Refresh to show change
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                $error_msg = "Lỗi: " . $conn->error;
            }
        }
    } elseif ($_POST['action'] == 'delete_user') {
        $user_id = (int)$_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success_msg = "Xóa người dùng thành công!";
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $error_msg = "Lỗi: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../../includes/header.php'; ?>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <?php include '../../includes/sidebar.php'; ?>

        <div class="content">
            <?php include '../../includes/navbar.php'; ?>

            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Quản lý Người dùng</h6>
                        <form class="d-flex" method="GET">
                            <?php if($role_filter): ?><input type="hidden" name="role" value="<?php echo $role_filter; ?>"><?php endif; ?>
                            <input class="form-control bg-dark border-0" type="search" name="search" placeholder="Search" value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary ms-2" type="submit">Tìm</button>
                        </form>
                    </div>

                    <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error_msg)): ?>
                        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-white">
                                    <th scope="col">Username</th>
                                    <th scope="col">Full Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Joined</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td>
                                                <?php 
                                                    if ($row['role_id'] == 1) echo '<span class="badge bg-warning text-dark">Freelancer</span>';
                                                    elseif ($row['role_id'] == 2) echo '<span class="badge bg-info text-dark">User</span>';
                                                    elseif ($row['role_id'] == 3) echo '<span class="badge bg-danger">Superadmin</span>';
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                    <?php if ($row['role_id'] == 2): ?>
                                                        <input type="hidden" name="action" value="change_role">
                                                        <input type="hidden" name="new_role" value="1">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Thăng cấp lên Freelancer"><i class="fa fa-arrow-up"></i></button>
                                                    <?php elseif ($row['role_id'] == 1): ?>
                                                        <input type="hidden" name="action" value="change_role">
                                                        <input type="hidden" name="new_role" value="2">
                                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Hạ cấp xuống User"><i class="fa fa-arrow-down"></i></button>
                                                    <?php endif; ?>
                                                </form>
                                                
                                                <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Xóa người dùng này? Hành động không thể hoàn tác!');">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">Không tìm thấy người dùng nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $role_filter ? '&role=' . $role_filter : ''; ?><?php echo $search ? '&search=' . $search : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>

            <?php include '../../includes/footer.php'; ?>
        </div>
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../../admin/lib/chart/chart.min.js"></script>
    <script src="../../../admin/lib/easing/easing.min.js"></script>
    <script src="../../../admin/lib/waypoints/waypoints.min.js"></script>
    <script src="../../../admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="../../../admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="../../../admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="../../../admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="../../../admin/pages/dashboard/js/main.js"></script>
</body>
</html>

