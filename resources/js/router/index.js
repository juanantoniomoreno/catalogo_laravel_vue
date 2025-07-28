import { createRouter, createWebHistory } from 'vue-router';
import ProductList from '../components/product/list/ProductList.vue'
import ProductCreateForm from '../components/product/create/ProductCreateForm.vue'

const routes = [
    {
        path: '/',
        name: 'ProductList',
        component: ProductList
    },
    {
        path: '/products/create',
        name: 'ProductCreateForm',
        component: ProductCreateForm
    },
    // Añade más rutas aquí
    // { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound } // Opcional: para rutas no encontradas
];

const router = createRouter({
    history: createWebHistory(), // Usa history mode para URLs limpias (sin #)
    routes,
});

export default router;