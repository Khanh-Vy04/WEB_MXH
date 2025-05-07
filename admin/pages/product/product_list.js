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
let selectedProducts = [];

// Đợi DOM load xong
document.addEventListener("DOMContentLoaded", function() {
    // Khởi tạo các biến DOM
    const tbody = document.getElementById("productBody");
    const confirmDeleteModal = document.getElementById("confirmDeleteModal");
    const confirmDeleteButton = document.getElementById("confirmDelete");
    const cancelDeleteButton = document.getElementById("cancelDelete");
    const addProductModal = document.getElementById("addProductModal");
    const addProductForm = document.getElementById("addProductForm");
    const selectAll = document.getElementById("selectAll");

    // Render sản phẩm ban đầu
    renderProducts();

    // Thêm các event listeners
    selectAll.addEventListener("change", handleSelectAll);
    tbody.addEventListener("change", handleProductCheck);
    addProductForm.addEventListener("submit", handleAddProduct);
    confirmDeleteButton.addEventListener("click", handleConfirmDelete);
    cancelDeleteButton.addEventListener("click", hideDeleteModal);

    // Filter event listeners
    document.getElementById("filterStatus").addEventListener("change", filterProducts);
    document.getElementById("filterCategory").addEventListener("change", filterProducts);
    document.getElementById("filterStock").addEventListener("change", filterProducts);
    document.getElementById("searchProduct").addEventListener("input", filterProducts);
});

// Các hàm xử lý
function renderProducts(filteredProducts = products) {
    const tbody = document.getElementById("productBody");
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
                <button class="text-yellow-600 hover:text-yellow-800" onclick="editProduct(${idx})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-red-600 hover:text-red-800" onclick="deleteProduct(${idx})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function handleSelectAll(event) {
    const checkboxes = document.querySelectorAll(".product-check");
    checkboxes.forEach((checkbox) => {
        checkbox.checked = event.target.checked;
    });
    updateSelectedProducts();
}

function handleProductCheck(event) {
    if (event.target.classList.contains("product-check")) {
        updateSelectedProducts();
    }
}

function handleAddProduct(e) {
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
}

function handleConfirmDelete() {
    selectedProducts.sort((a, b) => b - a).forEach((index) => {
        products.splice(index, 1);
    });
    renderProducts();
    hideDeleteModal();
    document.getElementById("selectAll").checked = false;
}

function deleteProduct(index) {
    if (confirm(`Are you sure you want to delete "${products[index].name}"?`)) {
        products.splice(index, 1);
        renderProducts();
    }
}

function showDeleteModal() {
    document.getElementById("confirmDeleteModal").classList.remove("hidden");
}

function hideDeleteModal() {
    document.getElementById("confirmDeleteModal").classList.add("hidden");
}

function openAddProductModal() {
    document.getElementById("addProductModal").style.display = "block";
}

function closeAddProductModal() {
    document.getElementById("addProductModal").style.display = "none";
    document.getElementById("addProductForm").reset();
}

function updateSelectedProducts() {
    selectedProducts = [];
    const checkboxes = document.querySelectorAll(".product-check:checked");
    checkboxes.forEach((checkbox) => {
        selectedProducts.push(Number(checkbox.dataset.index));
    });
    
    if (selectedProducts.length > 0) {
        showDeleteModal();
    } else {
        hideDeleteModal();
    }
}

function filterProducts() {
    const statusFilter = document.getElementById("filterStatus").value;
    const categoryFilter = document.getElementById("filterCategory").value;
    const stockFilter = document.getElementById("filterStock").value;
    const searchQuery = document.getElementById("searchProduct").value.toLowerCase();

    const filteredProducts = products.filter((prod) => {
        const statusMatch = statusFilter === "Status" || prod.status === statusFilter;
        const categoryMatch = categoryFilter === "Category" || prod.category === categoryFilter;
        const stockMatch = stockFilter === "Stock" || 
            (stockFilter === "In Stock" && prod.stock) || 
            (stockFilter === "Out of Stock" && !prod.stock);
        const searchMatch = prod.name.toLowerCase().includes(searchQuery) || 
            prod.desc.toLowerCase().includes(searchQuery);

        return statusMatch && categoryMatch && stockMatch && searchMatch;
    });

    renderProducts(filteredProducts);
}