import { createRouter, createWebHistory } from 'vue-router';
// Componentes de Productos
import ProductList from '../components/product/list/ProductList.vue'
import ProductCreateForm from '../components/product/create/ProductCreateForm.vue'
// Componentes de Ofertas
import OfferList from '../components/offer/list/OfferList.vue';
import OfferForm from '../components/offer/form/OfferForm.vue';
// Componentes de Opciones
import OptionList from '../components/option/list/OptionList.vue';
import OptionForm from '../components/option/form/OptionForm.vue';


const routes = [
    // Product Routes
    {
        path: '/',
        redirect: '/products/simple/list'
    },
    {
        path: '/products/simple/list',
        name: 'ProductList',
        component: ProductList,
        props: {
            filterType: 'simple'
        }
    },
    {
        path: '/products/create',
        name: 'ProductCreateForm',
        component: ProductCreateForm,
        props: (route) => ({
            productId: null,
            initialType: route.query.type || 'simple'
        })
    },
    {
        path: '/products/edit/:productId',
        name: 'ProductEditForm',
        component: ProductCreateForm,
        props: true
    },
    {
        path: '/products/packs/list',
        name: 'ProductPackList',
        component: ProductList,
        props: {
            filterType: 'pack'
        }
    },
    // Offer Routes
    {
        path: '/offers',
        name: 'OfferList',
        component: OfferList,
    },
    {
        path: '/offers/create',
        name: 'OfferCreate',
        component: OfferForm,
        props: {
            offerId: null
        },
    },
    {
        path: '/offers/edit/:offerId',
        name: 'OfferEdit',
        component: OfferForm,
        props: true,
    },
    // --- NUEVAS RUTAS PARA OPCIONES ---
    {
        path: '/options',
        name: 'OptionList',
        component: OptionList,
    },
    {
        path: '/options/create',
        name: 'OptionCreate',
        component: OptionForm,
    },
    {
        path: '/options/:optionId/edit',
        name: 'OptionEdit',
        component: OptionForm,
        props: true,
    }


    // Añade más rutas aquí
    // { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound } // Opcional: para rutas no encontradas
];

const router = createRouter({
    history: createWebHistory(), // Usa history mode para URLs limpias (sin #)
    routes,
});

export default router;