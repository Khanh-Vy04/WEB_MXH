<?php
$currentPage = 'voucher';
// Dữ liệu mẫu cho bảng voucher
$sample_vouchers = [
    [
        'promotion_id' => 'KM001',
        'product_name' => 'The Beatles - Abbey Road',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'min_order' => 500000,
        'start_date' => '2024-06-01',
        'end_date' => '2024-06-30',
    ],
    [
        'promotion_id' => 'KM002',
        'product_name' => 'Pink Floyd - The Dark Side of the Moon',
        'discount_type' => 'fixed',
        'discount_value' => 50000,
        'min_order' => 0,
        'start_date' => '2024-06-10',
        'end_date' => '2024-07-10',
    ],
    [
        'promotion_id' => 'KM003',
        'product_name' => 'Queen - Greatest Hits',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'min_order' => 300000,
        'start_date' => '2024-06-15',
        'end_date' => '2024-06-25',
    ],
    [
        'promotion_id' => 'KM004',
        'product_name' => 'Nirvana - Nevermind',
        'discount_type' => 'fixed',
        'discount_value' => 100000,
        'min_order' => 1000000,
        'start_date' => '2024-06-05',
        'end_date' => '2024-06-20',
    ],
    // Thêm dữ liệu mẫu cho phân trang
    [
        'promotion_id' => 'KM005',
        'product_name' => 'Miles Davis - Kind of Blue',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'min_order' => 200000,
        'start_date' => '2024-06-12',
        'end_date' => '2024-07-01',
    ],
    [
        'promotion_id' => 'KM006',
        'product_name' => 'The Beatles - Let It Be',
        'discount_type' => 'fixed',
        'discount_value' => 75000,
        'min_order' => 0,
        'start_date' => '2024-06-18',
        'end_date' => '2024-07-10',
    ],
    [
        'promotion_id' => 'KM007',
        'product_name' => 'Pink Floyd - Animals',
        'discount_type' => 'percentage',
        'discount_value' => 5,
        'min_order' => 100000,
        'start_date' => '2024-06-20',
        'end_date' => '2024-07-15',
    ],
    [
        'promotion_id' => 'KM008',
        'product_name' => 'Queen - Jazz',
        'discount_type' => 'fixed',
        'discount_value' => 30000,
        'min_order' => 0,
        'start_date' => '2024-06-22',
        'end_date' => '2024-07-20',
    ],
    [
        'promotion_id' => 'KM009',
        'product_name' => 'Nirvana - In Utero',
        'discount_type' => 'percentage',
        'discount_value' => 8,
        'min_order' => 250000,
        'start_date' => '2024-06-25',
        'end_date' => '2024-07-25',
    ],
    [
        'promotion_id' => 'KM010',
        'product_name' => 'Miles Davis - Bitches Brew',
        'discount_type' => 'fixed',
        'discount_value' => 60000,
        'min_order' => 0,
        'start_date' => '2024-06-28',
        'end_date' => '2024-07-30',
    ],
];
// Dữ liệu mẫu cho dropdown sản phẩm
$sample_products = [
    'The Beatles - Abbey Road',
    'Pink Floyd - The Dark Side of the Moon',
    'Queen - Greatest Hits',
    'Nirvana - Nevermind',
    'Miles Davis - Kind of Blue',
];
// Phân trang mẫu
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 5;
$total = count($sample_vouchers);
$start = ($page-1)*$per_page;
$end = min($start+$per_page, $total);
$vouchers_to_show = array_slice($sample_vouchers, $start, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voucher</title>
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        body {background: #f5f6fa;}
        .voucher-header {display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;}
        .voucher-title {font-size: 2rem; font-weight: 700; color: #222;}
        .breadcrumb {background: none; padding: 0; margin-bottom: 0;}
        .breadcrumb-item+.breadcrumb-item::before {content: '>'; color: #888;}
        .voucher-table th, .voucher-table td {vertical-align: middle; text-align: center;}
        .voucher-table th {background: #f3f4f6; color: #222; font-weight: 700;}
        .voucher-table td {background: #fff; color: #333;}
        .voucher-table .first-col {text-align: left;}
        .btn-add-voucher {background: #222; color: #fff; font-weight: 600; border-radius: 8px; padding: 8px 22px; font-size: 1.1rem; cursor:pointer; transition: background 0.2s, color 0.2s;}
        .btn-add-voucher:hover {background: #444; color: #fff; box-shadow: 0 2px 8px #2222;}
        .btn-edit {background: #fffbe6; color: #b45309; border-radius: 6px; padding: 4px 14px; font-size: 1em; border: 1px solid #fbbf24; cursor:pointer; transition: background 0.2s, color 0.2s;}
        .btn-edit:hover {background: #fbbf24; color: #fff; box-shadow: 0 2px 8px #fbbf2422;}
        .btn-delete {background: #fff1f2; color: #b91c1c; border-radius: 6px; padding: 4px 14px; font-size: 1em; border: 1px solid #ef4444; cursor:pointer; transition: background 0.2s, color 0.2s;}
        .btn-delete:hover {background: #ef4444; color: #fff; box-shadow: 0 2px 8px #ef444422;}
        .filter-bar {display: none;}
        .modal-content {border-radius: 14px; background: #fff;}
        .form-label {font-weight: 500; color: #374151;}
        .form-control, .form-select {background: #fff !important; color: #222 !important; border: 1px solid #d1d5db;}
        .form-control:focus, .form-select:focus {border-color: #6366f1; box-shadow: 0 0 0 2px #6366f133; background: #fff !important; color: #222 !important;}
        ::placeholder {color: #888 !important; opacity: 1;}
        .showing-info {color: #888; font-size: 1rem; margin-top: 8px; text-align: right;}
        .voucher-table {margin-top: 0;}
        .container.py-4 {
            max-width: 100%;
            width: 100%;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .voucher-header, .filter-bar, .table-responsive, .showing-info, .pagination-voucher {
            margin-left: 0;
            margin-right: 0;
            padding-left: 32px;
            padding-right: 32px;
        }
        @media (max-width: 991px) {
            .voucher-header, .filter-bar, .table-responsive, .showing-info, .pagination-voucher {
                padding-left: 8px;
                padding-right: 8px;
            }
        }
        .pagination-voucher {display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;}
        .pagination-voucher .page-link {
            background: #fff;
            color: #222;
            border: 1px solid #222;
            border-radius: 6px;
            padding: 4px 16px;
            font-weight: 500;
            cursor:pointer;
            margin: 0 2px;
            transition: background 0.2s, color 0.2s;
        }
        .pagination-voucher .page-link.active, .pagination-voucher .page-link:focus {
            background: #222;
            color: #fff;
            border-color: #222;
        }
        .pagination-voucher .page-link:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination-voucher .page-link:hover:not(:disabled):not(.active) {
            background: #444;
            color: #fff;
        }
        @media (min-width: 1200px) {
            .container.py-4 {
                max-width: 100%;
            }
        }
        @media (max-width: 991px) {
            .voucher-header {flex-direction: column; align-items: flex-start; gap: 10px;}
            .filter-bar {flex-direction: column; align-items: stretch;}
            .container.py-4 {padding-left: 0 !important; padding-right: 0 !important;}
        }
    </style>
</head>
<body style="min-height:100vh; display:flex; flex-direction:column;">
<div class="container-fluid position-relative d-flex p-0" style="flex:1 0 auto;">
    <?php include __DIR__.'/../dashboard/sidebar.php'; ?>
    <div class="content d-flex flex-column" style="min-height:100vh;">
        <?php include __DIR__.'/../dashboard/navbar.php'; ?>
        <div class="container py-4 flex-grow-1">
            <div class="voucher-header">
                <div class="voucher-title">Voucher</div>
                <button class="btn btn-add-voucher" data-bs-toggle="modal" data-bs-target="#voucherModal"><i class="bi bi-plus-circle"></i> Add new</button>
            </div>
            <!-- Bảng danh sách voucher -->
            <div class="table-responsive mb-4">
                <table class="table voucher-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Discount type</th>
                            <th>Discount value</th>
                            <th>Min order</th>
                            <th>Start date</th>
                            <th>End date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($vouchers_to_show as $v): ?>
                        <tr>
                            <td><?php echo $v['promotion_id']; ?></td>
                            <td class="first-col"><?php echo $v['product_name']; ?></td>
                            <td><?php echo $v['discount_type'] === 'percentage' ? 'Percentage' : 'Fixed'; ?></td>
                            <td><?php echo $v['discount_type'] === 'percentage' ? $v['discount_value'].'%' : number_format($v['discount_value'],0,',','.').'₫'; ?></td>
                            <td><?php echo $v['min_order'] ? number_format($v['min_order'],0,',','.').'₫' : '-'; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($v['start_date'])); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($v['end_date'])); ?></td>
                            <td>
                                <button class="btn btn-edit btn-sm me-1 edit-voucher-btn"
                                    data-bs-toggle="modal" data-bs-target="#voucherModal"
                                    data-id="<?php echo $v['promotion_id']; ?>"
                                    data-product="<?php echo htmlspecialchars($v['product_name']); ?>"
                                    data-type="<?php echo $v['discount_type']; ?>"
                                    data-value="<?php echo $v['discount_value']; ?>"
                                    data-minorder="<?php echo $v['min_order']; ?>"
                                    data-start="<?php echo date('Y-m-d', strtotime($v['start_date'])); ?>"
                                    data-end="<?php echo date('Y-m-d', strtotime($v['end_date'])); ?>"
                                ><i class="bi bi-pencil"></i> Edit</button>
                                <button class="btn btn-delete btn-sm"><i class="bi bi-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="showing-info">
                    Showing <?php echo $start+1; ?> to <?php echo $end; ?> of <?php echo $total; ?> vouchers
                </div>
                <div class="pagination-voucher">
                    <?php
                    $total_pages = ceil($total/$per_page);
                    if ($total_pages > 1):
                        for ($i=1; $i<=$total_pages; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo '<a href="?page='.$i.'" class="page-link '.$active.'">'.$i.'</a>';
                        }
                    endif;
                    ?>
                </div>
            </div>
        </div>
        <!-- Modal Thêm/Sửa voucher -->
        <div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="voucherForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="voucherModalLabel">Add / Edit Voucher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product</label>
                                    <select class="form-select" name="product" id="voucherProduct" required>
                                        <option value="">Select product</option>
                                        <?php foreach($sample_products as $p): ?>
                                            <option><?php echo $p; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Discount type</label>
                                    <select class="form-select" name="type" id="voucherType" required>
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Discount value</label>
                                    <input type="number" class="form-control" name="value" id="voucherValue" placeholder="Enter % or amount" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Min order value</label>
                                    <input type="number" class="form-control" name="minorder" id="voucherMinOrder" placeholder="Leave blank if not required">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Start date</label>
                                    <input type="date" class="form-control" name="start" id="voucherStart" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End date</label>
                                    <input type="date" class="form-control" name="end" id="voucherEnd" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/4e9c2b1c2e.js" crossorigin="anonymous"></script>
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
<script>
// Fill modal with voucher data when Edit is clicked
$(document).on('click', '.edit-voucher-btn', function() {
    $('#voucherProduct').val($(this).data('product'));
    $('#voucherType').val($(this).data('type'));
    $('#voucherValue').val($(this).data('value'));
    $('#voucherMinOrder').val($(this).data('minorder'));
    $('#voucherStart').val($(this).data('start'));
    $('#voucherEnd').val($(this).data('end'));
});
// Optional: Reset form when opening for Add new
$('.btn-add-voucher').on('click', function() {
    $('#voucherForm')[0].reset();
});
</script>
</body>
</html> 