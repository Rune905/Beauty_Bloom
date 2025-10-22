import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Plus, 
  Search, 
  Filter, 
  Edit, 
  Trash2, 
  Eye, 
  Package,
  MoreVertical,
  Star,
  TrendingUp
} from 'lucide-react';

const Products = () => {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [selectedBrand, setSelectedBrand] = useState('');
  const [sortBy, setSortBy] = useState('newest');
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchProducts();
    fetchCategories();
    fetchBrands();
  }, []);

  const fetchProducts = async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/admin/products.php');
      const data = await response.json();
      
      if (data.success) {
        setProducts(data.data);
      }
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchCategories = async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/categories/read.php');
      const data = await response.json();
      
      if (data.records) {
        setCategories(data.records);
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  };

  const fetchBrands = async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/brands/read.php');
      const data = await response.json();
      
      if (data.records) {
        setBrands(data.records);
      }
    } catch (error) {
      console.error('Error fetching brands:', error);
    }
  };

  const handleDelete = async (productId) => {
    if (window.confirm('Are you sure you want to delete this product?')) {
      try {
        const response = await fetch(`http://localhost/React/naznin/my-app/API/api/admin/products.php?id=${productId}`, {
          method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
          setProducts(products.filter(product => product.id !== productId));
        }
      } catch (error) {
        console.error('Error deleting product:', error);
      }
    }
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-BD', {
      style: 'currency',
      currency: 'BDT',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price);
  };

  const getFilteredProducts = () => {
    let filtered = [...products];

    // Search filter
    if (searchTerm) {
      filtered = filtered.filter(product =>
        product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        product.description?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }

    // Category filter
    if (selectedCategory) {
      filtered = filtered.filter(product => product.category_id == selectedCategory);
    }

    // Brand filter
    if (selectedBrand) {
      filtered = filtered.filter(product => product.brand_id == selectedBrand);
    }

    // Sort
    filtered.sort((a, b) => {
      switch (sortBy) {
        case 'price-low':
          return (a.sale_price || a.price) - (b.sale_price || b.price);
        case 'price-high':
          return (b.sale_price || b.price) - (a.sale_price || a.price);
        case 'name':
          return a.name.localeCompare(b.name);
        case 'newest':
        default:
          return new Date(b.created_at) - new Date(a.created_at);
      }
    });

    return filtered;
  };

  const filteredProducts = getFilteredProducts();

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center min-vh-100">
        <div className="spinner-border text-primary" role="status" style={{width: '3rem', height: '3rem'}}>
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="min-vh-100 bg-light">
      <div className="container-fluid">
        {/* Header */}
        <div className="d-flex justify-content-between align-items-center py-4">
          <div>
            <h1 className="h2 fw-bold text-dark mb-1">Products Management</h1>
            <p className="text-muted mb-0">Manage your product catalog</p>
          </div>
          <Link to="/admin/products/new" className="btn btn-primary">
            <Plus size={16} className="me-2" />
            Add Product
          </Link>
        </div>

        {/* Filters */}
        <div className="card border-0 shadow-sm mb-4">
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-4">
                <div className="input-group">
                  <span className="input-group-text">
                    <Search size={16} />
                  </span>
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Search products..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                  />
                </div>
              </div>
              
              <div className="col-md-2">
                <select
                  className="form-select"
                  value={selectedCategory}
                  onChange={(e) => setSelectedCategory(e.target.value)}
                >
                  <option value="">All Categories</option>
                  {categories.map(category => (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  ))}
                </select>
              </div>
              
              <div className="col-md-2">
                <select
                  className="form-select"
                  value={selectedBrand}
                  onChange={(e) => setSelectedBrand(e.target.value)}
                >
                  <option value="">All Brands</option>
                  {brands.map(brand => (
                    <option key={brand.id} value={brand.id}>
                      {brand.name}
                    </option>
                  ))}
                </select>
              </div>
              
              <div className="col-md-2">
                <select
                  className="form-select"
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                >
                  <option value="newest">Newest First</option>
                  <option value="name">Name A-Z</option>
                  <option value="price-low">Price Low-High</option>
                  <option value="price-high">Price High-Low</option>
                </select>
              </div>
              
              <div className="col-md-2">
                <button
                  className="btn btn-outline-secondary w-100"
                  onClick={() => {
                    setSearchTerm('');
                    setSelectedCategory('');
                    setSelectedBrand('');
                    setSortBy('newest');
                  }}
                >
                  Clear Filters
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Products Grid */}
        <div className="row g-4">
          {filteredProducts.length > 0 ? (
            filteredProducts.map((product) => (
              <div key={product.id} className="col-xl-3 col-lg-4 col-md-6">
                <div className="card border-0 shadow-sm h-100">
                  {/* Product Image */}
                  <div className="position-relative">
                    <img
                      src={`http://localhost/React/naznin/my-app/API/uploads/products/${product.image || 'placeholder.jpg'}`}
                      alt={product.name}
                      className="card-img-top"
                      style={{height: '200px', objectFit: 'cover'}}
                      onError={(e) => {
                        e.target.src = `https://via.placeholder.com/300x200/667eea/ffffff?text=${product.name.charAt(0)}`;
                      }}
                    />
                    
                    {/* Badges */}
                    <div className="position-absolute top-0 start-0 p-2">
                      {product.is_featured && (
                        <span className="badge bg-warning me-1">
                          <Star size={12} className="me-1" />
                          Featured
                        </span>
                      )}
                      {product.is_hot_deal && (
                        <span className="badge bg-danger">
                          <TrendingUp size={12} className="me-1" />
                          Hot Deal
                        </span>
                      )}
                    </div>

                    {/* Stock Status */}
                    <div className="position-absolute top-0 end-0 p-2">
                      <span className={`badge ${product.stock_quantity > 0 ? 'bg-success' : 'bg-danger'}`}>
                        {product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock'}
                      </span>
                    </div>

                    {/* Actions Dropdown */}
                    <div className="position-absolute bottom-0 end-0 p-2">
                      <div className="dropdown">
                        <button className="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                          <MoreVertical size={14} />
                        </button>
                        <ul className="dropdown-menu">
                          <li><Link className="dropdown-item" to={`/admin/products/${product.id}`}>
                            <Eye size={14} className="me-2" />
                            View
                          </Link></li>
                          <li><Link className="dropdown-item" to={`/admin/products/${product.id}/edit`}>
                            <Edit size={14} className="me-2" />
                            Edit
                          </Link></li>
                          <li><hr className="dropdown-divider" /></li>
                          <li><button 
                            className="dropdown-item text-danger" 
                            onClick={() => handleDelete(product.id)}
                          >
                            <Trash2 size={14} className="me-2" />
                            Delete
                          </button></li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  {/* Product Info */}
                  <div className="card-body">
                    <h6 className="card-title fw-bold mb-2">{product.name}</h6>
                    <p className="text-muted small mb-2">{product.short_description}</p>
                    
                    <div className="d-flex justify-content-between align-items-center mb-2">
                      <div>
                        <span className="fw-bold text-primary">{formatPrice(product.sale_price || product.price)}</span>
                        {product.sale_price && product.sale_price < product.price && (
                          <span className="text-decoration-line-through text-muted ms-2">
                            {formatPrice(product.price)}
                          </span>
                        )}
                      </div>
                      <small className="text-muted">Stock: {product.stock_quantity}</small>
                    </div>

                    <div className="d-flex justify-content-between align-items-center">
                      <small className="text-muted">
                        {categories.find(c => c.id == product.category_id)?.name || 'Uncategorized'}
                      </small>
                      <small className="text-muted">
                        {brands.find(b => b.id == product.brand_id)?.name || 'No Brand'}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="col-12">
              <div className="text-center py-5">
                <Package className="text-muted mb-3" size={64} />
                <h4 className="text-muted mb-2">No products found</h4>
                <p className="text-muted mb-4">Try adjusting your search criteria or add your first product.</p>
                <Link to="/admin/products/new" className="btn btn-primary">
                  <Plus size={16} className="me-2" />
                  Add Your First Product
                </Link>
              </div>
            </div>
          )}
        </div>

        {/* Results Summary */}
        {filteredProducts.length > 0 && (
          <div className="mt-4 text-center">
            <p className="text-muted">
              Showing {filteredProducts.length} of {products.length} products
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default Products; 