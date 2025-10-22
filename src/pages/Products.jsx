import { useState, useEffect, useCallback, useMemo, memo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Filter, Grid, List, Search, Star, ShoppingCart, Eye, Heart } from 'lucide-react';
import ProductCard from '../components/ProductCard';

const Products = ({ addToCart }) => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [viewMode, setViewMode] = useState('grid');
  const [sortBy, setSortBy] = useState('newest');
  const [priceRange, setPriceRange] = useState({ min: '', max: '' });
  const [selectedCategories, setSelectedCategories] = useState([]);
  const [selectedBrands, setSelectedBrands] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  const search = searchParams.get('search') || '';
  const category = searchParams.get('category') || '';

  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      let url = 'http://localhost/React/naznin/my-app/API/api/products/read.php';
      const params = new URLSearchParams();
      
      if (search) {
        params.append('search', search);
      } else if (category) {
        params.append('category_id', category);
      }
      
      params.append('limit', '100');
      
      if (params.toString()) {
        url += '?' + params.toString();
      }

      const response = await fetch(url);
      const data = await response.json();
      
      if (data.records) {
        setProducts(data.records);
      }
    } catch (error) {
      console.error('Error fetching products:', error);
    } finally {
      setLoading(false);
    }
  }, [search, category]);

  const fetchCategories = useCallback(async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/categories/read.php');
      const data = await response.json();
      if (data.records) {
        setCategories(data.records);
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  }, []);

  const fetchBrands = useCallback(async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/brands/read.php');
      const data = await response.json();
      if (data.records) {
        setBrands(data.records);
      }
    } catch (error) {
      console.error('Error fetching brands:', error);
    }
  }, []);

  const handlePriceFilter = useCallback(() => {
    // This will trigger the filtering via useMemo
  }, []);

  const clearFilters = useCallback(() => {
    setPriceRange({ min: '', max: '' });
    setSortBy('newest');
    setSelectedCategories([]);
    setSelectedBrands([]);
    setSearchTerm('');
    setSearchParams({});
  }, [setSearchParams]);

  const toggleCategory = useCallback((categoryId) => {
    setSelectedCategories(prev => 
      prev.includes(categoryId) 
        ? prev.filter(id => id !== categoryId)
        : [...prev, categoryId]
    );
  }, []);

  const toggleBrand = useCallback((brandId) => {
    setSelectedBrands(prev => 
      prev.includes(brandId) 
        ? prev.filter(id => id !== brandId)
        : [...prev, brandId]
    );
  }, []);

  // Filter and sort products in memory to avoid refetching
  const filteredAndSortedProducts = useMemo(() => {
    let filteredProducts = [...products];
    
    // Apply search filter
    if (searchTerm) {
      filteredProducts = filteredProducts.filter(product =>
        product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        product.description?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        product.short_description?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    // Apply category filter
    if (selectedCategories.length > 0) {
      filteredProducts = filteredProducts.filter(product =>
        selectedCategories.includes(product.category_id)
      );
    }
    
    // Apply brand filter
    if (selectedBrands.length > 0) {
      filteredProducts = filteredProducts.filter(product =>
        selectedBrands.includes(product.brand_id)
      );
    }
    
    // Apply price filter
    if (priceRange.min || priceRange.max) {
      filteredProducts = filteredProducts.filter(product => {
        const price = product.sale_price || product.price;
        if (priceRange.min && price < parseFloat(priceRange.min)) return false;
        if (priceRange.max && price > parseFloat(priceRange.max)) return false;
        return true;
      });
    }
    
    // Apply sorting
    filteredProducts.sort((a, b) => {
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
    
    return filteredProducts;
  }, [products, searchTerm, selectedCategories, selectedBrands, priceRange, sortBy]);

  useEffect(() => {
    fetchProducts();
    fetchCategories();
    fetchBrands();
  }, [fetchProducts, fetchCategories, fetchBrands]);

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-BD', {
      style: 'currency',
      currency: 'BDT',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price);
  };

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
        <div className="row mb-4">
          <div className="col-12">
            <div className="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
              <div>
                <h1 className="display-5 fw-bold text-dark mb-2">
                  {search ? `Search Results for "${search}"` : category ? 'Products by Category' : 'All Products'}
                </h1>
                <p className="lead text-muted mb-0">
                  {filteredAndSortedProducts.length} products found
                </p>
              </div>
              
              {/* Mobile Filter Toggle */}
              <button
                className="btn btn-outline-primary d-md-none mt-3"
                onClick={() => setShowFilters(!showFilters)}
              >
                <Filter className="me-2" size={16} />
                {showFilters ? 'Hide' : 'Show'} Filters
              </button>
            </div>
          </div>
        </div>

        <div className="row">
          {/* Sidebar Filters */}
          <div className={`col-lg-3 ${showFilters ? 'd-block' : 'd-none'} d-lg-block`}>
            <div className="card border-0 shadow-sm mb-4">
              <div className="card-header bg-primary text-white">
                <h5 className="mb-0">
                  <Filter className="me-2" size={16} />
                  Filters
                </h5>
              </div>
              <div className="card-body">
                
                {/* Search */}
                <div className="mb-4">
                  <label className="form-label fw-bold">Search Products</label>
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

                {/* Categories */}
                <div className="mb-4">
                  <label className="form-label fw-bold">Categories</label>
                  <div className="d-flex flex-column gap-2">
                    {categories.map((cat) => (
                      <div key={cat.id} className="form-check">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id={`category-${cat.id}`}
                          checked={selectedCategories.includes(cat.id)}
                          onChange={() => toggleCategory(cat.id)}
                        />
                        <label className="form-check-label" htmlFor={`category-${cat.id}`}>
                          {cat.name}
                        </label>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Brands */}
                <div className="mb-4">
                  <label className="form-label fw-bold">Brands</label>
                  <div className="d-flex flex-column gap-2">
                    {brands.map((brand) => (
                      <div key={brand.id} className="form-check">
                        <input
                          className="form-check-input"
                          type="checkbox"
                          id={`brand-${brand.id}`}
                          checked={selectedBrands.includes(brand.id)}
                          onChange={() => toggleBrand(brand.id)}
                        />
                        <label className="form-check-label" htmlFor={`brand-${brand.id}`}>
                          {brand.name}
                        </label>
                      </div>
                    ))}
                  </div>
                </div>

                {/* Price Range */}
                <div className="mb-4">
                  <label className="form-label fw-bold">Price Range (৳)</label>
                  <div className="row g-2">
                    <div className="col-6">
                      <input
                        type="number"
                        className="form-control form-control-sm"
                        placeholder="Min"
                        value={priceRange.min}
                        onChange={(e) => setPriceRange(prev => ({ ...prev, min: e.target.value }))}
                      />
                    </div>
                    <div className="col-6">
                      <input
                        type="number"
                        className="form-control form-control-sm"
                        placeholder="Max"
                        value={priceRange.max}
                        onChange={(e) => setPriceRange(prev => ({ ...prev, max: e.target.value }))}
                      />
                    </div>
                  </div>
                </div>

                {/* Clear Filters */}
                <button
                  onClick={clearFilters}
                  className="btn btn-outline-secondary w-100"
                >
                  Clear All Filters
                </button>
              </div>
            </div>
          </div>

          {/* Main Content */}
          <div className="col-lg-9">
            {/* Toolbar */}
            <div className="card border-0 shadow-sm mb-4">
              <div className="card-body">
                <div className="row align-items-center">
                  <div className="col-md-6 mb-3 mb-md-0">
                    <span className="text-muted">
                      Showing {filteredAndSortedProducts.length} products
                    </span>
                  </div>
                  <div className="col-md-6">
                    <div className="d-flex justify-content-md-end gap-3">
                      {/* Sort */}
                      <select
                        value={sortBy}
                        onChange={(e) => setSortBy(e.target.value)}
                        className="form-select form-select-sm"
                        style={{width: 'auto'}}
                      >
                        <option value="newest">Newest First</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="name">Name: A to Z</option>
                      </select>

                      {/* View Mode */}
                      <div className="btn-group" role="group">
                        <button
                          type="button"
                          className={`btn btn-sm ${viewMode === 'grid' ? 'btn-primary' : 'btn-outline-primary'}`}
                          onClick={() => setViewMode('grid')}
                        >
                          <Grid size={16} />
                        </button>
                        <button
                          type="button"
                          className={`btn btn-sm ${viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary'}`}
                          onClick={() => setViewMode('list')}
                        >
                          <List size={16} />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Products Grid/List */}
            {filteredAndSortedProducts.length > 0 ? (
              <div className={viewMode === 'grid' ? 'row g-4' : 'd-flex flex-column gap-3'}>
                {filteredAndSortedProducts.map((product) => (
                  viewMode === 'grid' ? (
                    <div key={product.id} className="col-md-6 col-lg-4">
                      <ProductCard
                        product={product}
                        addToCart={addToCart}
                      />
                    </div>
                  ) : (
                    <div key={product.id} className="card border-0 shadow-sm">
                      <div className="row g-0">
                        <div className="col-md-3">
                          <img
                            src={product.image_url || `/src/assets/image/${product.category_name?.toLowerCase() || 'skin'}/product-${product.id || '1'}.jpg`}
                            alt={product.name}
                            className="img-fluid rounded-start"
                            style={{height: '200px', objectFit: 'cover', width: '100%'}}
                          />
                        </div>
                        <div className="col-md-9">
                          <div className="card-body">
                            <div className="row">
                              <div className="col-md-8">
                                <h5 className="card-title fw-bold">{product.name}</h5>
                                <p className="card-text text-muted">{product.short_description}</p>
                                <div className="d-flex align-items-center gap-3 mb-3">
                                  <small className="text-muted">{product.brand_name}</small>
                                  <small className="text-muted">•</small>
                                  <small className="text-muted">{product.category_name}</small>
                                </div>
                                <div className="d-flex align-items-center gap-2 mb-3">
                                  <div className="text-warning">
                                    {[...Array(5)].map((_, i) => (
                                      <Star key={i} size={16} fill={i < 4 ? "currentColor" : "none"} />
                                    ))}
                                  </div>
                                  <small className="text-muted">(4.5)</small>
                                </div>
                              </div>
                              <div className="col-md-4 text-md-end">
                                <div className="mb-3">
                                  {product.sale_price && product.sale_price < product.price ? (
                                    <>
                                      <div className="text-decoration-line-through text-muted">৳{product.price}</div>
                                      <div className="fs-4 fw-bold text-danger">৳{product.sale_price}</div>
                                    </>
                                  ) : (
                                    <div className="fs-4 fw-bold text-primary">৳{product.price}</div>
                                  )}
                                </div>
                                <div className="d-grid gap-2">
                                  <button
                                    onClick={() => addToCart(product)}
                                    className="btn btn-primary btn-sm"
                                    disabled={product.stock_quantity === 0}
                                  >
                                    <ShoppingCart size={16} className="me-1" />
                                    {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
                                  </button>
                                  <button className="btn btn-outline-secondary btn-sm">
                                    <Eye size={16} className="me-1" />
                                    View Details
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  )
                ))}
              </div>
            ) : (
              <div className="text-center py-5">
                <Search className="display-1 text-muted mb-4" />
                <h3 className="h4 text-muted mb-3">No products found</h3>
                <p className="text-muted mb-4">
                  Try adjusting your search criteria or browse our categories
                </p>
                <button
                  onClick={clearFilters}
                  className="btn btn-primary"
                >
                  Clear All Filters
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default memo(Products);
