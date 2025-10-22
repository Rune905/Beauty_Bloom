import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  ArrowLeft, 
  Upload, 
  X, 
  Save, 
  Package,
  DollarSign,
  Tag,
  Hash,
  FileText,
  Star,
  TrendingUp,
  Eye,
  EyeOff
} from 'lucide-react';

const AddProduct = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [brands, setBrands] = useState([]);
  const [formData, setFormData] = useState({
    name: '',
    short_description: '',
    description: '',
    sku: '',
    category_id: '',
    brand_id: '',
    price: '',
    sale_price: '',
    stock_quantity: '',
    is_featured: false,
    is_hot_deal: false,
    is_active: true
  });
  const [images, setImages] = useState([]);
  const [errors, setErrors] = useState({});
  const [success, setSuccess] = useState('');

  useEffect(() => {
    fetchCategories();
    fetchBrands();
  }, []);

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

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    const validFiles = files.filter(file => {
      const isValidType = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
      const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB limit
      
      if (!isValidType) {
        alert(`${file.name} is not a valid image type. Please use JPEG, PNG, or WebP.`);
        return false;
      }
      
      if (!isValidSize) {
        alert(`${file.name} is too large. Please use images under 5MB.`);
        return false;
      }
      
      return true;
    });

    const newImages = validFiles.map(file => ({
      file,
      preview: URL.createObjectURL(file),
      name: file.name
    }));

    setImages(prev => [...prev, ...newImages]);
  };

  const removeImage = (index) => {
    setImages(prev => {
      const newImages = prev.filter((_, i) => i !== index);
      return newImages;
    });
  };

  const validateForm = () => {
    const newErrors = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required';
    }

    if (!formData.price || parseFloat(formData.price) <= 0) {
      newErrors.price = 'Valid price is required';
    }

    if (formData.sale_price && parseFloat(formData.sale_price) >= parseFloat(formData.price)) {
      newErrors.sale_price = 'Sale price must be less than regular price';
    }

    if (!formData.category_id) {
      newErrors.category_id = 'Category is required';
    }

    if (parseInt(formData.stock_quantity) < 0) {
      newErrors.stock_quantity = 'Stock quantity cannot be negative';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    setSuccess('');

    try {
      // Upload images first
      const uploadedImages = [];
      
      for (const imageData of images) {
        const formDataImage = new FormData();
        formDataImage.append('image', imageData.file);
        
        const uploadResponse = await fetch('http://localhost/React/naznin/my-app/API/api/admin/upload_image.php', {
          method: 'POST',
          body: formDataImage
        });
        
        const uploadResult = await uploadResponse.json();
        
        if (uploadResult.success) {
          uploadedImages.push(uploadResult.filename);
        }
      }

      // Create product with uploaded images
      const productData = {
        ...formData,
        price: parseFloat(formData.price),
        sale_price: formData.sale_price ? parseFloat(formData.sale_price) : null,
        stock_quantity: parseInt(formData.stock_quantity) || 0,
        image: uploadedImages.length > 0 ? uploadedImages[0] : '', // Main image
        images: uploadedImages // All images
      };

      const response = await fetch('http://localhost/React/naznin/my-app/API/api/admin/products.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(productData)
      });

      const data = await response.json();

      if (data.success) {
        setSuccess('Product created successfully! Redirecting...');
        setTimeout(() => {
          navigate('/admin/products');
        }, 2000);
      } else {
        setErrors({ submit: data.message || 'Failed to create product' });
      }
    } catch (error) {
      console.error('Error creating product:', error);
      setErrors({ submit: 'Network error. Please try again.' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-vh-100 bg-light">
      <div className="container-fluid">
        {/* Header */}
        <div className="d-flex justify-content-between align-items-center py-4">
          <div className="d-flex align-items-center">
            <button
              onClick={() => navigate('/admin/products')}
              className="btn btn-outline-secondary me-3"
            >
              <ArrowLeft size={20} />
            </button>
            <div>
              <h1 className="h2 fw-bold text-dark mb-1">Add New Product</h1>
              <p className="text-muted mb-0">Create a new product for your store</p>
            </div>
          </div>
          <button
            type="submit"
            form="productForm"
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? (
              <>
                <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                Creating...
              </>
            ) : (
              <>
                <Save size={16} className="me-2" />
                Create Product
              </>
            )}
          </button>
        </div>

        {/* Success Message */}
        {success && (
          <div className="alert alert-success alert-dismissible fade show" role="alert">
            <i className="bi bi-check-circle me-2"></i>
            {success}
            <button type="button" className="btn-close" onClick={() => setSuccess('')}></button>
          </div>
        )}

        {/* Error Message */}
        {errors.submit && (
          <div className="alert alert-danger alert-dismissible fade show" role="alert">
            <i className="bi bi-exclamation-triangle me-2"></i>
            {errors.submit}
            <button type="button" className="btn-close" onClick={() => setErrors(prev => ({ ...prev, submit: '' }))}></button>
          </div>
        )}

        <form id="productForm" onSubmit={handleSubmit}>
          <div className="row g-4">
            {/* Basic Information */}
            <div className="col-lg-8">
              <div className="card border-0 shadow-sm">
                <div className="card-header bg-white">
                  <h5 className="mb-0 fw-bold">
                    <Package size={20} className="me-2" />
                    Basic Information
                  </h5>
                </div>
                <div className="card-body">
                  <div className="row g-3">
                    <div className="col-12">
                      <label htmlFor="name" className="form-label fw-bold">
                        Product Name *
                      </label>
                      <input
                        type="text"
                        className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                        id="name"
                        name="name"
                        value={formData.name}
                        onChange={handleInputChange}
                        placeholder="Enter product name"
                        required
                      />
                      {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                    </div>

                    <div className="col-12">
                      <label htmlFor="short_description" className="form-label fw-bold">
                        Short Description
                      </label>
                      <textarea
                        className="form-control"
                        id="short_description"
                        name="short_description"
                        value={formData.short_description}
                        onChange={handleInputChange}
                        placeholder="Brief description for product cards"
                        rows="2"
                      ></textarea>
                    </div>

                    <div className="col-12">
                      <label htmlFor="description" className="form-label fw-bold">
                        Full Description
                      </label>
                      <textarea
                        className="form-control"
                        id="description"
                        name="description"
                        value={formData.description}
                        onChange={handleInputChange}
                        placeholder="Detailed product description"
                        rows="4"
                      ></textarea>
                    </div>

                    <div className="col-md-6">
                      <label htmlFor="sku" className="form-label fw-bold">
                        SKU
                      </label>
                      <div className="input-group">
                        <span className="input-group-text">
                          <Hash size={16} />
                        </span>
                        <input
                          type="text"
                          className="form-control"
                          id="sku"
                          name="sku"
                          value={formData.sku}
                          onChange={handleInputChange}
                          placeholder="Stock Keeping Unit"
                        />
                      </div>
                    </div>

                    <div className="col-md-6">
                      <label htmlFor="stock_quantity" className="form-label fw-bold">
                        Stock Quantity
                      </label>
                      <input
                        type="number"
                        className={`form-control ${errors.stock_quantity ? 'is-invalid' : ''}`}
                        id="stock_quantity"
                        name="stock_quantity"
                        value={formData.stock_quantity}
                        onChange={handleInputChange}
                        placeholder="0"
                        min="0"
                      />
                      {errors.stock_quantity && <div className="invalid-feedback">{errors.stock_quantity}</div>}
                    </div>
                  </div>
                </div>
              </div>

              {/* Pricing */}
              <div className="card border-0 shadow-sm mt-4">
                <div className="card-header bg-white">
                  <h5 className="mb-0 fw-bold">
                    <DollarSign size={20} className="me-2" />
                    Pricing
                  </h5>
                </div>
                <div className="card-body">
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label htmlFor="price" className="form-label fw-bold">
                        Regular Price (৳) *
                      </label>
                      <input
                        type="number"
                        className={`form-control ${errors.price ? 'is-invalid' : ''}`}
                        id="price"
                        name="price"
                        value={formData.price}
                        onChange={handleInputChange}
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        required
                      />
                      {errors.price && <div className="invalid-feedback">{errors.price}</div>}
                    </div>

                    <div className="col-md-6">
                      <label htmlFor="sale_price" className="form-label fw-bold">
                        Sale Price (৳)
                      </label>
                      <input
                        type="number"
                        className={`form-control ${errors.sale_price ? 'is-invalid' : ''}`}
                        id="sale_price"
                        name="sale_price"
                        value={formData.sale_price}
                        onChange={handleInputChange}
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                      />
                      {errors.sale_price && <div className="invalid-feedback">{errors.sale_price}</div>}
                      <div className="form-text">Leave empty if no sale price</div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Categories & Brands */}
              <div className="card border-0 shadow-sm mt-4">
                <div className="card-header bg-white">
                  <h5 className="mb-0 fw-bold">
                    <Tag size={20} className="me-2" />
                    Categories & Brands
                  </h5>
                </div>
                <div className="card-body">
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label htmlFor="category_id" className="form-label fw-bold">
                        Category *
                      </label>
                      <select
                        className={`form-select ${errors.category_id ? 'is-invalid' : ''}`}
                        id="category_id"
                        name="category_id"
                        value={formData.category_id}
                        onChange={handleInputChange}
                        required
                      >
                        <option value="">Select Category</option>
                        {categories.map(category => (
                          <option key={category.id} value={category.id}>
                            {category.name}
                          </option>
                        ))}
                      </select>
                      {errors.category_id && <div className="invalid-feedback">{errors.category_id}</div>}
                    </div>

                    <div className="col-md-6">
                      <label htmlFor="brand_id" className="form-label fw-bold">
                        Brand
                      </label>
                      <select
                        className="form-select"
                        id="brand_id"
                        name="brand_id"
                        value={formData.brand_id}
                        onChange={handleInputChange}
                      >
                        <option value="">Select Brand</option>
                        {brands.map(brand => (
                          <option key={brand.id} value={brand.id}>
                            {brand.name}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Sidebar */}
            <div className="col-lg-4">
              {/* Product Images */}
              <div className="card border-0 shadow-sm">
                <div className="card-header bg-white">
                  <h5 className="mb-0 fw-bold">
                    <Upload size={20} className="me-2" />
                    Product Images
                  </h5>
                </div>
                <div className="card-body">
                  <div className="mb-3">
                    <label htmlFor="imageUpload" className="form-label fw-bold">
                      Upload Images
                    </label>
                    <input
                      type="file"
                      className="form-control"
                      id="imageUpload"
                      multiple
                      accept="image/*"
                      onChange={handleImageUpload}
                    />
                    <div className="form-text">
                      Upload multiple images. First image will be the main product image.
                    </div>
                  </div>

                  {/* Image Preview */}
                  {images.length > 0 && (
                    <div className="row g-2">
                      {images.map((image, index) => (
                        <div key={index} className="col-6">
                          <div className="position-relative">
                            <img
                              src={image.preview}
                              alt={image.name}
                              className="img-fluid rounded"
                              style={{height: '100px', objectFit: 'cover', width: '100%'}}
                            />
                            <button
                              type="button"
                              className="btn btn-danger btn-sm position-absolute top-0 end-0"
                              onClick={() => removeImage(index)}
                              style={{margin: '2px'}}
                            >
                              <X size={12} />
                            </button>
                            {index === 0 && (
                              <span className="badge bg-primary position-absolute bottom-0 start-0 m-1">
                                Main
                              </span>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              {/* Product Settings */}
              <div className="card border-0 shadow-sm mt-4">
                <div className="card-header bg-white">
                  <h5 className="mb-0 fw-bold">
                    <FileText size={20} className="me-2" />
                    Product Settings
                  </h5>
                </div>
                <div className="card-body">
                  <div className="form-check form-switch mb-3">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      id="is_featured"
                      name="is_featured"
                      checked={formData.is_featured}
                      onChange={handleInputChange}
                    />
                    <label className="form-check-label fw-bold" htmlFor="is_featured">
                      <Star size={16} className="me-2" />
                      Featured Product
                    </label>
                    <div className="form-text">Show in featured products section</div>
                  </div>

                  <div className="form-check form-switch mb-3">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      id="is_hot_deal"
                      name="is_hot_deal"
                      checked={formData.is_hot_deal}
                      onChange={handleInputChange}
                    />
                    <label className="form-check-label fw-bold" htmlFor="is_hot_deal">
                      <TrendingUp size={16} className="me-2" />
                      Hot Deal
                    </label>
                    <div className="form-text">Show in hot deals section</div>
                  </div>

                  <div className="form-check form-switch">
                    <input
                      className="form-check-input"
                      type="checkbox"
                      id="is_active"
                      name="is_active"
                      checked={formData.is_active}
                      onChange={handleInputChange}
                    />
                    <label className="form-check-label fw-bold" htmlFor="is_active">
                      <Eye size={16} className="me-2" />
                      Active Product
                    </label>
                    <div className="form-text">Make product visible to customers</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddProduct; 