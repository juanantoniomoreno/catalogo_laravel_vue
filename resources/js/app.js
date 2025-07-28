import './bootstrap'; // Laravel bootstrap (Axios, etc.)

import { createApp } from 'vue';
import App from './App.vue'; // Tu componente raíz de la aplicación Vue
import router from './router'; // Importa tu configuración de router

const app = createApp(App); // Crea la aplicación con el componente App.vue como raíz

app.use(router); // Usa Vue Router

app.mount('#app'); // Monta la aplicación en el elemento con id="app"