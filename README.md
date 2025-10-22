# Beauty Bloom - Full-Stack E-Commerce Website

A modern, responsive beauty e-commerce website built with **React** (frontend), **PHP** (backend), and **MySQL** (database).

## 🌟 Features

### Frontend (React)
- **Responsive Design**: Mobile-first approach with Tailwind CSS
- **Product Catalog**: Browse products by category, search, and filter
- **Shopping Cart**: Add/remove items, quantity management, persistent storage
- **Product Details**: Comprehensive product information and images
- **User Experience**: Smooth animations, loading states, and intuitive navigation
- **Modern UI**: Beautiful gradient designs and hover effects

### Backend (PHP)
- **RESTful API**: Clean, organized API endpoints
- **Database Models**: Structured data access with PDO
- **Security**: Prepared statements to prevent SQL injection
- **CORS Support**: Cross-origin resource sharing enabled
- **Error Handling**: Comprehensive error management

### Database (MySQL)
- **Complete Schema**: Users, products, categories, orders, cart, reviews
- **Relationships**: Proper foreign key constraints and indexing
- **Sample Data**: Pre-populated with beauty products
- **Scalable Design**: Optimized for performance and growth

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (or similar local server with Apache, MySQL, PHP)
- **Node.js** (version 16 or higher)
- **npm** or **yarn**

### 1. Database Setup

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database called `beauty_bloom`
4. Import the database schema:
   ```sql
   -- Navigate to: API/Database/beauty_bloom_schema.sql
   -- Copy and paste the entire content into phpMyAdmin SQL tab
   -- Click "Go" to execute
   ```

### 2. Backend Setup

1. Navigate to the API directory:
   ```bash
   cd API
   ```

2. Update database configuration in `config/database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "beauty_bloom";
   private $username = "root";  // Your MySQL username
   private $password = "";      // Your MySQL password
   ```

3. (Optional) Insert sample products:
   ```bash
   # In your browser, navigate to:
   http://localhost/React/naznin/my-app/API/sample_data.php
   ```

### 3. Frontend Setup

1. Install dependencies:
   ```bash
   npm install
   ```

2. Start the development server:
   ```bash
   npm run dev
   ```

3. Open your browser and navigate to:
   ```
   http://localhost:5173
   ```

## 📁 Project Structure

```
my-app/
├── API/                          # PHP Backend
│   ├── config/
│   │   └── database.php         # Database connection
│   ├── models/
│   │   ├── Product.php          # Product model
│   │   └── Category.php         # Category model
│   ├── api/
│   │   ├── products/            # Product API endpoints
│   │   └── categories/          # Category API endpoints
│   ├── Database/
│   │   └── beauty_bloom_schema.sql  # Database schema
│   └── sample_data.php          # Sample data insertion
├── src/
│   ├── components/              # Reusable React components
│   │   ├── Header.jsx          # Navigation header
│   │   ├── Footer.jsx          # Site footer
│   │   └── ProductCard.jsx     # Product display card
│   ├── pages/                   # Page components
│   │   ├── Home.jsx            # Homepage
│   │   ├── Products.jsx        # Product listing
│   │   ├── ProductDetail.jsx   # Single product view
│   │   ├── Categories.jsx      # Category listing
│   │   └── Cart.jsx            # Shopping cart
│   ├── App.jsx                  # Main app component
│   ├── main.jsx                 # App entry point
│   └── index.css                # Global styles
├── tailwind.config.js           # Tailwind CSS configuration
├── package.json                 # Node.js dependencies
└── README.md                    # This file
```

## 🔧 API Endpoints

### Products
- `GET /API/api/products/read.php` - Get all products
- `GET /API/api/products/read.php?category_id={id}` - Get products by category
- `GET /API/api/products/read.php?search={term}` - Search products
- `GET /API/api/products/read_one.php?id={id}` - Get single product
- `GET /API/api/products/featured.php` - Get featured products

### Categories
- `GET /API/api/categories/read.php` - Get main categories
- `GET /API/api/categories/read.php?parent_id={id}` - Get subcategories

## 🎨 Customization

### Styling
- **Colors**: Update `tailwind.config.js` for brand colors
- **Fonts**: Modify font families in Tailwind config
- **Components**: Edit component files in `src/components/`

### Database
- **Schema**: Modify `API/Database/beauty_bloom_schema.sql`
- **Models**: Update PHP models in `API/models/`
- **API**: Customize endpoints in `API/api/`

### Features
- **Authentication**: Add user login/registration
- **Payment**: Integrate payment gateways
- **Admin Panel**: Create admin dashboard
- **Reviews**: Implement product review system

## 🚀 Deployment

### Frontend
1. Build the project:
   ```bash
   npm run build
   ```

2. Deploy the `dist` folder to your web server

### Backend
1. Upload the `API` folder to your server
2. Update database configuration for production
3. Ensure PHP and MySQL are configured

### Database
1. Export your local database
2. Import to production MySQL server
3. Update connection settings

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify XAMPP is running
   - Check database credentials in `config/database.php`
   - Ensure database `beauty_bloom` exists

2. **API Endpoints Not Working**
   - Check file permissions
   - Verify PHP is enabled in Apache
   - Check browser console for CORS errors

3. **Frontend Not Loading**
   - Ensure `npm install` completed successfully
   - Check for JavaScript errors in browser console
   - Verify all dependencies are installed

4. **Styling Issues**
   - Ensure Tailwind CSS is properly configured
   - Check if `tailwind.config.js` is in root directory
   - Verify CSS imports in `index.css`

## 📱 Browser Support

- **Chrome** (latest)
- **Firefox** (latest)
- **Safari** (latest)
- **Edge** (latest)
- **Mobile browsers** (iOS Safari, Chrome Mobile)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section above
- Review the code comments for guidance

---

**Happy Coding! 🎉**

Built with ❤️ using React, PHP, and MySQL
