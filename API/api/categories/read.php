<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Category.php';

$database = new Database();
$db = $database->getConnection();

$category = new Category($db);

$parent_id = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;

if ($parent_id !== null) {
    $stmt = $category->readSubcategories($parent_id);
} else {
    $stmt = $category->readMainCategories();
}

$num = $stmt->rowCount();

if ($num > 0) {
    $categories_arr = array();
    $categories_arr["records"] = array();
    $categories_arr["total"] = $num;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $category_item = array(
            "id" => $id,
            "name" => $name,
            "slug" => $slug,
            "description" => $description,
            "image" => $image,
            "parent_id" => $parent_id,
            "sort_order" => $sort_order,
            "is_active" => $is_active
        );

        array_push($categories_arr["records"], $category_item);
    }

    http_response_code(200);
    echo json_encode($categories_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No categories found."));
}
?>
