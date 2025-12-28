<?php
session_start();
require_once '../../../includes/session.php';
require_once '../../../config/database.php';

// Check Superadmin Role
requireSuperAdmin();

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $banner_url = trim($_POST['banner_url']);
    $banner_title = trim($_POST['banner_title']);
    $banner_description = trim($_POST['banner_description']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Simple validation
    if (!empty($banner_url) && !empty($banner_title)) {
        $stmt = $conn->prepare("INSERT INTO banners (banner_url, banner_title, banner_description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $banner_url, $banner_title, $banner_description, $display_order, $is_active);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = "URL and Title are required.";
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
                <div class="bg-secondary rounded p-4">
                    <h6 class="mb-4">Thêm Banner Mới</h6>
                    <?php if(isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Banner URL (Image Link)</label>
                            <input type="url" class="form-control" name="banner_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="banner_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="banner_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="0">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Thêm Banner</button>
                        <a href="index.php" class="btn btn-outline-secondary ms-2">Hủy</a>
                    </form>
                </div>
            </div>
            <?php include '../../includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
</body>
</html>

