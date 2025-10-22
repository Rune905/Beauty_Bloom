<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array(
        "success" => false,
        "message" => "Method not allowed"
    ));
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "No image file uploaded or upload error"
    ));
    exit();
}

$uploadedFile = $_FILES['image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$fileType = mime_content_type($uploadedFile['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid file type. Only JPEG, PNG, and WebP are allowed."
    ));
    exit();
}

// Validate file size (5MB limit)
$maxSize = 5 * 1024 * 1024; // 5MB in bytes
if ($uploadedFile['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "File size too large. Maximum size is 5MB."
    ));
    exit();
}

// Create upload directory if it doesn't exist
$uploadDir = '../../uploads/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
$timestamp = time();
$randomString = bin2hex(random_bytes(8));
$filename = 'product_' . $timestamp . '_' . $randomString . '.' . $fileExtension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($uploadedFile['tmp_name'], $filepath)) {
    // Get image dimensions
    $imageInfo = getimagesize($filepath);
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Create thumbnail
    $thumbnailDir = $uploadDir . 'thumbnails/';
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $thumbnailFilename = 'thumb_' . $filename;
    $thumbnailPath = $thumbnailDir . $thumbnailFilename;
    
    // Create thumbnail (resize to 300x300)
    createThumbnail($filepath, $thumbnailPath, 300, 300);
    
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "message" => "Image uploaded successfully",
        "filename" => $filename,
        "thumbnail" => $thumbnailFilename,
        "original_name" => $uploadedFile['name'],
        "size" => $uploadedFile['size'],
        "width" => $width,
        "height" => $height,
        "url" => "http://localhost/React/naznin/my-app/API/uploads/products/" . $filename,
        "thumbnail_url" => "http://localhost/React/naznin/my-app/API/uploads/products/thumbnails/" . $thumbnailFilename
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Failed to save uploaded file"
    ));
}

// Function to create thumbnail
function createThumbnail($sourcePath, $destinationPath, $maxWidth, $maxHeight) {
    $imageInfo = getimagesize($sourcePath);
    $sourceWidth = $imageInfo[0];
    $sourceHeight = $imageInfo[1];
    $sourceType = $imageInfo[2];
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
    $newWidth = round($sourceWidth * $ratio);
    $newHeight = round($sourceHeight * $ratio);
    
    // Create source image
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    // Create destination image
    $destinationImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and WebP
    if ($sourceType == IMAGETYPE_PNG || $sourceType == IMAGETYPE_WEBP) {
        imagealphablending($destinationImage, false);
        imagesavealpha($destinationImage, true);
        $transparent = imagecolorallocatealpha($destinationImage, 255, 255, 255, 127);
        imagefill($destinationImage, 0, 0, $transparent);
    }
    
    // Resize image
    imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
    
    // Save thumbnail
    $success = false;
    switch ($sourceType) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($destinationImage, $destinationPath, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($destinationImage, $destinationPath, 8);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($destinationImage, $destinationPath, 85);
            break;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($destinationImage);
    
    return $success;
}
?> 