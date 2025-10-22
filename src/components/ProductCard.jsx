import { useState, memo } from 'react';
import { Link } from 'react-router-dom';
import { Star, ShoppingCart, Eye, Heart } from 'lucide-react';

const ProductCard = ({ product, addToCart }) => {
  const [isWishlisted, setIsWishlisted] = useState(false);

  const toggleWishlist = () => {
    setIsWishlisted(!isWishlisted);
  };

  return (
    <div className="card h-100 border-0 shadow-sm card-hover">
      <div className="position-relative">
        <img 
          src={product.image_url || `/src/assets/image/${product.category_name?.toLowerCase() || 'skin'}/product-${product.id || '1'}.jpg`}
          alt={product.name}
          className="card-img-top"
          style={{height: '200px', objectFit: 'cover'}}
        />
        
        {/* Wishlist button */}
        <button
          onClick={toggleWishlist}
          className="btn btn-sm position-absolute top-0 end-0 m-2 rounded-circle"
          style={{
            width: '35px',
            height: '35px',
            backgroundColor: isWishlisted ? '#ec4899' : 'rgba(255,255,255,0.9)',
            color: isWishlisted ? 'white' : '#6c757d',
            border: 'none'
          }}
        >
          <Heart size={16} fill={isWishlisted ? "currentColor" : "none"} />
        </button>

        {/* Stock status */}
        {product.stock_quantity > 0 ? (
          <span className="position-absolute top-0 start-0 badge bg-success m-2">In Stock</span>
        ) : (
          <span className="position-absolute top-0 start-0 badge bg-danger m-2">Out of Stock</span>
        )}

        {/* Sale badge */}
        {product.sale_price && product.sale_price < product.price && (
          <span className="position-absolute top-0 start-0 badge bg-danger m-2" style={{top: '40px'}}>
            SALE
          </span>
        )}
      </div>

      <div className="card-body d-flex flex-column">
        <div className="mb-2">
          <small className="text-muted text-uppercase">{product.brand_name || 'Brand'}</small>
        </div>
        
        <h5 className="card-title fw-bold text-dark mb-2 line-clamp-2">
          {product.name}
        </h5>
        
        <p className="card-text text-muted small mb-3 line-clamp-2">
          {product.short_description || product.description}
        </p>

        <div className="mt-auto">
          {/* Rating */}
          <div className="d-flex align-items-center mb-2">
            <div className="text-warning me-1">
              {[...Array(5)].map((_, i) => (
                <Star key={i} size={16} fill={i < 4 ? "currentColor" : "none"} />
              ))}
            </div>
            <small className="text-muted">(4.5)</small>
          </div>

          {/* Price */}
          <div className="d-flex align-items-center justify-content-between mb-3">
            {product.sale_price && product.sale_price < product.price ? (
              <>
                <span className="text-decoration-line-through text-muted">৳{product.price}</span>
                <span className="fs-5 fw-bold text-danger">৳{product.sale_price}</span>
              </>
            ) : (
              <span className="fs-5 fw-bold text-dark">৳{product.price}</span>
            )}
          </div>

          {/* Stock info */}
          <div className="mb-3">
            <small className="text-muted">
              {product.stock_quantity > 0 
                ? `${product.stock_quantity} items available`
                : 'Currently out of stock'
              }
            </small>
          </div>

          {/* Action buttons */}
          <div className="d-grid gap-2">
            <button
              onClick={() => addToCart(product)}
              className="btn btn-beauty-primary"
              disabled={product.stock_quantity === 0}
            >
              <ShoppingCart size={18} className="me-2" />
              {product.stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock'}
            </button>
            
            <Link
              to={`/product/${product.id}`}
              className="btn btn-outline-secondary"
            >
              <Eye size={18} className="me-2" />
              View Details
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default memo(ProductCard);
