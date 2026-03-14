import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import mascotaService from '../../../services/mascotaService';
import { Search, SlidersHorizontal } from 'lucide-react';

export const CatalogPage = () => {
  const [pets, setPets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  
  // Filtros
  const [filters, setFilters] = useState({
    especie: '',
    sexo: '',
    search: '',
  });

  // Estado temporal para el input (para evitar llamadas constantes)
  const [searchInput, setSearchInput] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  // Cargar mascotas cuando cambian los filtros (excepto search)
  useEffect(() => {
    fetchPets();
  }, [filters.especie, filters.sexo]);

  // Debounce para la búsqueda 
  useEffect(() => {
    const timer = setTimeout(() => {
      setFilters(prev => ({ ...prev, search: searchInput }));
    }, 500);

    return () => clearTimeout(timer);
  }, [searchInput]);

  // Cargar mascotas cuando cambia el filtro de búsqueda
  useEffect(() => {
    if (filters.search !== undefined) {
      fetchPets();
    }
  }, [filters.search]);

  const fetchPets = async () => {
    try {
      setLoading(true);
      const response = await mascotaService.getAll(filters);
      setPets(response.data);
      setError('');
    } catch (err) {
      console.error('Error cargando mascotas:', err);
      setError('Error al cargar las mascotas. Intenta de nuevo.');
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const clearFilters = () => {
    setFilters({ especie: '', sexo: '', search: '' });
    setSearchInput('');
  };

  if (loading && pets.length === 0) {
    return (
      <div className="flex flex-col justify-center items-center h-[60vh]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mb-4"></div>
        <p className="text-gray-500 font-medium">Buscando peluditos...</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-6 py-8 max-w-7xl">
      
      {/* Cabecera */}
      <div className="mb-8">
        <h1 className="text-3xl md:text-4xl font-bold text-gray-900">
          Mascotas en Adopción
        </h1>
        <p className="text-gray-600 mt-2 text-lg">
          Encuentra a tu nuevo mejor amigo. {pets.length > 0 && `Tenemos ${pets.length} peluditos esperando por ti.`}
        </p>
      </div>

      {/* Barra de búsqueda y filtros */}
      <div className="mb-8 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div className="flex flex-col md:flex-row gap-4">
          
          {/* Búsqueda */}
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              type="text"
              placeholder="Buscar por nombre, raza..."
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            />
          </div>

          {/* Botón de filtros */}
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition"
          >
            <SlidersHorizontal className="w-5 h-5" />
            Filtros
          </button>
        </div>

        {/* Filtros expandibles */}
        {showFilters && (
          <div className="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
            
            {/* Especie */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Especie
              </label>
              <select
                value={filters.especie}
                onChange={(e) => handleFilterChange('especie', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Todas</option>
                <option value="PERRO">Perros</option>
                <option value="GATO">Gatos</option>
                <option value="OTRO">Otros</option>
              </select>
            </div>

            {/* Sexo */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Sexo
              </label>
              <select
                value={filters.sexo}
                onChange={(e) => handleFilterChange('sexo', e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
              >
                <option value="">Todos</option>
                <option value="MACHO">Macho</option>
                <option value="HEMBRA">Hembra</option>
              </select>
            </div>

            {/* Limpiar filtros */}
            <div className="flex items-end">
              <button
                onClick={clearFilters}
                className="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium transition"
              >
                Limpiar filtros
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Indicador de carga mientras filtra */}
      {loading && pets.length > 0 && (
        <div className="text-center py-4">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>
      )}

      {/* Mensaje de error */}
      {error && (
        <div className="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
          {error}
        </div>
      )}

      {/* Grid de Tarjetas */}
      {pets.length === 0 && !loading ? (
        <div className="text-center py-12">
          <p className="text-gray-500 text-lg">
            No se encontraron mascotas con estos filtros.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {pets.map(pet => (
            <div 
              key={pet.id} 
              className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col"
            >
              
              {/* Imagen */}
              <div className="h-56 w-full relative">
                <img 
                  src={pet.foto_url || '/images/pets/default.jpg'} 
                  alt={`Foto de ${pet.nombre}`} 
                  className="w-full h-full object-cover"
                  onError={(e) => {
                    e.target.src = '/images/pets/default.jpg';
                  }}
                />
                
                {/* Etiqueta de estado */}
                <span className={`absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-bold shadow-sm ${
                  pet.estado === 'DISPONIBLE' ? 'bg-green-500 text-white' :
                  pet.estado === 'ADOPTADA' ? 'bg-gray-500 text-white' : 
                  'bg-yellow-500 text-white'
                }`}>
                  {pet.estado === 'DISPONIBLE' ? 'Disponible' :
                   pet.estado === 'ADOPTADA' ? 'Adoptada' :
                   pet.estado === 'EN_PROCESO' ? 'En Proceso' : 'Inactiva'}
                </span>
              </div>

              {/* Contenido */}
              <div className="p-6 flex-grow flex flex-col">
                <h2 className="text-2xl font-bold text-gray-800 mb-1">
                  {pet.nombre}
                </h2>
                <p className="text-gray-500 text-sm mb-2">
                  {pet.raza || 'Mestizo'} • {pet.edad_aproximada ? `${Math.floor(pet.edad_aproximada / 12)} años` : 'Edad desconocida'}
                </p>
                <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                  {pet.descripcion}
                </p>
                
                {/* Botón */}
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
      )}
    </div>
  );
};