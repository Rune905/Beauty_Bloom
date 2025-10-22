import React, { useState, useEffect, useCallback, memo } from 'react';
import { Link } from 'react-router-dom';
import { Star, ShoppingCart, Eye } from 'lucide-react';
import ProductCard from '../components/ProductCard';

const Home = memo(({ addToCart }) => {
  const [featuredProducts, setFeaturedProducts] = useState([]);
  const [hotDeals, setHotDeals] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchFeaturedProducts = useCallback(async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/products/featured.php?limit=6');
      const data = await response.json();
      if (data.records) {
        console.log(data.records);
        setFeaturedProducts(data.records);
      }
    } catch (error) {
      console.error('Error fetching featured products:', error);
    }
  }, []);

  const fetchHotDeals = useCallback(async () => {
    try {
      const response = await fetch('http://localhost/React/naznin/my-app/API/api/products/hot_deals.php?limit=4');
      const data = await response.json();
      if (data.records) {
        console.log(data.records);
        setHotDeals(data.records);
      }
    } catch (error) {
      console.error('Error fetching hot deals:', error);
    }
  }, []);

  useEffect(() => {
    let mounted = true;
    
    const loadProducts = async () => {
      if (mounted) {
        setLoading(true);
        await Promise.all([
          fetchFeaturedProducts(),
          fetchHotDeals()
        ]);
        if (mounted) {
          setLoading(false);
        }
      }
    };

    loadProducts();
    
    return () => {
      mounted = false;
    };
  }, [fetchFeaturedProducts, fetchHotDeals]);

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
    <div className="min-vh-100">
      {/* Hero Section */}
      <section className="py-5 bg-gradient-beauty text-white">
        <div className="container">
          <div className="row justify-content-center text-center">
            <div className="col-lg-8">
              <h1 className="display-3 fw-bold mb-4">
                Discover Your
                <span className="text-warning"> Natural Beauty</span>
              </h1>
              <p className="lead mb-5">
                Premium beauty products that enhance your natural radiance. 
                Shop our curated collection of skincare, makeup, and beauty essentials.
              </p>
              <div className="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <Link
                  to="/products"
                  className="btn btn-light btn-lg px-4 py-3 fw-semibold hover-scale"
                >
                  Shop Now
                </Link>
                <Link
                  to="/categories"
                  className="btn btn-outline-light btn-lg px-4 py-3 fw-semibold"
                >
                  Browse Categories
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products */}
      <section className="py-5 bg-light">
        <div className="container">
          <div className="text-center mb-5">
            <h2 className="display-5 fw-bold text-dark mb-3">
              ⭐ Featured Products
            </h2>
            <p className="lead text-muted">
              Discover our handpicked selection of premium beauty products that customers love
            </p>
            <div className="mt-3">
              <span className="badge bg-primary fs-6 px-3 py-2">
                🌟 Customer Favorites
              </span>
            </div>
          </div>
          
          <div className="row g-4">
            {featuredProducts.length > 0 ? (
              featuredProducts.map((product) => (
                <div key={product.id} className="col-md-6 col-lg-4">
                  <div className="card h-100 border-0 shadow-sm hover-lift">
                    <div className="position-relative">
                      {product.images && product.images[0] && (
                        <img
                          src={`http://localhost/React/naznin/my-app/API/uploads/products/${product.images[0]}`}
                          alt={product.name}
                          className="card-img-top"
                          style={{ height: '250px', objectFit: 'cover' }}
                        />
                      )}
                      <div className="position-absolute top-0 end-0 m-2">
                        <span className="badge bg-primary fs-6 px-3 py-2">
                          ⭐ Featured
                        </span>
                      </div>
                    </div>
                    <div className="card-body d-flex flex-column">
                      <h5 className="card-title fw-bold text-dark mb-2">{product.name}</h5>
                      <p className="card-text text-muted flex-grow-1">{product.short_description}</p>
                      
                      <div className="mb-3">
                        <div className="d-flex align-items-center justify-content-between">
                          <span className="fs-4 fw-bold text-primary">৳{product.sale_price || product.price}</span>
                          {product.sale_price && product.sale_price < product.price && (
                            <span className="text-decoration-line-through text-muted">৳{product.price}</span>
                          )}
                        </div>
                      </div>
                      
                      <div className="d-grid gap-2">
                        <button 
                          className="btn btn-primary fw-bold"
                          onClick={() => addToCart(product)}
                        >
                          🛒 Add to Cart
                        </button>
                        <Link
                          to={`/products/${product.id}`}
                          className="btn btn-outline-primary"
                        >
                          👁️ View Details
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              ))
            ) : (
              <div className="col-12 text-center">
                <div className="py-5">
                  <div className="mb-3">
                    <span className="display-1 text-muted">📦</span>
                  </div>
                  <h4 className="text-muted">No featured products available at the moment.</h4>
                  <p className="text-muted">Check back soon for our latest featured products!</p>
                </div>
              </div>
            )}
          </div>
          
          <div className="text-center mt-5">
            <Link
              to="/products"
              className="btn btn-primary btn-lg px-5 py-3 fw-bold"
            >
              View All Products
            </Link>
          </div>
        </div>
      </section>

      {/* Hot Deals */}
      {hotDeals.length > 0 && (
        <section className="py-5 bg-gradient-danger text-white">
          <div className="container">
            <div className="text-center mb-5">
              <h2 className="display-5 fw-bold mb-3">
                🔥 Hot Deals - Limited Time Only!
              </h2>
              <p className="lead mb-0">
                Don't miss out on these amazing offers - Shop now before they're gone!
              </p>
              <div className="mt-3">
                <span className="badge bg-warning text-dark fs-6 px-3 py-2">
                  ⏰ Limited Time Offer
                </span>
              </div>
            </div>
            
            <div className="row g-4">
              {hotDeals.map((product) => (
                <div key={product.id} className="col-md-6 col-lg-3">
                  <div className="card h-100 border-0 shadow-lg position-relative overflow-hidden">
                    <div className="position-absolute top-0 start-0 m-2">
                      <span className="badge bg-danger fs-6 px-3 py-2">
                        🔥 HOT DEAL
                      </span>
                    </div>
                    {product.images && product.images[0] && (
                      <img
                        src={`http://localhost/React/naznin/my-app/API/uploads/products/${product.images[0]}`}
                        alt={product.name}
                        className="card-img-top"
                        style={{ height: '250px', objectFit: 'cover' }}
                      />
                    )}
                    <div className="card-body d-flex flex-column">
                      <h5 className="card-title fw-bold text-dark mb-2">{product.name}</h5>
                      <p className="card-text text-muted small flex-grow-1">{product.short_description}</p>
                      
                      <div className="mb-3">
                        <div className="d-flex align-items-center justify-content-between mb-2">
                          <span className="text-decoration-line-through text-muted fs-6">৳{product.price}</span>
                          <span className="fs-4 fw-bold text-danger">৳{product.sale_price}</span>
                        </div>
                        <div className="progress mb-2" style={{height: '8px'}}>
                          <div className="progress-bar bg-danger" style={{width: '75%'}}></div>
                        </div>
                        <small className="text-muted">Only a few left in stock!</small>
                      </div>
                      
                      <div className="d-grid gap-2">
                        <button
                          onClick={() => addToCart(product)}
                          className="btn btn-danger fw-bold"
                        >
                          🛒 Add to Cart
                        </button>
                        <Link
                          to={`/products/${product.id}`}
                          className="btn btn-outline-secondary"
                        >
                          👁️ View Details
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
            
            <div className="text-center mt-5">
              <Link
                to="/products"
                className="btn btn-light btn-lg px-5 py-3 fw-bold"
              >
                View All Products
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* Categories Preview */}
      <section className="py-5 bg-light">
        <div className="container">
          <div className="text-center mb-5">
            <h2 className="display-5 fw-bold text-dark mb-3">Shop by Category</h2>
            <p className="lead text-muted">
              Explore our wide range of beauty categories
            </p>
          </div>
          
          <div className="row g-4">
            {['Skincare', 'Makeup', 'Hair Care', 'Fragrance', 'Body Care'].map((category, index) => (
              <div key={index} className="col-md-6 col-lg-4">
                <Link to={`/products?category=${index + 1}`} className="text-decoration-none">
                  <div className="card border-0 shadow-sm card-hover text-center">
                    <div className="card-body py-5">
                      <div className="bg-gradient-beauty rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style={{width: '80px', height: '80px'}}>
                        <span className="text-white fw-bold fs-4">{category.charAt(0)}</span>
                      </div>
                      <h5 className="card-title fw-bold text-dark">{category}</h5>
                      <p className="card-text text-muted">Discover amazing {category.toLowerCase()} products</p>
                    </div>
                  </div>
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
});

export default Home;
