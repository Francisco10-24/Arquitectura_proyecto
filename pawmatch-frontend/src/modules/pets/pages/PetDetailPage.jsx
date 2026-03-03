import { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { mockApi } from '../../../shared/services/api';
import { useAuth } from '../../../context/AuthContext'; // <-- Importamos la sesión
import { ArrowLeft, Heart, Loader2, CheckCircle } from 'lucide-react';

export const PetDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth(); //Obtenemos el usuario actual
  
  const [pet, setPet] = useState(null);
  const [loading, setLoading] = useState(true);
  
  // Estados para el botón de adopción
  const [isRequesting, setIsRequesting] = useState(false);
  const [requestSuccess, setRequestSuccess] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    const fetchPet = async () => {
      try {
        const data = await mockApi.getPetById(id);
        setPet(data);
      } catch (error) {
        navigate('/pets');
      } finally {
        setLoading(false);
      }
    };
    fetchPet();
  }, [id, navigate]);

  // Función que se ejecuta al hacer clic en adoptar
  const handleAdoptionRequest = async () => {
    // Si no está logueado, lo mandamos al login
    if (!user) {
      navigate('/login');
      return;
    }

    setIsRequesting(true);
    setErrorMsg('');

    try {
      await mockApi.createRequest(user.id, pet.id);
      setRequestSuccess(true);
      // Actualizamos visualmente el estado de la mascota a "En Proceso"
      setPet({ ...pet, status: 'En Proceso' }); 
    } catch (error) {
      setErrorMsg(error.message);
    } finally {
      setIsRequesting(false);
    }
  };

  if (loading) return <div className="text-center p-20 font-bold text-gray-500">Cargando detalles...</div>;
  if (!pet) return null;

  return (
    <div className="container mx-auto px-6 py-10 max-w-5xl">
      <Link to="/pets" className="inline-flex items-center text-gray-500 hover:text-primary-600 mb-8 font-medium">
        <ArrowLeft className="w-5 h-5 mr-2" /> Volver al catálogo
      </Link>

      <div className="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row">
        <div className="md:w-1/2 h-80 md:h-auto relative">
          <img src={`/images/pets/${pet.image}`} alt={pet.name} className="w-full h-full object-cover" />
        </div>

        <div className="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
          <div className="flex justify-between items-start mb-4">
            <h1 className="text-4xl font-extrabold text-gray-900">{pet.name}</h1>
            <span className={`px-4 py-1.5 rounded-full text-sm font-bold shadow-sm ${
              pet.status === 'Disponible' ? 'bg-primary-100 text-primary-700' : 'bg-secondary-100 text-secondary-700'
            }`}>
              {pet.status}
            </span>
          </div>
          
          <div className="space-y-4 mb-8">
            <p className="text-lg text-gray-600"><span className="font-semibold text-gray-800">Raza:</span> {pet.breed}</p>
            <p className="text-lg text-gray-600"><span className="font-semibold text-gray-800">Edad:</span> {pet.age} años</p>
          </div>

          {/* MENSAJES DE ERROR O ÉXITO */}
          {errorMsg && <p className="text-red-500 mb-4 font-medium text-sm">{errorMsg}</p>}
          
          {requestSuccess ? (
            <div className="bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
              <CheckCircle className="w-6 h-6" />
              <div>
                <p className="font-bold">¡Solicitud enviada!</p>
                <Link to="/user/dashboard" className="text-sm underline hover:text-green-800">Ver mis solicitudes</Link>
              </div>
            </div>
          ) : (
            /* BOTÓN CONDICIONAL */
            pet.status === 'Disponible' ? (
              <button 
                onClick={handleAdoptionRequest}
                disabled={isRequesting}
                className="w-full bg-primary-600 text-white py-4 rounded-xl text-lg font-bold hover:bg-primary-700 transition flex items-center justify-center gap-2 shadow-lg disabled:opacity-70"
              >
                {isRequesting ? <Loader2 className="w-6 h-6 animate-spin" /> : <Heart className="w-6 h-6" />}
                {user ? 'Solicitar Adopción' : 'Inicia sesión para adoptar'}
              </button>
            ) : (
              <div className="w-full bg-gray-50 border border-gray-200 text-gray-500 py-4 rounded-xl text-center text-lg font-medium">
                Esta mascota ya está en proceso de adopción
              </div>
            )
          )}
        </div>
      </div>
    </div>
  );
};