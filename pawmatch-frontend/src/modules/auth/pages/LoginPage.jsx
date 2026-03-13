import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../../hooks/useAuth';
import { PawPrint, LogIn, AlertCircle } from 'lucide-react';

export const LoginPage = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      const response = await login({ email, password });
      const user = response.data.user;
      
      // Redirección basada en rol
      if (user.rol === 'ADMINISTRADOR') {
        navigate('/admin/dashboard');
      } else {
        navigate('/pets');
      }
    } catch (err) {
      // Manejar errores de la API
      if (err.response?.status === 401) {
        setError('Correo o contraseña incorrectos');
      } else if (err.response?.status === 429) {
        setError('Demasiados intentos. Espera un momento.');
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Error al iniciar sesión. Intenta de nuevo.');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
        
        {/* Encabezado */}
        <div className="text-center mb-8">
          <PawPrint className="mx-auto h-12 w-12 text-primary-600" />
          <h2 className="mt-4 text-3xl font-extrabold text-gray-900">Bienvenido de nuevo</h2>
          <p className="mt-2 text-sm text-gray-600">
            Inicia sesión para adoptar o gestionar mascotas
          </p>
        </div>

        {/* Mensaje de Error */}
        {error && (
          <div className="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md flex items-center">
            <AlertCircle className="h-5 w-5 text-red-500 mr-2 flex-shrink-0" />
            <p className="text-sm text-red-700">{error}</p>
          </div>
        )}

        {/* Formulario */}
        <form className="space-y-6" onSubmit={handleSubmit}>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Correo Electrónico
            </label>
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="tu@email.com"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Contraseña
            </label>
            <input
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="••••••••"
            />
          </div>

          <div className="flex items-center justify-between">
            <Link 
              to="/forgot-password" 
              className="text-sm font-medium text-primary-600 hover:text-primary-500"
            >
              ¿Olvidaste tu contraseña?
            </Link>
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ${
              isSubmitting ? 'opacity-70 cursor-not-allowed' : ''
            }`}
          >
            {isSubmitting ? (
              'Verificando...'
            ) : (
              <span className="flex items-center gap-2">
                <LogIn className="w-5 h-5" /> Iniciar Sesión
              </span>
            )}
          </button>
        </form>

        {/* Link a registro */}
        <div className="mt-6 text-center">
          <p className="text-sm text-gray-600">
            ¿No tienes cuenta?{' '}
            <Link 
              to="/register" 
              className="font-medium text-primary-600 hover:text-primary-500"
            >
              Regístrate aquí
            </Link>
          </p>
        </div>

        <div className="mt-6 pt-6 border-t border-gray-100 text-xs text-gray-500 text-center">
        </div>
      </div>
    </div>
  );
};