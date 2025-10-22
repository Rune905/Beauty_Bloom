import { useState, useEffect, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ShoppingCart, Heart, Star, Truck, Shield, RotateCcw, Eye, Share2, MessageCircle, Package } from 'lucide-react';

const ProductDetail = ({ addToCart }) => {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [quantity, setQuantity] = useState(1);
  const [selectedImage, setSelectedImage] = useState(0);
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [activeTab, setActiveTab] = useState('description');

  const fetchProduct = useCallback(async () => {
    try {
      const response = await fetch(`http://localhost/React/naznin/my-app/API/api/products/read_one.php?id=${id}`);
      const data = await response.json();
      setProduct(data);
      
      // Fetch related products
      if (data.category_id) {
        await fetchRelatedProducts(data.category_id, data.id);
      }
    } catch (error) {
      console.error('Error fetching product:', error);
    } finally {
      setLoading(false);
    }
  }, [id]);

  const fetchRelatedProducts = async (categoryId, currentProductId) => {
    try {
      const response = await fetch(`http://localhost/React/naznin/my-app/API/api/products/read.php?category_id=${categoryId}&limit=4`);
      const data = await response.json();
      if (data.records) {
        // Filter out the current product
        const filtered = data.records.filter(p => p.id != currentProductId);
        setRelatedProducts(filtered.slice(0, 3));
      }
    } catch (error) {
      console.error('Error fetching related products:', error);
    }
  };

  const handleAddToCart = () => {
    if (product && quantity > 0) {
      addToCart(product, quantity);
      // Show success message
      alert(`${quantity} ${quantity === 1 ? 'item' : 'items'} added to cart!`);
    }
  };

  const toggleWishlist = () => {
    setIsWishlisted(!isWishlisted);
  };

  const formatPrice = (price) => {
    return new Intl.NumberFormat('en-BD', {
      style: 'currency',
      currency: 'BDT',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(price);
  };

  const calculateDiscount = () => {
    if (product?.sale_price && product?.price > product?.sale_price) {
      return Math.round(((product.price - product.sale_price) / product.price) * 100);
    }
    return 0;
  };

  const getProductImages = () => {
    if (product?.images && product.images.length > 0) {
      return product.images;
    }
    // Return placeholder images
    return [
      `http://localhost/React/naznin/my-app/API/uploads/products/${product?.image || 'placeholder.jpg'}`,
      `http://localhost/React/naznin/my-app/API/uploads/products/${product?.image || 'placeholder.jpg'}`,
      `http://localhost/React/naznin/my-app/API/uploads/products/${product?.image || 'placeholder.jpg'}`,
      `http://localhost/React/naznin/my-app/API/uploads/products/${product?.image || 'placeholder.jpg'}`
    ];
  };

  useEffect(() => {
    fetchProduct();
  }, [fetchProduct]);

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center min-vh-100">
        <div className="spinner-border text-primary" role="status" style={{width: '3rem', height: '3rem'}}>
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  if (!product) {
    return (
      <div className="min-vh-100 bg-light d-flex align-items-center justify-content-center">
        <div className="text-center">
          <Package className="display-1 text-muted mb-4" />
          <h1 className="h2 fw-bold text-dark mb-3">Product not found</h1>
          <p className="text-muted mb-4">The product you're looking for doesn't exist.</p>
          <Link to="/products" className="btn btn-primary">
            Browse All Products
          </Link>
        </div>
      </div>
    );
  }

  const discount = calculateDiscount();
  const images = getProductImages();

  return (
    <div className="min-vh-100 bg-light py-5">
      <div className="container">
        {/* Breadcrumb */}
        <nav aria-label="breadcrumb" className="mb-4">
          <ol className="breadcrumb">
            <li className="breadcrumb-item">
              <Link to="/" className="text-decoration-none">Home</Link>
            </li>
            <li className="breadcrumb-item">
              <Link to="/categories" className="text-decoration-none">Categories</Link>
            </li>
            <li className="breadcrumb-item">
              <Link to={`/products?category=${product.category_id}`} className="text-decoration-none">
                {product.category_name}
              </Link>
            </li>
            <li className="breadcrumb-item active" aria-current="page">
              {product.name}
            </li>
          </ol>
        </nav>

        <div className="row g-5">
          {/* Product Images */}
          <div className="col-lg-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body p-4">
                {/* Main Image */}
                <div className="text-center mb-4">
                  <img
                    src={images[selectedImage]}
                    alt={product.name}
                    className="img-fluid rounded"
                    style={{maxHeight: '400px', objectFit: 'contain'}}
                    onError={(e) => {
                      e.target.src = `https://via.placeholder.com/400x400/667eea/ffffff?text=${product.name.charAt(0)}`;
                    }}
                  />
                </div>
                
                {/* Thumbnail Images */}
                <div className="d-flex gap-2 justify-content-center">
                  {images.slice(0, 4).map((image, index) => (
                    <button
                      key={index}
                      onClick={() => setSelectedImage(index)}
                      className={`btn btn-outline-secondary p-0 ${selectedImage === index ? 'border-primary' : ''}`}
                      style={{width: '80px', height: '80px'}}
                    >
                      <img
                        src={image}
                        alt={`${product.name} ${index + 1}`}
                        className="img-fluid rounded"
                        style={{width: '100%', height: '100%', objectFit: 'cover'}}
                        onError={(e) => {
                          e.target.src = `https://via.placeholder.com/80x80/667eea/ffffff?text=${product.name.charAt(0)}`;
                        }}
                      />
                    </button>
                  ))}
                </div>
              </div>
            </div>
          </div>

          {/* Product Info */}
          <div className="col-lg-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body p-4">
                {/* Product Title */}
                <h1 className="h2 fw-bold text-dark mb-2">{product.name}</h1>
                
                {/* Brand */}
                {product.brand_name && (
                  <p className="text-muted mb-3">
                    by <span className="fw-bold text-primary">{product.brand_name}</span>
                  </p>
                )}

                {/* Rating */}
                <div className="d-flex align-items-center gap-2 mb-3">
                  <div className="text-warning">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} size={16} fill={i < 4 ? "currentColor" : "none"} />
                    ))}
                  </div>
                  <span className="text-muted">(4.5)</span>
                  <span className="text-muted">•</span>
                  <span className="text-muted">12 reviews</span>
                </div>

                {/* Price */}
                <div className="mb-4">
                  {product.sale_price && product.sale_price < product.price ? (
                    <div className="d-flex align-items-center gap-3">
                      <span className="h2 fw-bold text-danger mb-0">
                        ৳{product.sale_price}
                      </span>
                      <span className="h4 text-decoration-line-through text-muted mb-0">
                        ৳{product.price}
                      </span>
                      {discount > 0 && (
                        <span className="badge bg-success fs-6 px-3 py-2">
                          -{discount}% OFF
                        </span>
                      )}
                    </div>
                  ) : (
                    <span className="h2 fw-bold text-primary">
                      ৳{product.price}
                    </span>
                  )}
                </div>

                {/* Stock Status */}
                <div className="mb-4">
                  <div className="d-flex align-items-center gap-2">
                    <div className={`w-3 h-3 rounded-circle ${product.stock_quantity > 0 ? 'bg-success' : 'bg-danger'}`}></div>
                    <span className={product.stock_quantity > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'}>
                      {product.stock_quantity > 0 
                        ? `✅ In Stock (${product.stock_quantity} available)`
                        : '❌ Out of Stock'
                      }
                    </span>
                  </div>
                </div>

                {/* SKU */}
                <p className="text-muted small mb-4">
                  SKU: <span className="fw-bold">{product.sku}</span>
                </p>

                {/* Quantity and Add to Cart */}
                <div className="mb-4">
                  <label className="form-label fw-bold">Quantity:</label>
                  <div className="d-flex align-items-center gap-3">
                    <div className="input-group" style={{width: '150px'}}>
                      <button
                        className="btn btn-outline-secondary"
                        type="button"
                        onClick={() => setQuantity(Math.max(1, quantity - 1))}
                      >
                        -
                      </button>
                      <input
                        type="number"
                        className="form-control text-center"
                        value={quantity}
                        onChange={(e) => setQuantity(Math.max(1, parseInt(e.target.value) || 1))}
                        min="1"
                        max={product.stock_quantity}
                      />
                      <button
                        className="btn btn-outline-secondary"
                        type="button"
                        onClick={() => setQuantity(quantity + 1)}
                      >
                        +
                      </button>
                    </div>
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="d-grid gap-3 mb-4">
                  <button
                    onClick={handleAddToCart}
                    disabled={product.stock_quantity === 0}
                    className={`btn btn-lg fw-bold ${product.stock_quantity > 0 ? 'btn-primary' : 'btn-secondary'}`}
                  >
                    <ShoppingCart className="me-2" size={18} />
                    {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
                  </button>
                  
                  <div className="d-flex gap-2">
                    <button
                      onClick={toggleWishlist}
                      className={`btn btn-outline-${isWishlisted ? 'danger' : 'secondary'} flex-fill`}
                    >
                      <Heart className="me-2" size={16} fill={isWishlisted ? "currentColor" : "none"} />
                      {isWishlisted ? 'Wishlisted' : 'Add to Wishlist'}
                    </button>
                    <button className="btn btn-outline-secondary">
                      <Share2 size={16} />
                    </button>
                  </div>
                </div>

                {/* Features */}
                <div className="row g-3 pt-4 border-top">
                  <div className="col-4 text-center">
                    <Truck className="text-primary mb-2" size={20} />
                    <div className="small text-muted">Free Shipping</div>
                  </div>
                  <div className="col-4 text-center">
                    <Shield className="text-primary mb-2" size={20} />
                    <div className="small text-muted">Secure Payment</div>
                  </div>
                  <div className="col-4 text-center">
                    <RotateCcw className="text-primary mb-2" size={20} />
                    <div className="small text-muted">30 Day Returns</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Product Details Tabs */}
        <div className="row mt-5">
          <div className="col-12">
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-white">
                <ul className="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                  <li className="nav-item" role="presentation">
                    <button
                      className={`nav-link ${activeTab === 'description' ? 'active' : ''}`}
                      onClick={() => setActiveTab('description')}
                    >
                      Description
                    </button>
                  </li>
                  <li className="nav-item" role="presentation">
                    <button
                      className={`nav-link ${activeTab === 'details' ? 'active' : ''}`}
                      onClick={() => setActiveTab('details')}
                    >
                      Product Details
                    </button>
                  </li>
                  <li className="nav-item" role="presentation">
                    <button
                      className={`nav-link ${activeTab === 'reviews' ? 'active' : ''}`}
                      onClick={() => setActiveTab('reviews')}
                    >
                      <MessageCircle className="me-2" size={16} />
                      Reviews
                    </button>
                  </li>
                </ul>
              </div>
              <div className="card-body p-4">
                {activeTab === 'description' && (
                  <div>
                    <h4 className="fw-bold mb-3">Product Description</h4>
                    <p className="text-muted mb-3">{product.short_description}</p>
                    <p className="text-muted">{product.description}</p>
                  </div>
                )}
                
                {activeTab === 'details' && (
                  <div className="row">
                    <div className="col-md-6">
                      <h4 className="fw-bold mb-3">Product Information</h4>
                      <table className="table table-borderless">
                        <tbody>
                          <tr>
                            <td className="fw-bold">Category:</td>
                            <td>{product.category_name}</td>
                          </tr>
                          {product.brand_name && (
                            <tr>
                              <td className="fw-bold">Brand:</td>
                              <td>{product.brand_name}</td>
                            </tr>
                          )}
                          <tr>
                            <td className="fw-bold">SKU:</td>
                            <td>{product.sku}</td>
                          </tr>
                          <tr>
                            <td className="fw-bold">Stock:</td>
                            <td>{product.stock_quantity} units</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div className="col-md-6">
                      <h4 className="fw-bold mb-3">Shipping & Returns</h4>
                      <ul className="list-unstyled">
                        <li className="mb-2">• Free shipping on orders over ৳1000</li>
                        <li className="mb-2">• Standard delivery: 3-5 business days</li>
                        <li className="mb-2">• 30-day return policy</li>
                        <li className="mb-2">• Secure packaging</li>
                      </ul>
                    </div>
                  </div>
                )}
                
                {activeTab === 'reviews' && (
                  <div>
                    <h4 className="fw-bold mb-3">Customer Reviews</h4>
                    <div className="text-center py-5">
                      <MessageCircle className="display-1 text-muted mb-3" />
                      <h5 className="text-muted">No reviews yet</h5>
                      <p className="text-muted">Be the first to review this product!</p>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <div className="row mt-5">
            <div className="col-12">
              <h3 className="h4 fw-bold mb-4">Related Products</h3>
              <div className="row g-4">
                {relatedProducts.map((relatedProduct) => (
                  <div key={relatedProduct.id} className="col-md-4">
                    <div className="card border-0 shadow-sm h-100 hover-lift">
                      <img
                        src={`http://localhost/React/naznin/my-app/API/uploads/products/${relatedProduct.image || 'placeholder.jpg'}`}
                        className="card-img-top"
                        alt={relatedProduct.name}
                        style={{height: '200px', objectFit: 'cover'}}
                        onError={(e) => {
                          e.target.src = `https://via.placeholder.com/300x200/667eea/ffffff?text=${relatedProduct.name.charAt(0)}`;
                        }}
                      />
                      <div className="card-body d-flex flex-column">
                        <h5 className="card-title fw-bold">{relatedProduct.name}</h5>
                        <p className="card-text text-muted flex-grow-1">{relatedProduct.short_description}</p>
                        <div className="d-flex justify-content-between align-items-center mb-3">
                          <span className="fw-bold text-primary">৳{relatedProduct.sale_price || relatedProduct.price}</span>
                          {relatedProduct.sale_price && (
                            <span className="text-decoration-line-through text-muted">৳{relatedProduct.price}</span>
                          )}
                        </div>
                        <div className="d-grid gap-2">
                          <Link
                            to={`/products/${relatedProduct.id}`}
                            className="btn btn-outline-primary btn-sm"
                          >
                            <Eye className="me-1" size={14} />
                            View Details
                          </Link>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ProductDetail;
