<template>
    <h1>Listado de {{ listTitle }}</h1>
    <div class="product-list">
        <p v-if="loading">Cargando productos...</p>
        <p v-if="error" class="error-message">{{ error }}</p>

        <router-link v-if="filterType === 'simple'" to="/products/create" class="create-product-button">
            Crear Nuevo Producto
        </router-link>
        <router-link v-if="filterType === 'pack'" :to="{ path: '/products/create', query: { type: 'pack' } }"
            class="create-product-button">
            Crear Nuevo Pack
        </router-link>

        <div v-if="!loading && products.length === 0">
            <p>No hay productos disponibles.</p>
        </div>

        <div v-else class="products-grid">
            <div v-for="product in products" :key="product.id" class="product-card">
                <img :src="product.main_image_url || 'https://via.placeholder.com/150'" alt="Imagen del producto"
                    class="product-image">
                <div class="product-details">
                    <h2>{{ product.name }}</h2>
                    <p class="product-description">{{ product.description }}</p>
                    <p class="product-price">{{ product.price ?? 'N/A' }}</p>
                    <p class="product-status">Estado: {{ product.status }}</p>
                    <div class="product-actions">
                        <router-link :to="{ name: 'ProductEditForm', params: { productId: product.id } }"
                            class="edit-button">
                            Editar
                        </router-link>
                        <button @click="confirmDelete(product.id)" class="delete-button">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, watch, computed } from 'vue';
import axios from 'axios';

export default {
    props: {        
        filterType: {
            type: String
        }
    },
    setup(props) {
        const products = ref([]);
        const loading = ref(true);
        const error = ref(null);

        const listTitle = computed(() => {
            let typeItems = 'Productos';

            if (props.filterType === 'pack') {
                typeItems = 'Packs';
            }
            return typeItems;
        });

        const fetchProducts = async () => {
            try {
                let apiUrl = '/api/products';
                const params = {};

                if (props.filterType) {
                    apiUrl = '/api/products/filtered';
                    params.type = props.filterType
                }

                const response = await axios.get(apiUrl, { params });
                // console.log('API Response data for ProductList:', response.data); 
                products.value = response.data;
            } catch (err) {
                error.value = 'Error al cargar los productos. Por favor, inténtalo de nuevo más tarde.';
                console.error('Error fetching products:', err);
            } finally {
                loading.value = false;
            }
        };

        const confirmDelete = async (productId) => {
            if (confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
                await deleteProduct(productId);
            }
        };

        const deleteProduct = async (productId) => {
            try {
                await axios.delete(`/api/products/${productId}`);
                // Si la eliminación es exitosa, filtra el producto de la lista local
                products.value = products.value.filter(p => p.id !== productId);
                alert('Producto eliminado exitosamente.');
            } catch (err) {
                console.error('Error deleting product:', err);
                let errorMessage = 'Error al eliminar el producto.';
                if (err.response) {
                    if (err.response.status === 403) {
                        errorMessage = 'No tienes permiso para eliminar este producto.';
                    } else if (err.response.data && err.response.data.message) {
                        errorMessage = err.response.data.message;
                    }
                }
                alert(errorMessage);
            }
        };

        // Vuelve a cargar productos cada vez que filterType cambie
        watch(() => props.filterType, fetchProducts, { immediate: true });

        return {
            error,
            filterType: computed(() => props.filterType),
            loading,
            listTitle,
            products,
            confirmDelete,
        };
    }
}
</script>

<style scoped>
.product-list {
    padding: 20px;
    max-width: 960px;
    margin: 0 auto;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.product-card {
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding-bottom: 15px;
}

.product-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    object-position: center;
    border-bottom: 1px solid #eee;
}

.product-details {
    padding: 15px;
}

.product-details h2 {
    font-size: 1.4em;
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
}

.product-description {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 10px;
}

.product-price {
    font-size: 1.2em;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 5px;
}

.product-status {
    font-size: 0.8em;
    color: #888;
}

.error-message {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.product-actions {
    display: flex;
    gap: 10px;
    /* Espacio entre los botones */
    margin-top: 15px;
}

.edit-button,
.delete-button {
    padding: 0.5em 1em;
    border-radius: 5px;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 0.9em;
}

.edit-button {
    background-color: #007bff;
    color: white;
    border: none;
}

.edit-button:hover {
    background-color: #0056b3;
}

.delete-button {
    background-color: #dc3545;
    color: white;
    border: none;
}

.delete-button:hover {
    background-color: #c82333;
}

.create-product-button {
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 0.25em 1.25em;
    margin-right: 0.5em;
    border-radius: 5px;
    text-decoration: none;
    /* Eliminar el subrayado del enlace */
    margin-bottom: 20px;
    /* Espacio debajo del botón antes de la lista */
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.create-product-button:hover {
    background-color: #218838;
}
</style>