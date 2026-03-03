import { useEffect, useState } from 'react';
import { mockApi } from '../../../shared/services/api';
import { useAuth } from '../../../context/AuthContext';
import { Link } from 'react-router-dom';
import { User, FileText } from 'lucide-react';

export const UserDashboardPage = () => {
  const { user } = useAuth(); // Necesitamos saber quién es el usuario
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchMyRequests = async () => {
      try {
        const data = await mockApi.getUserRequests(user.id);
        setRequests(data);
      } catch (error) {
        console.error("Error", error);
      } finally {
        setLoading(false);
      }
    };
    fetchMyRequests();
  }, [user.id]);

  if (loading) return <div className="text-center p-20 font-bold text-gray-500">Cargando tu historial...</div>;

  return (
    <div className="container mx-auto px-6 py-10 max-w-4xl">
      
      {/* Saludo personalizado */}
      <div className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8 flex items-center gap-4">
        <div className="bg-primary-100 p-4 rounded-full text-primary-600">
          <User className="w-8 h-8" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Hola, {user.name}</h1>
          <p className="text-gray-500">Bienvenido a tu panel de adoptante</p>
        </div>
      </div>

      <h2 className="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
        <FileText className="w-5 h-5 text-secondary-500" /> Mis Solicitudes de Adopción
      </h2>

      {/* Lista de solicitudes del usuario */}
      <div className="space-y-4">
        {requests.length === 0 ? (
          <div className="text-center bg-white p-10 rounded-2xl border border-dashed border-gray-300">
            <p className="text-gray-500 mb-4">Aún no has solicitado adoptar a ningún peludito.</p>
            <Link to="/pets" className="text-primary-600 font-bold hover:underline">Ir al catálogo de mascotas</Link>
          </div>
        ) : (
          requests.map(req => (
            <div key={req.id} className="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center justify-between">
              
              <div className="flex items-center gap-4">
                {/* Mini foto de la mascota */}
                <div className="w-16 h-16 bg-gray-200 rounded-xl overflow-hidden">
                  <img src={`/images/pets/${req.petImage}`} alt={req.petName} className="w-full h-full object-cover" />
                </div>
                <div>
                  <h3 className="font-bold text-lg text-gray-900">{req.petName}</h3>
                  <p className="text-sm text-gray-500">Solicitado el: {new Date(req.date).toLocaleDateString()}</p>
                </div>
              </div>

              {/* Estado de la solicitud */}
              <div className="text-right">
                <span className={`inline-block px-4 py-1.5 rounded-full text-sm font-bold shadow-sm ${
                  req.status === 'Pendiente' ? 'bg-secondary-100 text-secondary-700' :
                  req.status === 'Aprobada' ? 'bg-primary-100 text-primary-700' :
                  'bg-red-100 text-red-700'
                }`}>
                  {req.status}
                </span>
              </div>

            </div>
          ))
        )}
      </div>

    </div>
  );
};