
import { createContext, useState, useContext, useEffect } from 'react';
import { mockApi } from '../shared/services/api'; // Usaremos el Mock API que ya tienes


const AuthContext = createContext();

// Definimos el proveedor (Provider) del contexto
export const AuthProvider = ({ children }) => {
  // Estado para el usuario actual y el token
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('token') || null);
  const [loading, setLoading] = useState(true); // Para no mostrar nada mientras validamos sesión

  // Efecto para intentar recuperar la sesión al cargar la app
  useEffect(() => {
    const storedUser = localStorage.getItem('user');
    if (storedUser && token) {
      setUser(JSON.parse(storedUser));
    }
    setLoading(false);
  }, [token]);

  // Función de Login (llamo a Mock API)
  const login = async (email, password) => {
    try {
      const response = await mockApi.login(email, password); // Simulación de API
      
      // Guardamos en estado y en LocalStorage
      setUser(response.user);
      setToken(response.token);
      localStorage.setItem('token', response.token);
      localStorage.setItem('user', JSON.stringify(response.user));
      
      return response.user; // Retornamos para redireccionar
    } catch (error) {
      throw error; // Re-lanzamos error para manejarlo en el formulario
    }
  };

  // Función de Logout
  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  };

  // Valor que expondremos a toda la app
  const contextValue = {
    user,
    token,
    role: user?.role, // Exponemos directamente el rol
    login,
    logout,
    loading
  };

  return (
    <AuthContext.Provider value={contextValue}>
      {!loading && children} {/* Mostramos hijos solo cuando la carga inicial termina */}
    </AuthContext.Provider>
  );
};

//Hook personalizado para usar el contexto fácilmente
export const useAuth = () => useContext(AuthContext);