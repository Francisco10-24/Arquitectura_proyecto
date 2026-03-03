// src/shared/utils/mockData.js

export const db = {
  users: [
    { id: 1, name: 'Juan Adoptante', email: 'user@pawmatch.com', role: 'user', password: 'password' },
    { id: 2, name: 'Admin Refugio', email: 'admin@pawmatch.com', role: 'admin', password: 'password' }
  ],
  pets: [
    { id: 101, name: 'Max', breed: 'Golden Retriever', age: 2, status: 'Disponible', image: 'max.png' },
    { id: 102, name: 'Luna', breed: 'Mestizo', age: 1, status: 'En Proceso', image: 'luna.jpg' },
    { id: 103, name: 'Bella', breed: 'Dalmata', age: 1, status: 'Adoptada', image: 'bella.png' }
  ],
  requests: [
    { id: 501, userId: 1, petId: 102, status: 'Pendiente', date: '2026-03-02' }
  ]
};