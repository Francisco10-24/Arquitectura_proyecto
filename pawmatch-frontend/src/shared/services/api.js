import axios from 'axios';
import { db } from '../utils/mockData';

// Configuración real para cuando se conecte Laravel 
export const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { 'Content-Type': 'application/json' }
});

// Simulador de retraso de red (para que parezca que carga de verdad)
const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));


export const mockApi = {
  // Simula el Login
  login: async (email, password) => {
    await delay(800); // Tarda casi 1 segundo
    const user = db.users.find(u => u.email === email && u.password === password);
    
    if (!user) throw new Error('Credenciales inválidas');
    
    // Quitamos el password por seguridad antes de devolverlo
    const { password: _, ...userData } = user;
    return { user: userData, token: 'mock-jwt-token-123' };
  },
  
  // Simula traer las mascotas
  getPets: async () => {
    await delay(500);
    return db.pets;
  },

  // Simula traer una sola mascota por su ID
    getPetById: async (id) => {
    await delay(300);
    const pet = db.pets.find(p => p.id === parseInt(id));
    if (!pet) throw new Error('Mascota no encontrada');
    return pet;
  },
  getRequests: async () => {
    await delay(500);
    // Hacemos un "Join" manual para que la tabla tenga nombres en vez de solo IDs
    return db.requests.map(req => {
      const user = db.users.find(u => u.id === req.userId);
      const pet = db.pets.find(p => p.id === req.petId);
      return { 
        ...req, 
        userName: user ? user.name : 'Usuario Desconocido', 
        petName: pet ? pet.name : 'Mascota Desconocida' 
      };
    });
  },

  //Cambiar el estado de una solicitud (Aprobar/Rechazar)
  updateRequestStatus: async (requestId, newStatus) => {
    await delay(600);
    const request = db.requests.find(r => r.id === requestId);
    if (!request) throw new Error('Solicitud no encontrada');

    request.status = newStatus; // Actualizamos la solicitud

    // REGLA DE NEGOCIO: Si se aprueba, la mascota pasa a "Adoptada"
    if (newStatus === 'Aprobada') {
      const pet = db.pets.find(p => p.id === request.petId);
      if (pet) pet.status = 'Adoptada';
    }
    // Si se rechaza, la mascota vuelve a estar "Disponible"
    if (newStatus === 'Rechazada') {
      const pet = db.pets.find(p => p.id === request.petId);
      if (pet) pet.status = 'Disponible';
    }

    return request;
  },

  // Crear una nueva solicitud de adopción
  createRequest: async (userId, petId) => {
    await delay(600);
    
    // ¿Ya tiene una solicitud pendiente para esta misma mascota?
    const existing = db.requests.find(r => r.userId === userId && r.petId === petId && r.status === 'Pendiente');
    if (existing) throw new Error('Ya tienes una solicitud pendiente para este peludito.');

    //¿La mascota sigue disponible?
    const pet = db.pets.find(p => p.id === petId);
    if (!pet || pet.status !== 'Disponible') throw new Error('Lo sentimos, esta mascota ya no está disponible.');

    // Creamos la nueva solicitud
    const newRequest = {
      id: Date.now(), // Generamos un ID aleatorio temporal
      userId: userId,
      petId: petId,
      status: 'Pendiente',
      date: new Date().toISOString().split('T')[0] 
    };
    db.requests.push(newRequest);

    //La mascota pasa a estar "En Proceso"
    pet.status = 'En Proceso';

    return newRequest;
  },

  // Obtener solo las solicitudes del usuario actual
  getUserRequests: async (userId) => {
    await delay(500);
    // Filtramos solo las de este usuario y le pegamos el nombre de la mascota
    return db.requests
      .filter(req => req.userId === userId)
      .map(req => {
        const pet = db.pets.find(p => p.id === req.petId);
        return { 
          ...req, 
          petName: pet ? pet.name : 'Mascota Desconocida',
          petImage: pet ? pet.image : null
        };
      });
  }
};