<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../API/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    http_response_code(400);
    exit('Invalid order ID');
}

$orderId = $_GET['order_id'];

try {
    // Get order details
    $query = "
        SELECT 
            o.*,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            ua.address_line1,
            ua.address_line2,
            ua.city,
            ua.state,
            ua.postal_code,
            ua.country
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN user_addresses ua ON o.shipping_address_id = ua.id
        WHERE o.id = ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        http_response_code(404);
        exit('Order not found');
    }
    
    // Get order items
    $query = "
        SELECT 
            oi.*,
            p.name as product_name,
            p.image as product_image,
            p.slug as product_slug
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order status history
    $query = "
        SELECT 
            osh.*,
            a.username as admin_username
        FROM order_status_history osh
        LEFT JOIN admins a ON osh.updated_by = a.id
        WHERE osh.order_id = ?
        ORDER BY osh.updated_at DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$orderId]);
    $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Error fetching order details: ' . $e->getMessage());
}

// Calculate totals
$subtotal = 0;
$tax = 0;
$shipping = 0;
$total = 0;

foreach ($orderItems as $item) {
    $subtotal += $item['quantity'] * $item['price'];
}

$tax = $order['tax_amount'] ?? 0;
$shipping = $order['shipping_cost'] ?? 0;
$total = $subtotal + $tax + $shipping;
?>

<div class="row">
    <!-- Order Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Order Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-4"><strong>Order ID:</strong></div>
                    <div class="col-8">#<?php echo $order['id']; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Order Number:</strong></div>
                    <div class="col-8"><?php echo $order['order_number'] ?? 'N/A'; ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Date:</strong></div>
                    <div class="col-8"><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Status:</strong></div>
                    <div class="col-8">
                        <?php
                        $statusClass = 'status-' . strtolower($order['status']);
                        $statusText = ucfirst($order['status']);
                        ?>
                        <span class="badge order-status <?php echo $statusClass; ?>">
                            <?php echo $statusText; ?>
                        </span>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Payment Method:</strong></div>
                    <div class="col-8"><?php echo ucfirst($order['payment_method'] ?? 'N/A'); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Payment Status:</strong></div>
                    <div class="col-8">
                        <span class="badge <?php echo $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                            <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-4"><strong>Name:</strong></div>
                    <div class="col-8"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Email:</strong></div>
                    <div class="col-8"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-4"><strong>Phone:</strong></div>
                    <div class="col-8"><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <?php if ($order['address_line1']): ?>
                        <div><?php echo htmlspecialchars($order['address_line1']); ?></div>
                        <?php if ($order['address_line2']): ?>
                            <div><?php echo htmlspecialchars($order['address_line2']); ?></div>
                        <?php endif; ?>
                        <div>
                            <?php echo htmlspecialchars($order['city'] . ', ' . $order['state'] . ' ' . $order['postal_code']); ?>
                        </div>
                        <div><?php echo htmlspecialchars($order['country']); ?></div>
                    <?php else: ?>
                        <div class="text-muted">No shipping address provided</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order Items</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                                                                    <?php if (!empty($item['product_image'])): ?>
                                                    <img src="../API/uploads/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="product-image me-2">
                                    <?php else: ?>
                                        <div class="product-image bg-light d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Summary -->
<div class="row">
    <div class="col-md-6">
        <!-- Status History -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Status History</h6>
            </div>
            <div class="card-body">
                <?php if ($statusHistory): ?>
                    <div class="timeline">
                        <?php foreach ($statusHistory as $status): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 20px; height: 20px;">
                                        <i class="fas fa-circle text-white" style="font-size: 8px;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">
                                        <?php echo ucfirst($status['status']); ?>
                                        <?php if ($status['admin_username']): ?>
                                            <small class="text-muted">by <?php echo htmlspecialchars($status['admin_username']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?php echo date('M d, Y h:i A', strtotime($status['updated_at'])); ?>
                                    </div>
                                    <?php if ($status['note']): ?>
                                        <div class="mt-1">
                                            <small class="text-muted"><?php echo htmlspecialchars($status['note']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No status history available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Order Totals -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span>$<?php echo number_format($shipping, 2); ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-status {
    font-size: 0.75rem;
    font-weight: 600;
}
.status-pending { background-color: #ffc107; color: #000; }
.status-processing { background-color: #17a2b8; color: #fff; }
.status-shipped { background-color: #007bff; color: #fff; }
.status-delivered { background-color: #28a745; color: #fff; }
.status-cancelled { background-color: #dc3545; color: #fff; }
.status-refunded { background-color: #6c757d; color: #fff; }
.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}
</style>
