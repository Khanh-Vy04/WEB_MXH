<?php $currentPage = 'product_list'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Product List</title>
    <!-- Bootstrap CSS -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Product List CSS -->
    <link href="product_list.css" rel="stylesheet" />
</head>
<body>
    <!-- Sidebar Start 
    <div class="sidebar pe-4 pb-3">
        <nav class="navbar bg-secondary navbar-dark">
            <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="navbar-brand mx-4 mb-3">
                <h3 class="text-primary"><i class="fa fa-user-edit me-2"></i>AuraDisc</h3>
            </a>
            <div class="d-flex align-items-center ms-4 mb-4">
                <div class="ms-3">
                    <h6 class="mb-0">ADMIN</h6>
                </div>
            </div>
            <div class="navbar-nav w-100">
                <a href="/WEB_MXH/admin/pages/dashboard/dashboard.php" class="nav-item nav-link <?php if(isset($currentPage) && $currentPage == 'dashboard') echo 'active'; ?>">
                    <i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="/WEB_MXH/admin/pages/product/product_list/product_list.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'product') === 0) echo 'active'; ?>">
                    <i class="fa fa-shopping-basket me-2"></i>Product List</a>
                <a href="/WEB_MXH/admin/pages/order/order_detail/order_detail.php" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'order') === 0) echo 'active'; ?>">
                    <i class="fa fa-receipt me-2"></i>Order Detail</a>
                <a href="#" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'customer') === 0) echo 'active'; ?>" ><i class="fa fa-user-astronaut me-2"></i>Customer List</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && strpos($currentPage, 'support') === 0) echo 'active'; ?>" data-bs-toggle="dropdown"><i class="fa fa-people-carry me-2"></i>Customer Support</a>
                    <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && strpos($currentPage, 'support') === 0) echo 'show'; ?>">
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'feedbacks') echo 'active'; ?>">Feedbacks</a>
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'messages') echo 'active'; ?>">Messages</a>                        
                    </div>
                </div>
                <a href="#" class="nav-item nav-link <?php if(isset($currentPage) && strpos($currentPage, 'refund') === 0) echo 'active'; ?>"><i class="fa fa-hand-holding-usd me-2"></i>Refund List</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php if(isset($currentPage) && strpos($currentPage, 'setting') === 0) echo 'active'; ?>" data-bs-toggle="dropdown"><i class="fa fa-users-cog me-2"></i>Setting</a>
                    <div class="dropdown-menu bg-transparent border-0 <?php if(isset($currentPage) && strpos($currentPage, 'setting') === 0) echo 'show'; ?>">
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'notification') echo 'active'; ?>">Notification</a>
                        <a href="#" class="dropdown-item <?php if(isset($currentPage) && $currentPage == 'voucher') echo 'active'; ?>">Voucher</a>
                    </div>
                </div>
                <a href="#" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Log Out</a>
            </div>
        </nav>
    </div>
    Sidebar End -->

    <!-- Nội dung chính -->
    <div class="content">
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

        <div
          id="confirmDeleteModal"
          class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center hidden"
        >
          <div class="bg-white p-8 rounded-md shadow-lg">
            <h2 class="text-xl font-semibold mb-4">
              Are you sure you want to delete the selected products?
            </h2>
            <div class="flex space-x-4">
              <button
                id="confirmDelete"
                class="bg-red-500 text-white px-4 py-2 rounded"
              >
                Yes
              </button>
              <button
                id="cancelDelete"
                class="bg-gray-300 text-gray-800 px-4 py-2 rounded"
              >
                No
              </button>
            </div>
          </div>
        </div>
    </div>

    <script>
      let products = [
        {
          name: "Abbey Road - The Beatles (Vinyl)",
          desc: "The Beatles' iconic Abbey Road album on vinyl.",
          category: "Vinyl",
          stock: true,
          sku: "AB123",
          price: "$40.00",
          qty: "100",
          status: "Available",
          image: "https://via.placeholder.com/100x100?text=Abbey+Road",
        },
        {
          name: "The Dark Side of the Moon - Pink Floyd (LP)",
          desc: "Pink Floyd's legendary album, The Dark Side of the Moon.",
          category: "LP",
          stock: false,
          sku: "PF456",
          price: "$50.00",
          qty: "0",
          status: "Out of Stock",
          image: "https://via.placeholder.com/100x100?text=Dark+Side+LP",
        },
        {
          name: "Bohemian Rhapsody - Queen (CD)",
          desc: "Queen's masterpiece, Bohemian Rhapsody, on CD.",
          category: "CD",
          stock: true,
          sku: "QR789",
          price: "$15.00",
          qty: "150",
          status: "Available",
          image: "https://via.placeholder.com/100x100?text=Bohemian+Rhapsody",
        },
        {
          name: "Music Box - Classic Melody",
          desc: "Hand-cranked music box playing a classic melody.",
          category: "Music Box",
          stock: true,
          sku: "MB321",
          price: "$75.00",
          qty: "50",
          status: "Coming Soon",
          image: "https://via.placeholder.com/100x100?text=Classic+Music+Box",
        },
      ];

      const tbody = document.getElementById("productBody");
      const confirmDeleteModal = document.getElementById("confirmDeleteModal");
      const confirmDeleteButton = document.getElementById("confirmDelete");
      const cancelDeleteButton = document.getElementById("cancelDelete");
      const addProductModal = document.getElementById("addProductModal");
      const addProductForm = document.getElementById("addProductForm");
      let selectedProducts = [];

      function openAddProductModal() {
        addProductModal.style.display = "block";
      }

      function closeAddProductModal() {
        addProductModal.style.display = "none";
        addProductForm.reset();
      }

      addProductForm.addEventListener("submit", function(e) {
        e.preventDefault();
        
        const newProduct = {
          name: document.getElementById("productName").value,
          desc: document.getElementById("productDesc").value,
          category: document.getElementById("productCategory").value,
          stock: document.getElementById("productStatus").value === "Available",
          sku: document.getElementById("productSku").value,
          price: "$" + document.getElementById("productPrice").value,
          qty: document.getElementById("productQty").value,
          status: document.getElementById("productStatus").value,
          image: document.getElementById("productImage").value || "https://via.placeholder.com/100x100?text=New+Product"
        };

        products.push(newProduct);
        renderProducts();
        closeAddProductModal();
      });

      function renderProducts(filteredProducts = products) {
        tbody.innerHTML = "";
        filteredProducts.forEach((prod, idx) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td><input class="product-check" type="checkbox" data-index="${idx}"></td>
            <td><img src="${prod.image}" alt="${prod.name}" class="w-16 h-16 object-cover"></td>
            <td>
              <p class="font-semibold">${prod.name}</p>
              <p class="text-xs text-gray-500">${prod.desc}</p>
            </td>
            <td>${prod.category}</td>
            <td class="px-4 py-2 text-center">
              <input type="checkbox" class="stock-checkbox" ${prod.stock ? "checked" : ""} disabled>
            </td>
            <td>${prod.sku}</td>
            <td>${prod.price}</td>
            <td>${prod.qty}</td>
            <td>
              <span class="text-xs px-2 py-1 rounded ${
                prod.status === "Available"
                  ? "bg-green-100 text-green-600"
                  : prod.status === "Coming Soon"
                  ? "bg-yellow-100 text-yellow-600"
                  : "bg-red-100 text-red-600"
              }">${prod.status}</span>
            </td>
            <td class="px-4 py-2 text-center space-x-2">
              <button class="text-yellow-600 hover:text-yellow-800" onclick="alert('Edit ${prod.name}')"><i class="fas fa-edit"></i></button>
              <button class="text-red-600 hover:text-red-800" onclick="deleteProduct(${idx})"><i class="fas fa-trash"></i></button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }

      function deleteProduct(index) {
        if (confirm(`Are you sure you want to delete "${products[index].name}"?`)) {
          products.splice(index, 1);
          renderProducts();
        }
      }

      function showDeleteModal() {
        confirmDeleteModal.classList.remove("hidden");
      }

      function hideDeleteModal() {
        confirmDeleteModal.classList.add("hidden");
      }

      document.getElementById("selectAll").addEventListener("change", (event) => {
        const checkboxes = document.querySelectorAll(".product-check");
        checkboxes.forEach((checkbox) => {
          checkbox.checked = event.target.checked;
          updateSelectedProducts();
        });
      });

      tbody.addEventListener("change", (event) => {
        if (event.target.classList.contains("product-check")) {
          updateSelectedProducts();
        }
      });

      function updateSelectedProducts() {
        selectedProducts = [];
        const checkboxes = document.querySelectorAll(".product-check:checked");
        checkboxes.forEach((checkbox) => {
          selectedProducts.push(checkbox.dataset.index);
        });
        if (selectedProducts.length > 0) {
          showDeleteModal();
        } else {
          hideDeleteModal();
        }
      }

      confirmDeleteButton.addEventListener("click", () => {
        selectedProducts.forEach((index) => {
          products.splice(index, 1);
        });
        renderProducts();
        hideDeleteModal();
      });

      cancelDeleteButton.addEventListener("click", hideDeleteModal);

      function filterProducts() {
        const statusFilter = document.getElementById("filterStatus").value;
        const categoryFilter = document.getElementById("filterCategory").value;
        const stockFilter = document.getElementById("filterStock").value;
        const searchQuery = document.getElementById("searchProduct").value.toLowerCase();

        const filteredProducts = products.filter((prod) => {
          const statusMatch = statusFilter === "Status" || prod.status === statusFilter;
          const categoryMatch = categoryFilter === "Category" || prod.category === categoryFilter;
          const stockMatch = stockFilter === "Stock" || (stockFilter === "In Stock" && prod.stock) || (stockFilter === "Out of Stock" && !prod.stock);
          const searchMatch = prod.name.toLowerCase().includes(searchQuery) || prod.desc.toLowerCase().includes(searchQuery);

          return statusMatch && categoryMatch && stockMatch && searchMatch;
        });

        renderProducts(filteredProducts);
      }

      document.addEventListener("DOMContentLoaded", () => {
        renderProducts();
        const selectAll = document.getElementById("selectAll");
        selectAll.addEventListener("change", function () {
          const checkboxes = document.querySelectorAll(".product-check");
          checkboxes.forEach((cb) => (cb.checked = selectAll.checked));
        });

        document.getElementById("filterStatus").addEventListener("change", filterProducts);
        document.getElementById("filterCategory").addEventListener("change", filterProducts);
        document.getElementById("filterStock").addEventListener("change", filterProducts);
        document.getElementById("searchProduct").addEventListener("input", filterProducts);
      });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dashboard JS (nếu có) -->
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html> 