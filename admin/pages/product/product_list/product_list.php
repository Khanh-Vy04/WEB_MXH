<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <title>Customer List - AuraDisc Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/product/product_list/product_list.css" rel="stylesheet">
</head>
<body>
    <?php
      $currentPage = 'product';
      include '../../dashboard/sidebar.php';
    ?>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
    </div>
    <!-- Spinner End -->
    <!-- Nội dung chính -->
    <div class="content">
      <!-- Navbar Start -->
      <?php include '../../dashboard/navbar.php'; ?>
      <!-- Navbar End -->
        <div class="container mx-auto">
            <div class="container-fluid pt-4 px-4">
                <div class="bg-white rounded shadow-sm p-3 mb-4 d-flex flex-wrap align-items-center gap-2">
                    <select id="filterStatus" class="form-select w-auto">
                        <option>Status</option>
                        <option>Available</option>
                        <option>Out of Stock</option>
                        <option>Coming Soon</option>
                    </select>
                    <select id="filterCategory" class="form-select w-auto">
                        <option>Category</option>
                        <option>CD</option>
                        <option>Vinyl</option>
                        <option>LP</option>
                        <option>Music Box</option>
                    </select>
                    <select id="filterStock" class="form-select w-auto">
                        <option>Stock</option>
                        <option>In Stock</option>
                        <option>Out of Stock</option>
                    </select>
                    <input type="text" placeholder="Search product" class="form-control w-auto" id="searchProduct" style="min-width:180px;">
                    <button class="btn btn-primary ms-auto" style="min-width:140px;" onclick="openAddProductModal()">
                        <i class="fa fa-plus me-1"></i> Add Product
                    </button>
                </div>
            </div>

            <!-- Add Product Modal -->
            <div id="addProductModal" class="modal">
              <div class="modal-content">
                <span class="close" onclick="closeAddProductModal()">&times;</span>
                <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
                <form id="addProductForm" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input type="text" id="productName" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="productDesc" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select id="productCategory" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                      <option value="CD">CD</option>
                      <option value="Vinyl">Vinyl</option>
                      <option value="LP">LP</option>
                      <option value="Music Box">Music Box</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                    <input type="text" id="productSku" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Price</label>
                    <input type="text" id="productPrice" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" id="productQty" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="productStatus" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                      <option value="Available">Available</option>
                      <option value="Out of Stock">Out of Stock</option>
                      <option value="Coming Soon">Coming Soon</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Image</label>
                    <input type="text" id="productImage" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Image URL">
                  </div>
                  <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeAddProductModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Add Product</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Product Table -->
            <div class="bg-white shadow rounded overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-2">
                      <input id="selectAll" type="checkbox" />
                    </th>
                    <th class="px-4 py-2 text-left">Image</th>
                    <th class="px-4 py-2 text-left">Product</th>
                    <th class="px-4 py-2 text-left">Category</th>
                    <th class="px-4 py-2 text-left">Stock</th>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Price</th>
                    <th class="px-4 py-2 text-left">Quantity</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="productBody"></tbody>
              </table>
            </div>
        </div>
          <!-- Footer Start -->
          <?php include '../../dashboard/footer.php'; ?>
          <!-- Footer End -->
        </div>
        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
      
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
    <script src="/WEB_MXH/admin/pages/product/product_list.js"></script>
</body>
</html> 