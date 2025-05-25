<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Product</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="add_product.css" />
  </head>
  <body>
    <div class="container-fluid position-relative d-flex p-0">
      <?php
        $currentPage = 'product';
        include __DIR__.'/../../dashboard/sidebar.php';
      ?>
      <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="header">
          <div class="header-left">
            <h1>Add a new Product</h1>
            <p>Orders placed across your store</p>
          </div>
          <div class="action-buttons">
            <button class="btn-discard">Discard</button>
            <button class="btn-draft">Save draft</button>
            <button class="btn-publish">Publish product</button>
          </div>
        </div>
        <div class="content-wrapper">
          <!-- Left Column -->
          <div class="product-info">
            <div class="card">
              <h2>Product information</h2>
              <div class="form-group">
                <label>Name</label>
                <input type="text" placeholder="Product title" />
              </div>
              <div class="form-group">
                <label>SKU</label>
                <input type="text" placeholder="SKU" />
              </div>
              <div class="form-group">
                <label>Description (Optional)</label>
                <div class="editor-toolbar">
                  <button><i class="fas fa-bold"></i></button>
                  <button><i class="fas fa-italic"></i></button>
                  <button><i class="fas fa-underline"></i></button>
                  <button><i class="fas fa-list-ul"></i></button>
                  <button><i class="fas fa-list-ol"></i></button>
                  <button><i class="fas fa-link"></i></button>
                  <button><i class="fas fa-image"></i></button>
                </div>
                <textarea placeholder="Product Description"></textarea>
              </div>
            </div>
            <div class="card">
              <h2>Product Image</h2>
              <div class="image-upload">
                <div class="upload-area">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Drag and drop your image here</p>
                  <span>or</span>
                  <button class="btn-browse">Browse image</button>
                </div>
                <div class="media-url">
                  <a href="#">Add media from URL</a>
                </div>
              </div>
            </div>
            <div class="card">
              <h2>Variants</h2>
              <div class="variants-section">
                <label>Options</label>
                <div class="option-row">
                  <select>
                    <option>Size</option>
                  </select>
                  <input type="text" placeholder="Enter size" />
                </div>
                <button class="btn-add-option">+ Add another option</button>
              </div>
            </div>
            <div class="card">
              <h2>Inventory</h2>
              <div class="inventory-section">
                <button class="btn-restock">
                  <i class="fas fa-box"></i> Restock
                </button>
                <div class="inventory-options">
                  <div class="option">
                    <i class="fas fa-truck"></i> Shipping
                  </div>
                  <div class="option">
                    <i class="fas fa-tags"></i> Attributes
                  </div>
                  <div class="option"><i class="fas fa-cog"></i> Advanced</div>
                </div>
                <div class="stock-info">
                  <div class="stock-control">
                    <label>Add to Stock</label>
                    <div class="stock-input">
                      <input type="number" placeholder="Quantity" />
                      <button class="btn-confirm">Confirm</button>
                    </div>
                  </div>
                  <div class="stock-stats">
                    <p>Product in stock now: 54</p>
                    <p>Product in transit: 390</p>
                    <p>Last time restocked: 24th June, 2023</p>
                    <p>Total stock over lifetime: 2430</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Right Column -->
          <div class="product-sidebar">
            <div class="card">
              <h2>Pricing</h2>
              <div class="form-group">
                <label>Base Price</label>
                <input type="number" placeholder="Price" />
              </div>
              <div class="form-group">
                <label>Discounted Price</label>
                <input type="number" placeholder="Discounted Price" />
              </div>
              <div class="form-check">
                <input type="checkbox" id="taxCheck" />
                <label for="taxCheck">Charge tax on this product</label>
              </div>
              <div class="switch-container">
                <label class="switch">
                  <input type="checkbox" checked />
                  <span class="slider round"></span>
                </label>
                <span class="switch-label">In stock</span>
              </div>
            </div>
            <div class="card">
              <h2>Organize</h2>
              <div class="form-group">
                <label>Artist</label>
                <div class="custom-select">
                  <div class="select-selected">
                    <span>Select Artist</span>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="select-items">
                    <div class="select-item">The Beatles</div>
                    <div class="select-item">Adele</div>
                    <div class="select-item">Pink Floyd</div>
                    <div class="select-item">Queen</div>
                    <div class="select-item">Led Zeppelin</div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Vendor</label>
                <div class="custom-select">
                  <div class="select-selected">
                    <span>Select Vendor</span>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="select-items">
                    <div class="select-item">Vendor 1</div>
                    <div class="select-item">Vendor 2</div>
                    <div class="select-item">Vendor 3</div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Category</label>
                <div class="category-input">
                  <div class="custom-select">
                    <div class="select-selected">
                      <span>Select Category</span>
                      <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="select-items">
                      <div class="select-item">Category 1</div>
                      <div class="select-item">Category 2</div>
                      <div class="select-item">Category 3</div>
                    </div>
                  </div>
                  <button class="btn-add"><i class="fas fa-plus"></i></button>
                </div>
              </div>
              <div class="form-group">
                <label>Collection</label>
                <div class="custom-select">
                  <div class="select-selected">
                    <span>Collection</span>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="select-items">
                    <div class="select-item">Collection 1</div>
                    <div class="select-item">Collection 2</div>
                    <div class="select-item">Collection 3</div>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Status</label>
                <div class="custom-select">
                  <div class="select-selected">
                    <span>Published</span>
                    <i class="fas fa-chevron-down"></i>
                  </div>
                  <div class="select-items">
                    <div class="select-item">Published</div>
                    <div class="select-item">Draft</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        // Get all custom select elements
        const customSelects = document.querySelectorAll(".custom-select");

        // Handle click outside to close dropdowns
        document.addEventListener("click", function (e) {
          if (!e.target.closest(".custom-select")) {
            customSelects.forEach((select) => {
              select.classList.remove("active");
            });
          }
        });

        // Setup each custom select
        customSelects.forEach((select) => {
          const selected = select.querySelector(".select-selected");
          const items = select.querySelector(".select-items");
          const selectedText = selected.querySelector("span");

          // Toggle dropdown on click
          selected.addEventListener("click", function (e) {
            e.stopPropagation();
            customSelects.forEach((otherSelect) => {
              if (otherSelect !== select) {
                otherSelect.classList.remove("active");
              }
            });
            select.classList.toggle("active");
          });

          // Handle item selection
          const selectItems = items.querySelectorAll(".select-item");
          selectItems.forEach((item) => {
            item.addEventListener("click", function () {
              selectedText.textContent = this.textContent;
              selectItems.forEach((si) => si.classList.remove("selected"));
              this.classList.add("selected");
              select.classList.remove("active");
            });
          });
        });
      });
    </script>
  </body>
</html>
