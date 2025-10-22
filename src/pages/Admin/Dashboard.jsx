import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Users, 
  Package, 
  ShoppingCart, 
  DollarSign, 
  TrendingUp, 
  Eye, 
  Plus,
  Edit,
  Trash2,
  BarChart3,
  PieChart,
  Activity
} from 'lucide-react';

const Dashboard = () => {
  const [stats, setStats] = useState({
    totalUsers: 0,
    totalProducts: 0,
    totalOrders: 0,
    totalRevenue: 0
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [recentProducts, setRecentProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      // Fetch dashboard statistics
      // const statsResponse = await fetch('http://localhost/React/naznin/my-app/API/api/admin/dashboard_stats.php');
      // const statsData = await statsResponse.json();
      const statsResponse = await fetch('API_URL');
      const statsData = await statsResponse.json();
      
      if (statsData.success) {
        setStats(statsData.data);
      }

      // Fetch recent orders
      const ordersResponse = await fetch('API_URL');
      const ordersData = await ordersResponse.json();
      
      if (ordersData.success) {
        setRecentOrders(ordersData.data);
      }

      // Fetch recent products
      const productsResponse = await fetch('API_URL');
      const productsData = await productsResponse.json();
      
      if (productsData.success) {
        setRecentProducts(productsData.data);
      }
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
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

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
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
    <div className="min-vh-100 bg-light">
      <div className="container-fluid">
        {/* Header */}
        <div className="d-flex justify-content-between align-items-center py-4">
          <div>
            <h1 className="h2 fw-bold text-dark mb-1">Admin Dashboard</h1>
            <p className="text-muted mb-0">Welcome back! Here's what's happening with your store.</p>
          </div>
          <div className="d-flex gap-2">
            <Link to="/admin/products/new" className="btn btn-primary">
              <Plus size={16} className="me-2" />
              Add Product
            </Link>
            <Link to="/admin/orders" className="btn btn-outline-primary">
              <Eye size={16} className="me-2" />
              View Orders
            </Link>
          </div>
        </div>

        {/* Statistics Cards */}
        <div className="row g-4 mb-4">
          <div className="col-xl-3 col-md-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body">
                <div className="d-flex align-items-center">
                  <div className="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <Users className="text-primary" size={24} />
                  </div>
                  <div>
                    <h6 className="text-muted mb-1">Total Users</h6>
                    <h3 className="fw-bold mb-0">{stats.totalUsers}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-xl-3 col-md-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body">
                <div className="d-flex align-items-center">
                  <div className="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                    <Package className="text-success" size={24} />
                  </div>
                  <div>
                    <h6 className="text-muted mb-1">Total Products</h6>
                    <h3 className="fw-bold mb-0">{stats.totalProducts}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-xl-3 col-md-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body">
                <div className="d-flex align-items-center">
                  <div className="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                    <ShoppingCart className="text-warning" size={24} />
                  </div>
                  <div>
                    <h6 className="text-muted mb-1">Total Orders</h6>
                    <h3 className="fw-bold mb-0">{stats.totalOrders}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div className="col-xl-3 col-md-6">
            <div className="card border-0 shadow-sm">
              <div className="card-body">
                <div className="d-flex align-items-center">
                  <div className="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                    <DollarSign className="text-danger" size={24} />
                  </div>
                  <div>
                    <h6 className="text-muted mb-1">Total Revenue</h6>
                    <h3 className="fw-bold mb-0">{formatPrice(stats.totalRevenue)}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="row g-4">
          {/* Recent Orders */}
          <div className="col-lg-8">
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-white">
                <div className="d-flex justify-content-between align-items-center">
                  <h5 className="mb-0 fw-bold">Recent Orders</h5>
                  <Link to="/admin/orders" className="btn btn-sm btn-outline-primary">
                    View All
                  </Link>
                </div>
              </div>
              <div className="card-body">
                {recentOrders.length > 0 ? (
                  <div className="table-responsive">
                    <table className="table table-hover">
                      <thead>
                        <tr>
                          <th>Order ID</th>
                          <th>Customer</th>
                          <th>Amount</th>
                          <th>Status</th>
                          <th>Date</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        {recentOrders.map((order) => (
                          <tr key={order.id}>
                            <td>#{order.id}</td>
                            <td>{order.customer_name}</td>
                            <td>{formatPrice(order.total_amount)}</td>
                            <td>
                              <span className={`badge bg-${order.status === 'completed' ? 'success' : order.status === 'pending' ? 'warning' : 'secondary'}`}>
                                {order.status}
                              </span>
                            </td>
                            <td>{formatDate(order.created_at)}</td>
                            <td>
                              <Link to={`/admin/orders/${order.id}`} className="btn btn-sm btn-outline-primary">
                                <Eye size={14} />
                              </Link>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                ) : (
                  <div className="text-center py-4">
                    <ShoppingCart className="text-muted mb-3" size={48} />
                    <h6 className="text-muted">No orders yet</h6>
                    <p className="text-muted small">Orders will appear here once customers start shopping.</p>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Recent Products */}
          <div className="col-lg-4">
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-white">
                <div className="d-flex justify-content-between align-items-center">
                  <h5 className="mb-0 fw-bold">Recent Products</h5>
                  <Link to="/admin/products" className="btn btn-sm btn-outline-primary">
                    View All
                  </Link>
                </div>
              </div>
              <div className="card-body">
                {recentProducts.length > 0 ? (
                  <div className="d-flex flex-column gap-3">
                    {recentProducts.map((product) => (
                      <div key={product.id} className="d-flex align-items-center">
                        <img
                          src={`API_URL/${product.image || 'placeholder.jpg'}`}
                          alt={product.name}
                          className="rounded me-3"
                          style={{width: '50px', height: '50px', objectFit: 'cover'}}
                          onError={(e) => {
                            e.target.src = `https://via.placeholder.com/50x50/667eea/ffffff?text=${product.name.charAt(0)}`;
                          }}
                        />
                        <div className="flex-grow-1">
                          <h6 className="mb-1 fw-bold">{product.name}</h6>
                          <p className="text-muted small mb-0">{formatPrice(product.price)}</p>
                        </div>
                        <div className="dropdown">
                          <button className="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            ⋮
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
                            <li><button className="dropdown-item text-danger">
                              <Trash2 size={14} className="me-2" />
                              Delete
                            </button></li>
                          </ul>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-4">
                    <Package className="text-muted mb-3" size={48} />
                    <h6 className="text-muted">No products yet</h6>
                    <p className="text-muted small">Add your first product to get started.</p>
                    <Link to="/admin/products/new" className="btn btn-sm btn-primary">
                      <Plus size={14} className="me-2" />
                      Add Product
                    </Link>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="row g-4 mt-4">
          <div className="col-12">
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-white">
                <h5 className="mb-0 fw-bold">Quick Actions</h5>
              </div>
              <div className="card-body">
                <div className="row g-3">
                  <div className="col-md-3">
                    <Link to="/admin/products/new" className="btn btn-outline-primary w-100">
                      <Plus size={16} className="me-2" />
                      Add Product
                    </Link>
                  </div>
                  <div className="col-md-3">
                    <Link to="/admin/categories" className="btn btn-outline-success w-100">
                      <Package size={16} className="me-2" />
                      Manage Categories
                    </Link>
                  </div>
                  <div className="col-md-3">
                    <Link to="/admin/users" className="btn btn-outline-info w-100">
                      <Users size={16} className="me-2" />
                      Manage Users
                    </Link>
                  </div>
                  <div className="col-md-3">
                    <Link to="/admin/orders" className="btn btn-outline-warning w-100">
                      <ShoppingCart size={16} className="me-2" />
                      View Orders
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 