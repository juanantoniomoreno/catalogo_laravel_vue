<template>
    <nav class="navbar">
      <div class="navbar-brand">
        <router-link to="/" class="navbar-logo">
          Mi Tienda de Licencias
        </router-link>
      </div>
      <ul class="navbar-nav">
        <li class="nav-item">
          <router-link to="/" class="nav-link">
            Productos
          </router-link>
        </li>
        <li class="nav-item"> 
          <router-link to="/products/create" class="nav-link">
            Crear Producto
          </router-link>
        </li>
      </ul>
      <div class="user-info">
        <span v-if="currentUser">Hola, {{ currentUser.name }} ({{ currentUser.role }})</span>
        <span v-else>Invitado</span>
      </div>
    </nav>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  
  export default {
    name: 'Navbar',
    setup() {
      const currentUser = ref(null);
      const isAdmin = ref(false);
  
      onMounted(() => {
        // Accede a los datos del usuario desde window.App
        if (window.App && window.App.user) {
          currentUser.value = window.App.user;
          isAdmin.value = window.App.user.role === 'admin';
        }
      });
  
      return {
        currentUser,
        isAdmin,
      };
    },
  };
  </script>
  
  <style scoped>
  /* Estilos específicos para este componente Navbar */
  .navbar {
    background-color: #333;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
  
  .navbar-brand {
    margin-right: 20px;
  }
  
  .navbar-logo {
    color: white;
    text-decoration: none;
    font-size: 1.5em;
    font-weight: bold;
  }
  
  .navbar-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
  }
  
  .nav-item {
    margin-left: 20px;
  }
  
  .nav-link {
    color: white;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
  }
  
  .nav-link:hover {
    background-color: #555;
  }
  
  .nav-link.router-link-exact-active {
    background-color: #007bff;
    color: white;
  }
  .user-info {
    color: #ccc;
    font-size: 0.9em;
  }
  </style>