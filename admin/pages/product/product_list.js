let products = [
  // ... dữ liệu sản phẩm giữ nguyên ...
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
const confirmDeleteTitle = confirmDeleteModal.querySelector("h2");
const deleteSelectedBtn = document.getElementById("deleteSelectedBtn");
let selectedProducts = [];
let isSelectAll = false;
let modalVisible = false;

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
  modalVisible = true;
}

function hideDeleteModal() {
  confirmDeleteModal.classList.add("hidden");
  modalVisible = false;
}

// Xử lý chọn tất cả
const selectAllCheckbox = document.getElementById("selectAll");
selectAllCheckbox.addEventListener("change", (event) => {
  const checkboxes = document.querySelectorAll(".product-check");
  isSelectAll = event.target.checked;
  checkboxes.forEach((checkbox) => {
    checkbox.checked = event.target.checked;
  });
  updateSelectedProducts();
});

tbody.addEventListener("change", (event) => {
  if (event.target.classList.contains("product-check")) {
    // Nếu bỏ chọn bất kỳ ô nào thì bỏ chọn ô select all
    if (!event.target.checked) {
      selectAllCheckbox.checked = false;
    }
    updateSelectedProducts();
  }
});

function updateSelectedProducts() {
  selectedProducts = [];
  const checkboxes = document.querySelectorAll(".product-check:checked");
  checkboxes.forEach((checkbox) => {
    selectedProducts.push(Number(checkbox.dataset.index));
  });
  // Kiểm tra nếu chọn tất cả
  const allCheckboxes = document.querySelectorAll(".product-check");
  if (selectedProducts.length === allCheckboxes.length && allCheckboxes.length > 0) {
    isSelectAll = true;
    selectAllCheckbox.checked = true;
    deleteSelectedBtn.disabled = false;
  } else if (selectedProducts.length > 0) {
    isSelectAll = false;
    deleteSelectedBtn.disabled = false;
  } else {
    deleteSelectedBtn.disabled = true;
  }
}

deleteSelectedBtn.addEventListener("click", () => {
  if (isSelectAll) {
    confirmDeleteTitle.textContent = "Bạn có muốn xóa tất cả sản phẩm không?";
  } else {
    confirmDeleteTitle.textContent = "Bạn có muốn xóa các sản phẩm đã chọn không?";
  }
  showDeleteModal();
});

confirmDeleteButton.addEventListener("click", () => {
  if (isSelectAll) {
    products = [];
  } else {
    // Xóa các sản phẩm đã chọn (theo index giảm dần để không bị lệch index)
    selectedProducts.sort((a, b) => b - a).forEach((index) => {
      products.splice(index, 1);
    });
  }
  renderProducts();
  hideDeleteModal();
  selectAllCheckbox.checked = false;
  deleteSelectedBtn.disabled = true;
});

cancelDeleteButton.addEventListener("click", hideDeleteModal);

// Filter products
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
  selectAllCheckbox.checked = false;
  deleteSelectedBtn.disabled = true;
  // Add event listeners for filtering
  document.getElementById("filterStatus").addEventListener("change", filterProducts);
  document.getElementById("filterCategory").addEventListener("change", filterProducts);
  document.getElementById("filterStock").addEventListener("change", filterProducts);
  document.getElementById("searchProduct").addEventListener("input", filterProducts);
}); 