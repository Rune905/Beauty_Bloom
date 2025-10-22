<?php
/**
 * Beauty Bloom Setup Script
 * Run this script to test your database connection and API setup
 */

echo "<h1>Beauty Bloom Setup</h1>";
echo "<h2>Database Connection Test</h2>";

// Test database connection
try {
    include_once 'API/config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test if database exists and has tables
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p style='color: green;'>✅ Database 'beauty_bloom' exists with " . count($tables) . " tables</p>";
            echo "<p>Tables found:</p><ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ Database exists but no tables found. Please import the schema.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>API Endpoints Test</h2>";

// Test API endpoints
$endpoints = [
    'Categories' => 'API/api/categories/read.php',
    'Products' => 'API/api/products/read.php',
    'Featured Products' => 'API/api/products/featured.php'
];

foreach ($endpoints as $name => $endpoint) {
    echo "<h3>Testing $name</h3>";
    
    if (file_exists($endpoint)) {
        echo "<p style='color: green;'>✅ File exists: $endpoint</p>";
        
        // Test if endpoint is accessible
        $url = "http://localhost/React/naznin/my-app/$endpoint";
        echo "<p>Testing URL: <a href='$url' target='_blank'>$url</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ File not found: $endpoint</p>";
    }
}

echo "<h2>Setup Instructions</h2>";
echo "<ol>";
echo "<li>Make sure XAMPP is running (Apache and MySQL)</li>";
echo "<li>Create database 'beauty_bloom' in phpMyAdmin</li>";
echo "<li>Import the schema from: <code>API/Database/beauty_bloom_schema.sql</code></li>";
echo "<li>Update database credentials in: <code>API/config/database.php</code></li>";
echo "<li>Run sample data script: <a href='API/sample_data.php'>API/sample_data.php</a></li>";
echo "<li>Start React dev server: <code>npm run dev</code></li>";
echo "</ol>";

echo "<h2>Next Steps</h2>";
echo "<p>After completing the setup:</p>";
echo "<ul>";
echo "<li>Frontend will be available at: <code>http://localhost:5173</code></li>";
echo "<li>API endpoints will be available at: <code>http://localhost/React/naznin/my-app/API/api/</code></li>";
echo "<li>Test the website by navigating to the home page</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Note:</strong> This is a setup script. Remove it after successful setup for security.</p>";
?>
