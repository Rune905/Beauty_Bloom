import { Link } from 'react-router-dom';
import { Mail, Phone, MapPin, Facebook, Twitter, Instagram, Youtube } from 'lucide-react';

const Footer = () => {
  return (
    <footer className="bg-gradient-beauty text-white py-5">
      <div className="container">
        <div className="row g-4">
          {/* Company Info */}
          <div className="col-lg-4 col-md-6">
            <div className="mb-4">
              <h5 className="fw-bold mb-3">
                <span className="text-white">Beauty</span>
                <span className="text-warning">Bloom</span>
              </h5>
              <p className="text-light">
                Your one-stop destination for premium beauty products. We curate the finest 
                skincare, makeup, and beauty essentials to enhance your natural radiance.
              </p>
            </div>
            
            {/* Social Media */}
            <div className="d-flex gap-3">
              <a href="#" className="text-light hover-scale" style={{fontSize: '1.5rem'}}>
                <Facebook />
              </a>
              <a href="#" className="text-light hover-scale" style={{fontSize: '1.5rem'}}>
                <Twitter />
              </a>
              <a href="#" className="text-light hover-scale" style={{fontSize: '1.5rem'}}>
                <Instagram />
              </a>
              <a href="#" className="text-light hover-scale" style={{fontSize: '1.5rem'}}>
                <Youtube />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-3">Quick Links</h6>
            <ul className="list-unstyled">
              <li className="mb-2">
                <Link to="/" className="text-light text-decoration-none hover-scale">
                  Home
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/products" className="text-light text-decoration-none hover-scale">
                  Products
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/categories" className="text-light text-decoration-none hover-scale">
                  Categories
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/about" className="text-light text-decoration-none hover-scale">
                  About Us
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/contact" className="text-light text-decoration-none hover-scale">
                  Contact
                </Link>
              </li>
            </ul>
          </div>

          {/* Categories */}
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-3">Categories</h6>
            <ul className="list-unstyled">
              <li className="mb-2">
                <Link to="/products?category=1" className="text-light text-decoration-none hover-scale">
                  Skincare
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/products?category=2" className="text-light text-decoration-none hover-scale">
                  Makeup
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/products?category=3" className="text-light text-decoration-none hover-scale">
                  Hair Care
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/products?category=4" className="text-light text-decoration-none hover-scale">
                  Fragrance
                </Link>
              </li>
              <li className="mb-2">
                <Link to="/products?category=5" className="text-light text-decoration-none hover-scale">
                  Body Care
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact Info */}
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-3">Contact Info</h6>
            <ul className="list-unstyled">
              <li className="mb-2 d-flex align-items-center">
                <Mail size={16} className="me-2 text-light" />
                <span className="text-light">info@beautybloom.com</span>
              </li>
              <li className="mb-2 d-flex align-items-center">
                <Phone size={16} className="me-2 text-light" />
                <span className="text-light">+880 1234 567890</span>
              </li>
              <li className="mb-2 d-flex align-items-center">
                <MapPin size={16} className="me-2 text-light" />
                <span className="text-light">Dhaka, Bangladesh</span>
              </li>
            </ul>
          </div>

          {/* Newsletter */}
          <div className="col-lg-2 col-md-6">
            <h6 className="fw-bold mb-3">Newsletter</h6>
            <p className="text-light small mb-3">
              Subscribe for beauty tips and exclusive offers
            </p>
            <form>
              <div className="input-group mb-3">
                <input
                  type="email"
                  className="form-control form-control-sm"
                  placeholder="Your email"
                  required
                />
                <button className="btn btn-sm btn-warning" type="submit">
                  Subscribe
                </button>
              </div>
            </form>
          </div>
        </div>

        {/* Bottom Bar */}
        <hr className="my-4" />
        <div className="row align-items-center">
          <div className="col-md-6">
            <p className="text-light small mb-0">
              © 2024 Beauty Bloom. All rights reserved.
            </p>
          </div>
          <div className="col-md-6 text-md-end">
            <div className="d-flex gap-3 justify-content-md-end">
              <Link to="/privacy" className="text-light text-decoration-none small">
                Privacy Policy
              </Link>
              <Link to="/terms" className="text-light text-decoration-none small">
                Terms of Service
              </Link>
              <Link to="/shipping" className="text-light text-decoration-none small">
                Shipping Info
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
