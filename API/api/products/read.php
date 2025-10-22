<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Product.php';

// Suppress error display and log errors
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

// Get query parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

$stmt = null;

if ($search) {
    $stmt = $product->search($search, $limit);
} elseif ($category_id) {
    $stmt = $product->getByCategory($category_id, $limit, $offset);
} else {
    $stmt = $product->read($limit, $offset);
}

$num = $stmt->rowCount();

if ($num > 0) {
    $products_arr = array();
    $products_arr["records"] = array();
    $products_arr["total"] = $num;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $images = [];
        try {
            $images = $product->getImages($id);
            if (!is_array($images)) {
                $images = [];
            }
        } catch (Exception $e) {
            $images = [];
        }
        $product_item = array(
            "id" => $id,
            "name" => $name,
            "slug" => $slug,
            "description" => $description,
            "short_description" => $short_description,
            "sku" => $sku,
            "category_id" => $category_id,
            "category_name" => $category_name,
            "brand_id" => $brand_id,
            "brand_name" => $brand_name,
            "price" => $price,
            "sale_price" => $sale_price,
            "stock_quantity" => $stock_quantity,
            "is_featured" => $is_featured,
            "is_hot_deal" => $is_hot_deal,
            "is_active" => $is_active,
            "created_at" => $created_at,
            "images" => isset($image) && $image ? [$image] : []
        );

        array_push($products_arr["records"], $product_item);
    }

    http_response_code(200);
    echo json_encode($products_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No products found."));
}
?>
