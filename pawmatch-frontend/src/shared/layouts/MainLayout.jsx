import { Outlet } from 'react-router-dom';
import { Navbar } from '../components/Navbar';


export const MainLayout = () => {
  return (
    <div className="min-h-screen bg-surface flex flex-col">
      {/* Navbar Fijo arriba */}
      <Navbar />

      {/* Contenido Principal Dinámico */}
      <main className="flex-grow">
        {/* Aquí renderizará la página actual correspondiente a la ruta */}
        <Outlet /> 
      </main>

      
      <footer className="bg-white border-t border-gray-100 py-6 mt-12 text-center text-gray-500 text-sm">
        <div className="container mx-auto">
          © {new Date().getFullYear()} PawMatch Plataforma de Adopción. Todos los derechos reservados.
        </div>
      </footer>
    </div>
  );
};