import { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { 
  LayoutDashboard, 
  Package, 
  ShoppingCart, 
  Users, 
  Settings, 
  LogOut, 
  Menu, 
  X,
  BarChart3,
  Tag,
  FileText
} from 'lucide-react';

const AdminLayout = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [user, setUser] = useState(null);
  const location = useLocation();
  const navigate = useNavigate();

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      const userObj = JSON.parse(userData);
      if (userObj.role !== 'admin') {
        navigate('/login');
        return;
      }
      setUser(userObj);
    } else {
      navigate('/login');
    }
  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    navigate('/login');
  };

  const menuItems = [
    {
      path: '/admin',
      icon: <LayoutDashboard size={20} />,
      label: 'Dashboard',
      exact: true
    },
    {
      path: '/admin/products',
      icon: <Package size={20} />,
      label: 'Products'
    },
    {
      path: '/admin/categories',
      icon: <Tag size={20} />,
      label: 'Categories'
    },
    {
      path: '/admin/orders',
      icon: <ShoppingCart size={20} />,
      label: 'Orders'
    },
    {
      path: '/admin/users',
      icon: <Users size={20} />,
      label: 'Users'
    },
    {
      path: '/admin/reports',
      icon: <BarChart3 size={20} />,
      label: 'Reports'
    },
    {
      path: '/admin/settings',
      icon: <Settings size={20} />,
      label: 'Settings'
    }
  ];

  const isActive = (path, exact = false) => {
    if (exact) {
      return location.pathname === path;
    }
    return location.pathname.startsWith(path);
  };

  if (!user) {
    return null;
  }

  return (
    <div className="min-vh-100 bg-light">
      {/* Mobile Sidebar Overlay */}
      {sidebarOpen && (
        <div 
          className="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-lg-none"
          style={{zIndex: 1040}}
          onClick={() => setSidebarOpen(false)}
        ></div>
      )}

      {/* Sidebar */}
      <div className={`position-fixed top-0 start-0 h-100 bg-white shadow-sm d-flex flex-column ${sidebarOpen ? 'd-block' : 'd-none'} d-lg-block`} 
           style={{width: '280px', zIndex: 1050}}>
        
        {/* Sidebar Header */}
        <div className="p-4 border-bottom">
          <div className="d-flex align-items-center">
            <div className="bg-gradient-beauty rounded-circle d-inline-flex align-items-center justify-content-center me-3" 
                 style={{width: '40px', height: '40px'}}>
              <Package className="text-white" size={20} />
            </div>
            <div>
              <h5 className="mb-0 fw-bold">Beauty Bloom</h5>
              <small className="text-muted">Admin Panel</small>
            </div>
          </div>
        </div>

        {/* User Info */}
        <div className="p-4 border-bottom">
          <div className="d-flex align-items-center">
            <div className="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center me-3" 
                 style={{width: '40px', height: '40px'}}>
              <span className="text-white fw-bold">{user.first_name.charAt(0)}</span>
            </div>
            <div>
              <h6 className="mb-0 fw-bold">{user.first_name} {user.last_name}</h6>
              <small className="text-muted">{user.email}</small>
            </div>
          </div>
        </div>

        {/* Navigation Menu */}
        <nav className="flex-grow-1 p-3">
          <ul className="nav flex-column">
            {menuItems.map((item, index) => (
              <li key={index} className="nav-item mb-2">
                <Link
                  to={item.path}
                  className={`nav-link d-flex align-items-center py-2 px-3 rounded ${
                    isActive(item.path, item.exact) 
                      ? 'bg-primary text-white' 
                      : 'text-dark hover-bg-light'
                  }`}
                  onClick={() => setSidebarOpen(false)}
                >
                  {item.icon}
                  <span className="ms-3">{item.label}</span>
                </Link>
              </li>
            ))}
          </ul>
        </nav>

        {/* Logout */}
        <div className="p-3 border-top">
          <button
            onClick={handleLogout}
            className="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center"
          >
            <LogOut size={20} className="me-2" />
            Logout
          </button>
        </div>
      </div>

      {/* Main Content */}
      <div className="d-lg-block" style={{marginLeft: '280px'}}>
        {/* Top Bar */}
        <div className="bg-white shadow-sm border-bottom">
          <div className="d-flex justify-content-between align-items-center px-4 py-3">
            <button
              className="btn btn-outline-secondary d-lg-none"
              onClick={() => setSidebarOpen(!sidebarOpen)}
            >
              {sidebarOpen ? <X size={20} /> : <Menu size={20} />}
            </button>
            
            <div className="d-flex align-items-center">
              <h4 className="mb-0 fw-bold">Admin Panel</h4>
            </div>

            <div className="d-flex align-items-center gap-3">
              <div className="dropdown">
                <button className="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                  <FileText size={16} className="me-2" />
                  Quick Actions
                </button>
                <ul className="dropdown-menu">
                  <li><Link className="dropdown-item" to="/admin/products/new">
                    <Package size={16} className="me-2" />
                    Add Product
                  </Link></li>
                  <li><Link className="dropdown-item" to="/admin/categories">
                    <Tag size={16} className="me-2" />
                    Manage Categories
                  </Link></li>
                  <li><Link className="dropdown-item" to="/admin/users">
                    <Users size={16} className="me-2" />
                    Manage Users
                  </Link></li>
                  <li><hr className="dropdown-divider" /></li>
                  <li><Link className="dropdown-item" to="/">
                    <LayoutDashboard size={16} className="me-2" />
                    View Store
                  </Link></li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        {/* Page Content */}
        <div className="p-0">
          {children}
        </div>
      </div>
    </div>
  );
};

export default AdminLayout; 