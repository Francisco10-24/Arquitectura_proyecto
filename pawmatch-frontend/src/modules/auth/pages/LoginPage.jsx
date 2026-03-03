import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../../context/AuthContext';
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
      const user = await login(email, password);
      
      // Redirección inteligente basada en el rol
      if (user.role === 'admin') {
        navigate('/admin/dashboard');
      } else {
        navigate('/pets'); // Al usuario normal lo mandamos a ver mascotas
      }
    } catch (err) {
      setError(err.message || 'Error al iniciar sesión');
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
            <AlertCircle className="h-5 w-5 text-red-500 mr-2" />
            <p className="text-sm text-red-700">{error}</p>
          </div>
        )}

        {/* Formulario */}
        <form className="space-y-6" onSubmit={handleSubmit}>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="admin@pawmatch.com"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="••••••••"
            />
          </div>

          <button
            type="submit"
            disabled={isSubmitting}
            className={`w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors ${isSubmitting ? 'opacity-70 cursor-not-allowed' : ''}`}
          >
            {isSubmitting ? 'Verificando...' : (
              <span className="flex items-center gap-2">
                <LogIn className="w-5 h-5" /> Iniciar Sesión
              </span>
            )}
          </button>
        </form>

        {/* Info del Mock */}
        <div className="mt-6 pt-6 border-t border-gray-100 text-xs text-gray-500 text-center">
          <p>Credenciales de prueba:</p>
          <p>Admin: admin@pawmatch.com / password</p>
          <p>Usuario: user@pawmatch.com / password</p>
        </div>
      </div>
    </div>
  );
};