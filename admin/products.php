<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../API/config/database.php';
require_once '../API/models/Product.php';
require_once '../API/models/Category.php';
require_once '../API/models/Brand.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$category = new Category($db);
$brand = new Brand($db);

$message = '';
$error = '';

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
                                    $productData = [
                            'name' => $_POST['name'],
                            'description' => $_POST['description'],
                            'price' => $_POST['price'],
                            'sale_price' => !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
                            'stock_quantity' => $_POST['stock_quantity'],
                            'category_id' => $_POST['category_id'],
                            'brand_id' => $_POST['brand_id'],
                            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                            'is_hot_deal' => isset($_POST['is_hot_deal']) ? 1 : 0,
                            'status' => $_POST['status'],
                            'sku' => !empty($_POST['sku']) ? $_POST['sku'] : null
                        ];

            // Generate slug from name
            $productData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $productData['name'])));

            if ($_POST['action'] === 'add') {
                try {
                    $productId = $product->create($productData);
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $uploadDir = '../API/uploads/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $fileName = 'product_' . $productId . '_' . time() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            // Save image path to database
                            $product->updateImage($productId, $fileName);
                        }
                    }
                    
                    $message = 'Product added successfully!';
                } catch (Exception $e) {
                    $error = 'Error adding product: ' . $e->getMessage();
                }
            } else {
                // Edit existing product
                $productId = $_POST['product_id'];
                try {
                    $product->update($productId, $productData);
                    
                    // Handle image upload for edit
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $uploadDir = '../API/uploads/products/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $fileName = 'product_' . $productId . '_' . time() . '.' . $fileExtension;
                        $uploadPath = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                            // Save image path to database
                            $product->updateImage($productId, $fileName);
                        }
                    }
                    
                    $message = 'Product updated successfully!';
                } catch (Exception $e) {
                    $error = 'Error updating product: ' . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $productId = $_POST['product_id'];
            try {
                $product->delete($productId);
                $message = 'Product deleted successfully!';
            } catch (Exception $e) {
                $error = 'Error deleting product: ' . $e->getMessage();
            }
        }
    }
}

// Get all products, categories, and brands
$products = $product->readAll();
$categories = $category->read();
$brands = $brand->read();

// Get product for editing
$editProduct = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editProduct = $product->readOneById($_GET['edit']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-white mb-4">Admin Panel</h4>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags me-2"></i>Categories
                        </a>
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box me-2"></i>Products
                        </a>
                        <a class="nav-link" href="brands.php">
                            <i class="fas fa-copyright me-2"></i>Brands
                        </a>
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart me-2"></i>Orders
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-box me-2"></i>Products Management</h2>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </button>
                    </div>

                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Products Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">All Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                                                                    <thead>
                                                    <tr>
                                                        <th>Image</th>
                                                        <th>Name</th>
                                                        <th>SKU</th>
                                                        <th>Category</th>
                                                        <th>Brand</th>
                                                        <th>Price</th>
                                                        <th>Stock</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                    <tbody>
                                        <?php foreach ($products as $prod): ?>
                                            <tr>
                                                                                                <td>
                                                    <?php if (!empty($prod['image'])): ?>
                                                        <img src="../API/uploads/products/<?php echo htmlspecialchars($prod['image']); ?>"
                                                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                                             class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                                                                            <td>
                                                                <strong><?php echo htmlspecialchars($prod['name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($prod['slug']); ?></small>
                                                            </td>
                                                            <td>
                                                                <code class="text-primary"><?php echo htmlspecialchars($prod['sku'] ?? 'N/A'); ?></code>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($prod['category_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($prod['brand_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php if ($prod['sale_price'] && $prod['sale_price'] < $prod['price']): ?>
                                                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($prod['price'], 2); ?></span>
                                                        <br>
                                                        <span class="text-danger fw-bold">$<?php echo number_format($prod['sale_price'], 2); ?></span>
                                                    <?php else: ?>
                                                        $<?php echo number_format($prod['price'], 2); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $prod['stock_quantity'] > 10 ? 'bg-success' : ($prod['stock_quantity'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                        <?php echo $prod['stock_quantity']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $prod['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?> status-badge">
                                                        <?php echo ucfirst($prod['status']); ?>
                                                    </span>
                                                    <?php if ($prod['is_featured']): ?>
                                                        <span class="badge bg-primary status-badge ms-1">Featured</span>
                                                    <?php endif; ?>
                                                    <?php if ($prod['is_hot_deal']): ?>
                                                        <span class="badge bg-danger status-badge ms-1">Hot Deal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="?edit=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $editProduct ? 'edit' : 'add'; ?>">
                        <?php if ($editProduct): ?>
                            <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                        <?php endif; ?>

                                                            <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Product Name *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                    value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sku" class="form-label">SKU</label>
                                                <input type="text" class="form-control" id="sku" name="sku" 
                                                    value="<?php echo $editProduct ? htmlspecialchars($editProduct['sku']) : ''; ?>" 
                                                    placeholder="Leave empty to auto-generate">
                                                <small class="text-muted">Leave empty to auto-generate a unique SKU</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Category *</label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?php echo $cat['id']; ?>" 
                                                                <?php echo ($editProduct && $editProduct['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($cat['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="brand_id" class="form-label">Brand</label>
                                    <select class="form-select" id="brand_id" name="brand_id">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($brands as $brand_item): ?>
                                            <option value="<?php echo $brand_item['id']; ?>" 
                                                    <?php echo ($editProduct && $editProduct['brand_id'] == $brand_item['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand_item['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?php echo ($editProduct && $editProduct['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($editProduct && $editProduct['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="draft" <?php echo ($editProduct && $editProduct['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" 
                                               value="<?php echo $editProduct ? $editProduct['price'] : ''; ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="sale_price" name="sale_price" step="0.01" min="0" 
                                               value="<?php echo $editProduct ? $editProduct['sale_price'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" 
                                           value="<?php echo $editProduct ? $editProduct['stock_quantity'] : ''; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                               <?php echo ($editProduct && $editProduct['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Featured Product
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_hot_deal" name="is_hot_deal" 
                                               <?php echo ($editProduct && $editProduct['is_hot_deal']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_hot_deal">
                                            Hot Deal
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <?php if ($editProduct && $editProduct['image']): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Current image: <?php echo htmlspecialchars($editProduct['image']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editProduct ? 'Update Product' : 'Add Product'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the product "<span id="deleteProductName"></span>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteProduct(productId, productName) {
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deleteProductName').textContent = productName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Auto-show modal if editing
        <?php if ($editProduct): ?>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('addProductModal')).show();
        });
        <?php endif; ?>
    </script>
</body>
</html>
