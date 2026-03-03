import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { mockApi } from '../../../shared/services/api';

export const CatalogPage = () => {
  // Definición de Estados
  const [pets, setPets] = useState([]);
  const [loading, setLoading] = useState(true);

  // Efecto para cargar los datos al montar el componente
  useEffect(() => {
    const fetchPets = async () => {
      try {
        const data = await mockApi.getPets();
        setPets(data);
      } catch (error) {
        console.error("Error cargando mascotas", error);
      } finally {
        setLoading(false);
      }
    };
    
    fetchPets();
  }, []);

  //  Renderizado Condicional: Estado de Carga (Loading)
  if (loading) {
    return (
      <div className="flex flex-col justify-center items-center h-[60vh]">
        {/* Spinner animado con Tailwind */}
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-4"></div>
        <p className="text-gray-500 font-medium">Buscando peluditos...</p>
      </div>
    );
  }

  //  Renderizado Principal: El Catálogo
  return (
    <div className="container mx-auto px-6 py-8 max-w-7xl">
      
      {/* Cabecera de la página */}
      <div className="mb-10">
        <h1 className="text-3xl md:text-4xl font-bold text-gray-900">Mascotas en Adopción</h1>
        <p className="text-gray-600 mt-2 text-lg">
          Encuentra a tu nuevo mejor amigo. Tenemos {pets.length} peluditos esperando por ti.
        </p>
      </div>

      {/* Grid de Tarjetas */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        {pets.map(pet => (
          <div key={pet.id} className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col">
            
            {/* Imagen de las mascotas */}
                <div className="h-56 w-full relative">
                <img 
                    src={`/images/pets/${pet.image}`} 
                    alt={`Foto de ${pet.name}`} 
                    className="w-full h-full object-cover"
                />
            {/* Etiqueta flotante para el estado */}
                <span className={`absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-bold shadow-sm ${
                pet.status === 'Disponible' ? 'bg-primary-500 text-white' :
                pet.status === 'Adoptada' ? 'bg-gray-500 text-white' : 'bg-secondary-500 text-white'
            }`}>
                {pet.status}
                </span>
            </div>

            {/* Contenido de la Tarjeta */}
            <div className="p-6 flex-grow flex flex-col">
              <h2 className="text-2xl font-bold text-gray-800 mb-1">{pet.name}</h2>
              <p className="text-gray-500 text-sm mb-4">
                {pet.breed} • {pet.age} {pet.age === 1 ? 'año' : 'años'}
              </p>
              
              {/* Empujamos el botón hacia abajo si hay espacio extra */}
              <div className="mt-auto pt-4">
                <Link
                  to={`/pets/${pet.id}`}
                  className="block w-full text-center bg-primary-50 text-primary-700 border border-primary-100 py-2.5 rounded-xl hover:bg-primary-600 hover:text-white transition-colors font-semibold"
                >
                  Ver detalles
                </Link>
              </div>
            </div>

          </div>
        ))}

      </div>
    </div>
  );
};