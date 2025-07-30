<template>
    <h1>Listado de {{ listTitle }}</h1>
    <div class="product-list">
        <router-link v-if="filterType === 'simple'" to="/products/create" class="create-product-button">
            Crear Nuevo Producto
        </router-link>
        <router-link v-if="filterType === 'pack'" :to="{ path: '/products/create', query: { type: 'pack' } }"
            class="create-product-button">
            Crear Nuevo Pack
        </router-link>

        <p v-if="loading">Cargando productos...</p>
        <p v-if="error" class="error-message">{{ error }}</p>
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
    padding: var(--spacing-xl);
    max-width: var(--max-width-lg);
    margin: 0 auto;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;    
}

.product-card {
    background-color: var(--color-background-light);
    border: 1px solid var(--color-border-light);
    border-radius: var(--border-radius-md);
    box-shadow: var(--box-shadow-light);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    padding-bottom: var(--spacing-lg);
}

.product-image {
    width: 100%;
    height: 200px;    
    object-fit: cover;
    object-position: center;
    border-bottom: var(--border-width) solid var(--color-border-medium);
}

.product-details {
    padding: var(--spacing-lg);
}

.product-details h2 {
    font-size: var(--font-size-h2);
    margin-top: 0;
    margin-bottom: var(--spacing-sm);
    color: var(--color-text-dark);
}

.product-description {
    font-size: var(--font-size-md);
    color: var(--color-text-light);
    margin-bottom: var(--spacing-sm);
}

.product-price {
    font-size: var(--font-size-h3);
    font-weight: bold;
    color: var(--color-primary);
    margin-bottom: var(--spacing-xs);
}

.product-status {
    font-size: var(--font-size-sm);    
    color: var(--color-text-lighter);    
}

.error-message {
    color: var(--color-danger);    
    background-color: var(--color-background-error);    
    border: 1px solid var(--color-border-error);    
    padding: var(--spacing-sm);    
    border-radius: var(--border-radius-sm);    
    margin-bottom: var(--spacing-lg);    
}

.product-actions {
    display: flex;
    justify-content: space-around;
    padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-lg);    
    border-top: 1px solid var(--color-border-medium);    
    background-color: var(--color-background-gray);    
}

.edit-button,
.delete-button {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-lg);
    margin: 0 var(--spacing-xs);
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: var(--font-size-md);
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease;
}

.edit-button {
    background-color: var(--color-primary);
    color: var(--color-background-light);
    border: none;
}

.edit-button:hover {
    background-color: var(--color-primary-hover);
}

.delete-button {
    background-color: var(--color-danger);
    color: var(--color-background-light);
    border: none;
}

.delete-button:hover {
    background-color: var(--color-danger-hover);
}

.create-product-button {
    display: inline-block;
    background-color: var(--color-success);
    color: var(--color-background-light);
    padding: var(--spacing-xs) var(--spacing-xl);
    margin-right: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
    border-radius: 5px;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.create-product-button:hover {
    background-color: #218838;
}
</style>