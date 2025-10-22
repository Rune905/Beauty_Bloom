import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { ChevronRight, Package, Star, ShoppingCart, Eye, Heart } from 'lucide-react';

const Categories = () => {
  const [categories, setCategories] = useState([]);
  const [categoryProducts, setCategoryProducts] = useState({});
  const [loading, setLoading] = useState(true);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [viewMode, setViewMode] = useState('grid');

  const fetchCategories = useCallback(async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/categories/read.php');
      const data = await response.json();
      if (data.records) {
        setCategories(data.records);
        // Fetch product counts for each category
        await fetchCategoryProductCounts(data.records);
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  const fetchCategoryProductCounts = async (categoriesList) => {
    const productCounts = {};
    
    for (const category of categoriesList) {
      try {
        const response = await fetch(`http://localhost/React/naznin/my-app/API/api/products/read.php?category_id=${category.id}&limit=1`);
        const data = await response.json();
        productCounts[category.id] = data.total || 0;
      } catch (error) {
        console.error(`Error fetching product count for category ${category.id}:`, error);
        productCounts[category.id] = 0;
      }
    }
    
    setCategoryProducts(productCounts);
  };

  const fetchCategoryProducts = async (categoryId) => {
    try {
      const response = await fetch(`http://localhost/React/naznin/my-app/API/api/products/read.php?category_id=${categoryId}&limit=6`);
      const data = await response.json();
      return data.records || [];
    } catch (error) {
      console.error('Error fetching category products:', error);
      return [];
    }
  };

  const getCategoryIcon = (categoryName) => {
    const name = categoryName.toLowerCase();
    if (name.includes('skin')) return '🧴';
    if (name.includes('makeup')) return '💄';
    if (name.includes('hair')) return '💇‍♀️';
    if (name.includes('fragrance')) return '🌸';
    if (name.includes('body')) return '🛁';
    if (name.includes('personal')) return '🧼';
    return '✨';
  };

  const getCategoryColor = (categoryId) => {
    const colors = [
      'bg-gradient-beauty',
      'bg-gradient-danger', 
      'bg-primary',
      'bg-success',
      'bg-warning',
      'bg-info'
    ];
    return colors[categoryId % colors.length];
  };

  useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

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
    <div className="min-vh-100 bg-light py-5">
      <div className="container">
        {/* Page Header */}
        <div className="text-center mb-5">
          <h1 className="display-4 fw-bold text-dark mb-3">
            🛍️ Product Categories
          </h1>
          <p className="lead text-muted mb-0">
            Explore our comprehensive range of beauty and personal care products organized by category
          </p>
          <div className="mt-3">
            <span className="badge bg-primary fs-6 px-3 py-2">
              {categories.length} Categories Available
            </span>
          </div>
        </div>

        {/* Categories Grid */}
        <div className="row g-4 mb-5">
          {categories.map((category) => (
            <div key={category.id} className="col-md-6 col-lg-4">
              <div className="card border-0 shadow-sm h-100 hover-lift">
                <div className="card-body text-center p-4">
                  {/* Category Icon */}
                  <div className={`${getCategoryColor(category.id)} rounded-circle d-inline-flex align-items-center justify-content-center mb-4`} 
                       style={{width: '80px', height: '80px'}}>
                    <span className="display-6 text-white">
                      {getCategoryIcon(category.name)}
                    </span>
                  </div>

                  {/* Category Name */}
                  <h3 className="h4 fw-bold text-dark mb-3">
                    {category.name}
                  </h3>

                  {/* Category Description */}
                  {category.description && (
                    <p className="text-muted mb-4">
                      {category.description}
                    </p>
                  )}

                  {/* Product Count */}
                  <div className="mb-4">
                    <span className="badge bg-light text-dark fs-6 px-3 py-2">
                      {categoryProducts[category.id] || 0} Products
                    </span>
                  </div>

                  {/* Action Buttons */}
                  <div className="d-grid gap-2">
                    <Link
                      to={`/products?category=${category.id}`}
                      className="btn btn-primary fw-bold"
                    >
                      <Package className="me-2" size={16} />
                      Explore Category
                    </Link>
                    <button
                      className="btn btn-outline-secondary btn-sm"
                      onClick={() => setSelectedCategory(category)}
                      data-bs-toggle="modal"
                      data-bs-target="#categoryModal"
                    >
                      <Eye className="me-1" size={14} />
                      Quick View
                    </button>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Category Statistics */}
        <div className="row g-4 mb-5">
          <div className="col-12">
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-primary text-white">
                <h5 className="mb-0">
                  📊 Category Statistics
                </h5>
              </div>
              <div className="card-body">
                <div className="row g-3">
                  <div className="col-md-3 col-6">
                    <div className="text-center">
                      <div className="h3 fw-bold text-primary mb-1">
                        {categories.length}
                      </div>
                      <div className="text-muted">Total Categories</div>
                    </div>
                  </div>
                  <div className="col-md-3 col-6">
                    <div className="text-center">
                      <div className="h3 fw-bold text-success mb-1">
                        {Object.values(categoryProducts).reduce((sum, count) => sum + count, 0)}
                      </div>
                      <div className="text-muted">Total Products</div>
                    </div>
                  </div>
                  <div className="col-md-3 col-6">
                    <div className="text-center">
                      <div className="h3 fw-bold text-warning mb-1">
                        {Math.max(...Object.values(categoryProducts))}
                      </div>
                      <div className="text-muted">Most Products</div>
                    </div>
                  </div>
                  <div className="col-md-3 col-6">
                    <div className="text-center">
                      <div className="h3 fw-bold text-info mb-1">
                        {Math.min(...Object.values(categoryProducts))}
                      </div>
                      <div className="text-muted">Least Products</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Additional Info */}
        <div className="row">
          <div className="col-12">
            <div className="card border-0 shadow-sm">
              <div className="card-body text-center p-5">
                <h2 className="h3 fw-bold text-dark mb-4">
                  Can't find what you're looking for?
                </h2>
                <p className="text-muted mb-4">
                  Browse our complete product catalog or use the search function to find specific items
                </p>
                <div className="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                  <Link
                    to="/products"
                    className="btn btn-primary btn-lg px-4 py-3 fw-bold"
                  >
                    <Package className="me-2" size={18} />
                    View All Products
                  </Link>
                  <Link
                    to="/"
                    className="btn btn-outline-primary btn-lg px-4 py-3 fw-bold"
                  >
                    <ChevronRight className="me-2" size={18} />
                    Back to Home
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Category Modal */}
      <div className="modal fade" id="categoryModal" tabIndex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div className="modal-dialog modal-lg">
          <div className="modal-content">
            <div className="modal-header">
              <h5 className="modal-title" id="categoryModalLabel">
                {selectedCategory?.name} Category
              </h5>
              <button type="button" className="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div className="modal-body">
              {selectedCategory && (
                <div>
                  <div className="row mb-4">
                    <div className="col-md-4 text-center">
                      <div className={`${getCategoryColor(selectedCategory.id)} rounded-circle d-inline-flex align-items-center justify-content-center mb-3`} 
                           style={{width: '100px', height: '100px'}}>
                        <span className="display-4 text-white">
                          {getCategoryIcon(selectedCategory.name)}
                        </span>
                      </div>
                    </div>
                    <div className="col-md-8">
                      <h4 className="fw-bold mb-2">{selectedCategory.name}</h4>
                      <p className="text-muted mb-3">{selectedCategory.description}</p>
                      <div className="d-flex gap-3">
                        <span className="badge bg-primary fs-6 px-3 py-2">
                          {categoryProducts[selectedCategory.id] || 0} Products
                        </span>
                        <span className="badge bg-success fs-6 px-3 py-2">
                          Active Category
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div className="text-center">
                    <Link
                      to={`/products?category=${selectedCategory.id}`}
                      className="btn btn-primary btn-lg px-5 py-3 fw-bold"
                      data-bs-dismiss="modal"
                    >
                      <Package className="me-2" size={18} />
                      Browse All {selectedCategory.name} Products
                    </Link>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Categories;
