<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/Brand.php';

$database = new Database();
$db = $database->getConnection();

$brand = new Brand($db);

$stmt = $brand->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $brands_arr = array();
    $brands_arr["records"] = array();
    $brands_arr["total"] = $num;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $brand_item = array(
            "id" => $id,
            "name" => $name,
            "slug" => $slug,
            "description" => $description,
            "website" => $website,
            "is_active" => $is_active,
            "created_at" => $created_at
        );

        array_push($brands_arr["records"], $brand_item);
    }

    http_response_code(200);
    echo json_encode($brands_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No brands found."));
}
?> 