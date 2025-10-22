<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Sample brands data (must be inserted first due to foreign key constraints)
$sample_brands = [
    [
        'id' => 1,
        'name' => 'Beauty Essentials',
        'slug' => 'beauty-essentials',
        'description' => 'Premium beauty products for everyday use',
        'website' => 'https://beautyessentials.com'
    ],
    [
        'id' => 2,
        'name' => 'Natural Glow',
        'slug' => 'natural-glow',
        'description' => 'Natural and organic beauty products',
        'website' => 'https://naturalglow.com'
    ],
    [
        'id' => 3,
        'name' => 'Glamour Pro',
        'slug' => 'glamour-pro',
        'description' => 'Professional makeup and cosmetics',
        'website' => 'https://glamourpro.com'
    ]
];

// Sample products data
$sample_products = [
    [
        'name' => 'Natural Glow Face Cream',
        'slug' => 'natural-glow-face-cream',
        'description' => 'A luxurious face cream with natural ingredients for glowing skin',
        'short_description' => 'Natural face cream for glowing skin',
        'sku' => 'NGC001',
        'category_id' => 1, // Skincare
        'brand_id' => 2, // Natural Glow
        'price' => 2500.00,
        'sale_price' => 1999.00,
        'stock_quantity' => 50,
        'is_featured' => 1,
        'is_hot_deal' => 1
    ],
    [
        'name' => 'Matte Lipstick Collection',
        'slug' => 'matte-lipstick-collection',
        'description' => 'Long-lasting matte lipsticks in beautiful shades',
        'short_description' => 'Matte finish lipsticks',
        'sku' => 'MLC002',
        'category_id' => 2, // Makeup
        'brand_id' => 3, // Glamour Pro
        'price' => 1800.00,
        'sale_price' => 1450.00,
        'stock_quantity' => 75,
        'is_featured' => 1,
        'is_hot_deal' => 0
    ],
    [
        'name' => 'Argan Oil Hair Serum',
        'slug' => 'argan-oil-hair-serum',
        'description' => 'Nourishing hair serum with pure argan oil',
        'short_description' => 'Argan oil hair treatment',
        'sku' => 'AOH003',
        'category_id' => 3, // Hair Care
        'brand_id' => 2, // Natural Glow
        'price' => 2200.00,
        'sale_price' => 1850.00,
        'stock_quantity' => 40,
        'is_featured' => 0,
        'is_hot_deal' => 1
    ],
    [
        'name' => 'Floral Perfume Spray',
        'slug' => 'floral-perfume-spray',
        'description' => 'Elegant floral fragrance for everyday wear',
        'short_description' => 'Floral perfume spray',
        'sku' => 'FPS004',
        'category_id' => 4, // Fragrance
        'brand_id' => 1, // Beauty Bloom
        'price' => 3500.00,
        'sale_price' => 2800.00,
        'stock_quantity' => 30,
        'is_featured' => 1,
        'is_hot_deal' => 0
    ],
    [
        'name' => 'Body Scrub Exfoliator',
        'slug' => 'body-scrub-exfoliator',
        'description' => 'Gentle body scrub for smooth and soft skin',
        'short_description' => 'Body exfoliating scrub',
        'sku' => 'BSE005',
        'category_id' => 5, // Body Care
        'brand_id' => 2, // Natural Glow
        'price' => 1500.00,
        'sale_price' => 1200.00,
        'stock_quantity' => 60,
        'is_featured' => 0,
        'is_hot_deal' => 1
    ],
    [
        'name' => 'BB Cream Foundation',
        'slug' => 'bb-cream-foundation',
        'description' => 'All-in-one BB cream with SPF protection',
        'short_description' => 'BB cream with SPF',
        'sku' => 'BCF006',
        'category_id' => 2, // Makeup
        'brand_id' => 1, // Beauty Bloom
        'price' => 2400.00,
        'sale_price' => 1900.00,
        'stock_quantity' => 45,
        'is_featured' => 1,
        'is_hot_deal' => 0
    ]
];

try {
    // First, insert sample brands (required for foreign key constraints)
    $brand_query = "INSERT INTO brands (id, name, slug, description, website, is_active) 
                    VALUES (:id, :name, :slug, :description, :website, 1)
                    ON DUPLICATE KEY UPDATE 
                    name = VALUES(name), 
                    slug = VALUES(slug), 
                    description = VALUES(description), 
                    website = VALUES(website)";
    
    $brand_stmt = $db->prepare($brand_query);
    
    foreach ($sample_brands as $brand) {
        $brand_stmt->bindParam(':id', $brand['id']);
        $brand_stmt->bindParam(':name', $brand['name']);
        $brand_stmt->bindParam(':slug', $brand['slug']);
        $brand_stmt->bindParam(':description', $brand['description']);
        $brand_stmt->bindParam(':website', $brand['website']);
        $brand_stmt->execute();
    }
    
    echo "Sample brands inserted successfully!\n";
    
    // Then insert sample products (handle duplicates gracefully)
    $query = "INSERT INTO products (name, slug, description, short_description, sku, category_id, brand_id, price, sale_price, stock_quantity, is_featured, is_hot_deal, is_active) 
              VALUES (:name, :slug, :description, :short_description, :sku, :category_id, :brand_id, :price, :sale_price, :stock_quantity, :is_featured, :is_hot_deal, 1)
              ON DUPLICATE KEY UPDATE 
              name = VALUES(name),
              description = VALUES(description),
              short_description = VALUES(short_description),
              category_id = VALUES(category_id),
              brand_id = VALUES(brand_id),
              price = VALUES(price),
              sale_price = VALUES(sale_price),
              stock_quantity = VALUES(stock_quantity),
              is_featured = VALUES(is_featured),
              is_hot_deal = VALUES(is_hot_deal)";
    
    $stmt = $db->prepare($query);
    
    foreach ($sample_products as $product) {
        $stmt->bindParam(':name', $product['name']);
        $stmt->bindParam(':slug', $product['slug']);
        $stmt->bindParam(':description', $product['description']);
        $stmt->bindParam(':short_description', $product['short_description']);
        $stmt->bindParam(':sku', $product['sku']);
        $stmt->bindParam(':category_id', $product['category_id']);
        $stmt->bindParam(':brand_id', $product['brand_id']);
        $stmt->bindParam(':price', $product['price']);
        $stmt->bindParam(':sale_price', $product['sale_price']);
        $stmt->bindParam(':stock_quantity', $product['stock_quantity']);
        $stmt->bindParam(':is_featured', $product['is_featured']);
        $stmt->bindParam(':is_hot_deal', $product['is_hot_deal']);
        
        $stmt->execute();
    }
    
    echo "Sample products inserted successfully!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
