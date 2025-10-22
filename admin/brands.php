<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

include_once '../API/config/database.php';
$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $slug = strtolower(str_replace(' ', '-', $name));
                $description = trim($_POST['description']);
                $website = trim($_POST['website']);
                
                if (!empty($name)) {
                    try {
                        $query = "INSERT INTO brands (name, slug, description, website) VALUES (?, ?, ?, ?)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$name, $slug, $description, $website]);
                        $message = "Brand added successfully!";
                    } catch (Exception $e) {
                        $error = "Error adding brand: " . $e->getMessage();
                    }
                } else {
                    $error = "Brand name is required!";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $slug = strtolower(str_replace(' ', '-', $name));
                $description = trim($_POST['description']);
                $website = trim($_POST['website']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if (!empty($name)) {
                    try {
                        $query = "UPDATE brands SET name = ?, slug = ?, description = ?, website = ?, is_active = ? WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$name, $slug, $description, $website, $is_active, $id]);
                        $message = "Brand updated successfully!";
                    } catch (Exception $e) {
                        $error = "Error updating brand: " . $e->getMessage();
                    }
                } else {
                    $error = "Brand name is required!";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    // Check if brand has products
                    $check_query = "SELECT COUNT(*) as count FROM products WHERE brand_id = ?";
                    $stmt = $db->prepare($check_query);
                    $stmt->execute([$id]);
                    $product_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    
                    if ($product_count > 0) {
                        $error = "Cannot delete brand. It has {$product_count} product(s).";
                    } else {
                        $query = "DELETE FROM brands WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$id]);
                        $message = "Brand deleted successfully!";
                    }
                } catch (Exception $e) {
                    $error = "Error deleting brand: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all brands
$query = "SELECT * FROM brands ORDER BY name";
$stmt = $db->query($query);
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brands Management - Beauty Bloom Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(236, 72, 153, 0.2);
            color: #ec4899;
            transform: translateX(5px);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-brand {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-white text-center mb-4">
                        <i class="fas fa-crown me-2"></i>
                        <span class="navbar-brand">Beauty Bloom</span>
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i>Categories
                        </a>
                        <a class="nav-link active" href="brands.php">
                            <i class="fas fa-star"></i>Brands
                        </a>
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i>Products
                        </a>
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i>Orders
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i>Users
                        </a>
                        <a class="nav-link" href="admins.php">
                            <i class="fas fa-user-shield"></i>Admins
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                    <div class="container-fluid">
                        <h4 class="mb-0">Brands Management</h4>
                        <div class="navbar-nav ms-auto">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-2"></i>
                                    <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="p-4">
                    <!-- Messages -->
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Add Brand Form -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-plus me-2 text-success"></i>
                                Add New Brand
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="add">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Brand Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" placeholder="https://example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Add Brand
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Brands List -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>
                                All Brands
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($brands)): ?>
                                <p class="text-muted text-center py-4">No brands found. Add your first brand above.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Slug</th>
                                                <th>Website</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($brands as $brand): ?>
                                                <tr>
                                                    <td><?php echo $brand['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($brand['name']); ?></strong>
                                                    </td>
                                                    <td>
                                                        <code><?php echo htmlspecialchars($brand['slug']); ?></code>
                                                    </td>
                                                    <td>
                                                        <?php if ($brand['website']): ?>
                                                            <a href="<?php echo htmlspecialchars($brand['website']); ?>" target="_blank" class="text-decoration-none">
                                                                <i class="fas fa-external-link-alt me-1"></i>
                                                                Visit
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No website</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($brand['description']): ?>
                                                            <span class="text-muted"><?php echo htmlspecialchars(substr($brand['description'], 0, 50)) . (strlen($brand['description']) > 50 ? '...' : ''); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">No description</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $brand['is_active'] ? 'success' : 'danger'; ?>">
                                                            <?php echo $brand['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" 
                                                                onclick="editBrand(<?php echo htmlspecialchars(json_encode($brand)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deleteBrand(<?php echo $brand['id']; ?>, '<?php echo htmlspecialchars($brand['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Brand Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="edit_website" name="website" placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the brand "<strong id="delete_brand_name"></strong>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_brand_id">
                        <button type="submit" class="btn btn-danger">Delete Brand</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBrand(brand) {
            document.getElementById('edit_id').value = brand.id;
            document.getElementById('edit_name').value = brand.name;
            document.getElementById('edit_website').value = brand.website || '';
            document.getElementById('edit_description').value = brand.description || '';
            document.getElementById('edit_is_active').checked = brand.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editBrandModal')).show();
        }
        
        function deleteBrand(id, name) {
            document.getElementById('delete_brand_id').value = id;
            document.getElementById('delete_brand_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteBrandModal')).show();
        }
    </script>
</body>
</html>
