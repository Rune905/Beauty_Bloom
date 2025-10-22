<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/User.php';
include_once '../../models/Product.php';
include_once '../../models/Order.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Get total users
    $user = new User($db);
    $users_query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
    $users_stmt = $db->prepare($users_query);
    $users_stmt->execute();
    $total_users = $users_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total products
    $product = new Product($db);
    $products_query = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
    $products_stmt = $db->prepare($products_query);
    $products_stmt->execute();
    $total_products = $products_stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total orders and revenue
    $orders_query = "SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE status != 'cancelled'";
    $orders_stmt = $db->prepare($orders_query);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->fetch(PDO::FETCH_ASSOC);
    $total_orders = $orders_result['total_orders'];
    $total_revenue = $orders_result['total_revenue'];

    $stats = array(
        "totalUsers" => (int)$total_users,
        "totalProducts" => (int)$total_products,
        "totalOrders" => (int)$total_orders,
        "totalRevenue" => (float)$total_revenue
    );

    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "data" => $stats
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Error fetching dashboard statistics: " . $e->getMessage()
    ));
}
?> 