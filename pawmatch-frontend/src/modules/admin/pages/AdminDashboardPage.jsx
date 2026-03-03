import { useEffect, useState } from 'react';
import { mockApi } from '../../../shared/services/api';
import { CheckCircle, XCircle, Clock, ShieldCheck } from 'lucide-react';

export const AdminDashboardPage = () => {
  const [requests, setRequests] = useState([]);
  const [loading, setLoading] = useState(true);
  const [processingId, setProcessingId] = useState(null); 

  // Cargar las solicitudes al entrar al panel
  useEffect(() => {
    fetchRequests();
  }, []);

  const fetchRequests = async () => {
    try {
      const data = await mockApi.getRequests();
      setRequests(data);
    } catch (error) {
      console.error("Error cargando solicitudes", error);
    } finally {
      setLoading(false);
    }
  };

  // Función para manejar el clic en Aprobar o Rechazar
  const handleStatusChange = async (requestId, newStatus) => {
    setProcessingId(requestId); // Mostramos estado de carga en este botón
    try {
      await mockApi.updateRequestStatus(requestId, newStatus);
      await fetchRequests(); // Recargamos la tabla para ver los cambios
    } catch (error) {
      alert("Hubo un error al actualizar la solicitud");
    } finally {
      setProcessingId(null);
    }
  };

  if (loading) return <div className="text-center p-20 text-xl font-bold text-gray-500">Cargando panel de administración...</div>;

  return (
    <div className="container mx-auto px-6 py-10 max-w-6xl">
      
      {/* Cabecera del Dashboard */}
      <div className="flex items-center gap-4 mb-8 border-b border-gray-200 pb-6">
        <ShieldCheck className="w-10 h-10 text-primary-600" />
        <div>
          <h1 className="text-3xl font-extrabold text-gray-900">Panel de Administración</h1>
          <p className="text-gray-500 mt-1">Gestiona las solicitudes de adopción de la plataforma.</p>
        </div>
      </div>

      {/* Tabla de Solicitudes */}
      <div className="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h2 className="text-xl font-bold text-gray-800 flex items-center gap-2">
            <Clock className="w-5 h-5 text-secondary-500" /> Solicitudes Recientes
          </h2>
        </div>
        
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-white border-b border-gray-100 text-sm text-gray-500 uppercase tracking-wider">
                <th className="p-4 font-semibold">ID</th>
                <th className="p-4 font-semibold">Adoptante</th>
                <th className="p-4 font-semibold">Mascota</th>
                <th className="p-4 font-semibold">Fecha</th>
                <th className="p-4 font-semibold">Estado</th>
                <th className="p-4 font-semibold text-right">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              
              {requests.length === 0 ? (
                <tr>
                  <td colSpan="6" className="p-8 text-center text-gray-500">No hay solicitudes pendientes.</td>
                </tr>
              ) : (
                requests.map(req => (
                  <tr key={req.id} className="hover:bg-gray-50 transition-colors">
                    <td className="p-4 text-sm text-gray-600 font-mono">#{req.id}</td>
                    <td className="p-4 font-medium text-gray-900">{req.userName}</td>
                    <td className="p-4 text-primary-600 font-semibold">{req.petName}</td>
                    <td className="p-4 text-sm text-gray-500">{new Date(req.date).toLocaleDateString()}</td>
                    
                    {/* Badge de Estado */}
                    <td className="p-4">
                      <span className={`px-3 py-1 rounded-full text-xs font-bold ${
                        req.status === 'Pendiente' ? 'bg-secondary-100 text-secondary-700' :
                        req.status === 'Aprobada' ? 'bg-primary-100 text-primary-700' :
                        'bg-red-100 text-red-700'
                      }`}>
                        {req.status}
                      </span>
                    </td>
                    
                    {/* Botones de Acción */}
                    <td className="p-4 text-right">
                      {req.status === 'Pendiente' ? (
                        <div className="flex justify-end gap-2">
                          <button 
                            onClick={() => handleStatusChange(req.id, 'Aprobada')}
                            disabled={processingId === req.id}
                            className="p-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition disabled:opacity-50"
                            title="Aprobar Adopción"
                          >
                            <CheckCircle className="w-5 h-5" />
                          </button>
                          <button 
                            onClick={() => handleStatusChange(req.id, 'Rechazada')}
                            disabled={processingId === req.id}
                            className="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition disabled:opacity-50"
                            title="Rechazar Adopción"
                          >
                            <XCircle className="w-5 h-5" />
                          </button>
                        </div>
                      ) : (
                        <span className="text-sm text-gray-400 italic">Procesada</span>
                      )}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};