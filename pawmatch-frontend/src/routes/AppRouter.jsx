import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext'; // Importamos el contexto
import { MainLayout } from '../shared/layouts/MainLayout'; // Importamos el Layout

// Importaciones de Páginas
import { PetDetailPage } from '../modules/pets/pages/PetDetailPage';
import { LoginPage } from '../modules/auth/pages/LoginPage';
import { CatalogPage } from '../modules/pets/pages/CatalogPage';
import { AdminDashboardPage } from '../modules/admin/pages/AdminDashboardPage';
import { UserDashboardPage } from '../modules/user/pages/UserDashboardPage';


// Guardia de Ruta Protegida
const ProtectedRoute = ({ children }) => {
  const { token } = useAuth();
  if (!token) return <Navigate to="/login" replace />; // Redirige a login si no hay sesión
  return children;
};

// 2. Guardia Basada en Rol (Solo Admin)
const RoleBasedRoute = ({ children, allowedRoles }) => {
  const { role } = useAuth();
  if (!allowedRoles.includes(role)) return <Navigate to="/" replace />; // Redirige a home si no tiene permiso
  return children;
};


export const AppRouter = () => {
  return (
    <BrowserRouter>
      <Routes>
        {/* Usamos el MainLayout para todas estas rutas */}
        <Route element={<MainLayout />}>
          
          {/* RUTAS PÚBLICAS */}
          <Route path="/" element={<CatalogPage />} /> {/* Catálogo como Home para el MVP */}
          <Route path="/pets" element={<CatalogPage />} />
          
          {/* Detalle de mascota */}
          <Route path="/pets/:id" element={<PetDetailPage />} /> 
          
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<div>Registro (implementa después)</div>} />

          {/*ruta de usuario autenticado */}
          <Route path="/user/dashboard" element={
            <ProtectedRoute>
              <UserDashboardPage />
            </ProtectedRoute>
          } />

          {/* rutas de admin */}
          <Route path="/admin/dashboard" element={
            <ProtectedRoute> {/* Primero logueado */}
              <RoleBasedRoute allowedRoles={['admin']}> {/* Luego Admin */}
                <AdminDashboardPage />
              </RoleBasedRoute>
            </ProtectedRoute>
          } />

          {/* Ruta 404 - No encontrada */}
          <Route path="*" element={<div className="p-10 text-center font-bold text-xl text-red-500">404 - Página No Encontrada</div>} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
};