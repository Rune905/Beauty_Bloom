<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;

$stmt = $product->readFeatured($limit);
$num = $stmt->rowCount();

if ($num > 0) {
    $products_arr = array();
    $products_arr["records"] = array();
    $products_arr["total"] = $num;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

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
    echo json_encode(array("message" => "No featured products found."));
}
?>
