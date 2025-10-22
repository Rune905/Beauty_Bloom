<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$product->id = isset($_GET['id']) ? $_GET['id'] : die();

if($product->readOne()) {
    $product_arr = array(
        "id" => $product->id,
        "name" => $product->name,
        "slug" => $product->slug,
        "description" => $product->description,
        "short_description" => $product->short_description,
        "sku" => $product->sku,
        "category_id" => $product->category_id,
        "brand_id" => $product->brand_id,
        "price" => $product->price,
        "sale_price" => $product->sale_price,
        "stock_quantity" => $product->stock_quantity,
        "is_featured" => $product->is_featured,
        "is_hot_deal" => $product->is_hot_deal,
        "is_active" => $product->is_active,
        "created_at" => $product->created_at
    );

    http_response_code(200);
    echo json_encode($product_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Product not found."));
}
?>
