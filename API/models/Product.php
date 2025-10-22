<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $slug;
    public $description;
    public $short_description;
    public $sku;
    public $category_id;
    public $brand_id;
    public $price;
    public $sale_price;
    public $stock_quantity;
    public $is_featured;
    public $is_hot_deal;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all products
    public function read($limit = 10, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.is_active = 1
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Read featured products
    public function readFeatured($limit = 6) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.is_featured = 1 AND p.is_active = 1
                  ORDER BY p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Read hot deals
    public function readHotDeals($limit = 4) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.is_hot_deal = 1 AND p.is_active = 1 
                  AND (p.hot_deal_end_date IS NULL OR p.hot_deal_end_date > NOW())
                  ORDER BY p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Read single product (for frontend - with active filter)
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.id = :id AND p.is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->slug = $row['slug'];
            $this->description = $row['description'];
            $this->short_description = $row['short_description'];
            $this->sku = $row['sku'];
            $this->category_id = $row['category_id'];
            $this->brand_id = $row['brand_id'];
            $this->price = $row['price'];
            $this->sale_price = $row['price'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->is_featured = $row['is_featured'];
            $this->is_hot_deal = $row['is_hot_deal'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    // Search products
    public function search($search_term, $limit = 20) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.is_active = 1 
                  AND (p.name LIKE :search_term OR p.description LIKE :search_term)
                  ORDER BY p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(':search_term', $search_term);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Get products by category
    public function getByCategory($category_id, $limit = 20, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.category_id = :category_id AND p.is_active = 1
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    // Generate unique SKU
    private function generateSKU($name, $categoryId = null) {
        // Get category prefix if available
        $categoryPrefix = '';
        if ($categoryId) {
            $catQuery = "SELECT name FROM categories WHERE id = ?";
            $catStmt = $this->conn->prepare($catQuery);
            $catStmt->execute([$categoryId]);
            $category = $catStmt->fetch(PDO::FETCH_ASSOC);
            if ($category) {
                $categoryPrefix = strtoupper(substr($category['name'], 0, 3)) . '-';
            }
        }
        
        // Generate base SKU from product name
        $baseSKU = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($name, 0, 5)));
        
        // Add timestamp to ensure uniqueness
        $timestamp = date('YmdHis');
        
        return $categoryPrefix . $baseSKU . '-' . $timestamp;
    }

    // Create new product
    public function create($data) {
        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSKU($data['name'], $data['category_id']);
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, slug, description, short_description, sku, category_id, brand_id, 
                   price, sale_price, stock_quantity, is_featured, is_hot_deal, is_active, 
                   image, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['sku'],
            $data['category_id'],
            $data['brand_id'] ?? null,
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'],
            $data['is_featured'] ?? 0,
            $data['is_hot_deal'] ?? 0,
            $data['is_active'] ?? 1,
            $data['image'] ?? null
        ])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update product
    public function update($id, $data) {
        // Generate new SKU if name or category changed and no SKU provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSKU($data['name'], $data['category_id']);
        }
        
        $query = "UPDATE " . $this->table_name . " SET 
                  name = ?, slug = ?, description = ?, short_description = ?, sku = ?, 
                  category_id = ?, brand_id = ?, price = ?, sale_price = ?, stock_quantity = ?, 
                  is_featured = ?, is_hot_deal = ?, is_active = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['short_description'] ?? null,
            $data['sku'],
            $data['category_id'],
            $data['brand_id'] ?? null,
            $data['price'],
            $data['sale_price'] ?? null,
            $data['stock_quantity'],
            $data['is_featured'] ?? 0,
            $data['is_hot_deal'] ?? 0,
            $data['status'] === 'active' ? 1 : 0,
            $id
        ]);
    }

    // Delete product
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Update product image
    public function updateImage($id, $imageName) {
        $query = "UPDATE " . $this->table_name . " SET image = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$imageName, $id]);
    }

    // Read one product by ID (for admin)
    public function readOneById($id) {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  WHERE p.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Read all products (for admin - without active filter)
    public function readAll() {
        $query = "SELECT p.*, c.name as category_name, b.name as brand_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  LEFT JOIN brands b ON p.brand_id = b.id
                  ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Add image field as array for consistency
        foreach ($products as &$product) {
            $product['images'] = $product['image'] ? [$product['image']] : [];
        }
        return $products;
    }
}
?>
