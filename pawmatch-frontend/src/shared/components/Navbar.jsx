import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { LogOut, PawPrint, User, LayoutDashboard, Menu, X } from 'lucide-react'; // Añadimos Menu y X

export const Navbar = () => {
  const { user, role, logout } = useAuth();
  const navigate = useNavigate();
  
 
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/login');
    setIsMenuOpen(false); 
  };

  return (
    <nav className="bg-white shadow-md border-b border-gray-100 relative z-50">
      <div className="container mx-auto px-6 py-4 flex items-center justify-between">
        
        {/* LOGO  */}
        <Link to="/" className="flex items-center gap-2">
          <PawPrint className="text-primary-600 w-8 h-8" />
          <span className="text-2xl font-bold text-gray-900">
            Paw<span className="text-secondary-600">Match</span>
          </span>
        </Link>

        {/* BOTÓN DE HAMBURGUESA SOLO PARA CELU */}
        <button 
          onClick={() => setIsMenuOpen(!isMenuOpen)}
          className="md:hidden p-2 text-gray-600 hover:text-primary-600 transition"
        >
          {isMenuOpen ? <X size={28} /> : <Menu size={28} />}
        </button>

        {/* MENÚ DE COMPUTADORA */}
        <div className="hidden md:flex items-center gap-6 text-gray-700 font-medium">
          <Link to="/pets" className="hover:text-primary-600 transition">Mascotas</Link>

          {user ? (
            <div className="flex items-center gap-4">
              {role === 'admin' ? (
                <Link to="/admin/dashboard" className="flex items-center gap-2 text-sm bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200">
                  <LayoutDashboard size={18} /> Panel Admin
                </Link>
              ) : (
                <Link to="/user/dashboard" className="flex items-center gap-2 text-sm bg-gray-100 px-3 py-1.5 rounded-lg hover:bg-gray-200">
                  <User size={18} /> Mi Perfil
                </Link>
              )}
              <button onClick={handleLogout} className="flex items-center gap-2 text-red-600 hover:text-red-700 font-semibold transition">
                <LogOut size={20} /> Salir
              </button>
            </div>
          ) : (
            <div className="flex items-center gap-3">
              <Link to="/login" className="px-4 py-2 border border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition">
                Iniciar Sesión
              </Link>
              <Link to="/register" className="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition shadow-sm">
                Registrarse
              </Link>
            </div>
          )}
        </div>
      </div>

      {/* MENÚ DESPLEGABLE DE CELULAR Solo aparece si isMenuOpen es true */}
      {isMenuOpen && (
        <div className="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-100 shadow-lg py-4 px-6 flex flex-col gap-4 text-center">
          <Link to="/pets" onClick={() => setIsMenuOpen(false)} className="text-gray-700 font-medium py-2 hover:bg-gray-50 rounded-lg">Mascotas</Link>
          
          <hr className="border-gray-100" />

          {user ? (
            <div className="flex flex-col gap-3">
              {role === 'admin' ? (
                <Link to="/admin/dashboard" onClick={() => setIsMenuOpen(false)} className="flex items-center justify-center gap-2 bg-gray-100 py-3 rounded-lg text-gray-700 font-medium">
                  <LayoutDashboard size={18} /> Panel Admin
                </Link>
              ) : (
                <Link to="/user/dashboard" onClick={() => setIsMenuOpen(false)} className="flex items-center justify-center gap-2 bg-gray-100 py-3 rounded-lg text-gray-700 font-medium">
                  <User size={18} /> Mi Perfil
                </Link>
              )}
              <button onClick={handleLogout} className="flex items-center justify-center gap-2 text-red-600 bg-red-50 py-3 rounded-lg font-bold">
                <LogOut size={20} /> Cerrar Sesión
              </button>
            </div>
          ) : (
            <div className="flex flex-col gap-3">
              <Link to="/login" onClick={() => setIsMenuOpen(false)} className="border border-primary-600 text-primary-600 py-3 rounded-lg font-medium">
                Iniciar Sesión
              </Link>
              <Link to="/register" onClick={() => setIsMenuOpen(false)} className="bg-primary-600 text-white py-3 rounded-lg font-medium">
                Registrarse
              </Link>
            </div>
          )}
        </div>
      )}
    </nav>
  );
};