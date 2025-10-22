<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->first_name) && !empty($data->last_name) && !empty($data->email) && !empty($data->password)) {
    // Check if email already exists
    if ($user->emailExists($data->email)) {
        http_response_code(400);
        echo json_encode(array(
            "status" => "error",
            "message" => "Email already exists"
        ));
        exit;
    }
    
    // Set user properties
    $user->first_name = $data->first_name;
    $user->last_name = $data->last_name;
    $user->email = $data->email;
    $user->phone = $data->phone ?? '';
    $user->address = $data->address ?? '';
    $user->password = $data->password;
    $user->role = 'customer'; // Default role
    
    if ($user->create()) {
        http_response_code(201);
        echo json_encode(array(
            "status" => "success",
            "message" => "User registered successfully"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => "Unable to register user"
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "status" => "error",
        "message" => "Required fields are missing"
    ));
}
?> 