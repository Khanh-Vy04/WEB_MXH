<?php
session_start();
require_once '../../../includes/session.php';
require_once '../../../config/database.php';

// Check Superadmin Role
requireSuperAdmin();

$currentPage = 'banner';

// Get banners
$sql = "SELECT * FROM banners ORDER BY display_order ASC";
$result = $conn->query($sql);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM banners WHERE banner_id = $id");
        header("Location: index.php");
        exit();
    } elseif ($_POST['action'] == 'toggle') {
        $id = (int)$_POST['id'];
        $status = (int)$_POST['status'] ? 0 : 1;
        $conn->query("UPDATE banners SET is_active = $status WHERE banner_id = $id");
        header("Location: index.php");
        exit();
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
                        <h6 class="mb-0">Quản lý Banner</h6>
                        <a href="add.php" class="btn btn-primary">Thêm Banner</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-white">
                                    <th scope="col">Image</th>
                                    <th scope="col">Title</th>
                                    <th scope="col">Order</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><img src="<?php echo htmlspecialchars($row['banner_url']); ?>" style="width: 100px; height: auto;"></td>
                                            <td><?php echo htmlspecialchars($row['banner_title']); ?></td>
                                            <td><?php echo $row['display_order']; ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?php echo $row['banner_id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $row['is_active']; ?>">
                                                    <button type="submit" class="btn btn-sm <?php echo $row['is_active'] ? 'btn-success' : 'btn-danger'; ?>">
                                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $row['banner_id']; ?>" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                                                <form method="POST" class="d-inline ms-1" onsubmit="return confirm('Xóa banner này?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $row['banner_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Không có banner nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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

