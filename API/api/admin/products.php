<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all products for admin
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN brands b ON p.brand_id = b.id 
                  ORDER BY p.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $products_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $product_item = array(
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "slug" => $row['slug'],
                    "description" => $row['description'],
                    "short_description" => $row['short_description'],
                    "sku" => $row['sku'],
                    "category_id" => $row['category_id'],
                    "category_name" => $row['category_name'],
                    "brand_id" => $row['brand_id'],
                    "brand_name" => $row['brand_name'],
                    "price" => $row['price'],
                    "sale_price" => $row['sale_price'],
                    "stock_quantity" => $row['stock_quantity'],
                    "is_featured" => $row['is_featured'],
                    "is_hot_deal" => $row['is_hot_deal'],
                    "is_active" => $row['is_active'],
                    "image" => $row['image'],
                    "created_at" => $row['created_at'],
                    "updated_at" => $row['updated_at']
                );
                
                array_push($products_arr, $product_item);
            }
            
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "data" => $products_arr
            ));
        } else {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "data" => []
            ));
        }
        break;
        
    case 'POST':
        // Create new product
        $data = json_decode(file_get_contents("php://input"));
        
        if (!empty($data->name) && !empty($data->price)) {
            // Create product data array
            $productData = array(
                'name' => $data->name,
                'slug' => createSlug($data->name),
                'description' => $data->description ?? '',
                'short_description' => $data->short_description ?? '',
                'sku' => $data->sku ?? '',
                'category_id' => $data->category_id ?? null,
                'brand_id' => $data->brand_id ?? null,
                'price' => $data->price,
                'sale_price' => $data->sale_price ?? null,
                'stock_quantity' => $data->stock_quantity ?? 0,
                'is_featured' => $data->is_featured ?? 0,
                'is_hot_deal' => $data->is_hot_deal ?? 0,
                'is_active' => $data->is_active ?? 1,
                'image' => $data->image ?? ''
            );
            
            $productId = $product->create($productData);
            
            if ($productId) {
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Product created successfully",
                    "product_id" => $productId
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to create product"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Product name and price are required"
            ));
        }
        break;
        
    case 'PUT':
        // Update product
        $data = json_decode(file_get_contents("php://input"));
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if ($id && !empty($data->name)) {
            $product->id = $id;
            $product->name = $data->name;
            $product->slug = createSlug($data->name);
            $product->description = $data->description ?? '';
            $product->short_description = $data->short_description ?? '';
            $product->sku = $data->sku ?? '';
            $product->category_id = $data->category_id ?? null;
            $product->brand_id = $data->brand_id ?? null;
            $product->price = $data->price;
            $product->sale_price = $data->sale_price ?? null;
            $product->stock_quantity = $data->stock_quantity ?? 0;
            $product->is_featured = $data->is_featured ?? 0;
            $product->is_hot_deal = $data->is_hot_deal ?? 0;
            $product->is_active = $data->is_active ?? 1;
            $product->image = $data->image ?? '';
            
            if ($product->update()) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Product updated successfully"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to update product"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Product ID and name are required"
            ));
        }
        break;
        
    case 'DELETE':
        // Delete product
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if ($id) {
            if ($product->delete($id)) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Product deleted successfully"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to delete product"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Product ID is required"
            ));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array(
            "success" => false,
            "message" => "Method not allowed"
        ));
        break;
}

// Helper function to create slug
function createSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}
?> 