<?php
// This script creates the first super admin account
// Run this once to set up your admin access
// DELETE THIS FILE AFTER CREATING YOUR ADMIN ACCOUNT

include_once '../API/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check if admin already exists
$check_query = "SELECT COUNT(*) as count FROM admins";
$stmt = $db->prepare($check_query);
$stmt->execute();
$admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($admin_count > 0) {
    echo "<h2>Admin Setup</h2>";
    echo "<p style='color: red;'>Admin account already exists! Please delete this file for security.</p>";
    echo "<p>You can now login at: <a href='index.php'>Admin Login</a></p>";
    exit();
}

// Default admin credentials (CHANGE THESE!)
$admin_username = "admin";
$admin_email = "admin@beautybloom.com";
$admin_password = "admin123"; // CHANGE THIS PASSWORD!
$admin_full_name = "Super Administrator";

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    $query = "INSERT INTO admins (username, email, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 'super_admin', 1)";
    $stmt = $db->prepare($query);
    $stmt->execute([$admin_username, $admin_email, $hashed_password, $admin_full_name]);
    
    echo "<h2>Admin Setup Complete!</h2>";
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ Admin Account Created Successfully</h3>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($admin_username) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin_email) . "</p>";
    echo "<p><strong>Password:</strong> " . htmlspecialchars($admin_password) . "</p>";
    echo "<p><strong>Role:</strong> Super Administrator</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4 style='color: #856404;'>⚠️ IMPORTANT SECURITY STEPS:</h4>";
    echo "<ol>";
    echo "<li><strong>DELETE THIS FILE</strong> (setup_admin.php) immediately after logging in</li>";
    echo "<li>Change your password through the admin panel</li>";
    echo "<li>Update your email address</li>";
    echo "<li>Consider creating additional admin accounts and removing this one</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
    
} catch (Exception $e) {
    echo "<h2>Admin Setup Failed</h2>";
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
}
h2 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}
</style>
