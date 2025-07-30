<template>
    <div class="offer-list">
        <h1>Listado de Ofertas</h1>

        <router-link to="/offers/create" class="create-button">
            Crear Nueva Oferta
        </router-link>

        <div v-if="loading" class="loading-message">Cargando ofertas...</div>
        <div v-else-if="error" class="error-message">{{ error }}</div>
        <div v-else-if="offers.length === 0" class="no-offers-message">
            <p>No hay ofertas disponibles.</p>
        </div>

        <div v-else class="offers-grid">
            <div v-for="offer in offers" :key="offer.id" class="offer-card">
                <div class="offer-details">
                    <p class="offer-price">Precio: {{ offer.offer_price }} €</p>
                    <p class="offer-dates">
                        Fechas: {{ formatDate(offer.start_date) }} - {{ formatDate(offer.end_date) }}
                    </p>
                    <p class="offer-status">Estado: {{ offer.status }}</p>
                    <p class="offer-product">Producto: {{ offer.product_name || 'N/A' }}</p>

                    <div class="offer-actions">
                        <router-link :to="{ name: 'OfferEdit', params: { offerId: offer.id } }" class="edit-button">
                            Editar
                        </router-link>
                        <button @click="confirmDelete(offer.id)" class="delete-button">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'OfferList',
    setup() {
        const offers = ref([]);
        const loading = ref(true);
        const error = ref(null);

        const fetchOffers = async () => {
            loading.value = true;
            error.value = null;

            try {
                const response = await axios.get('/api/offers');
                offers.value = response.data;
            } catch (err) {
                error.value = 'Error fetching offers';
                console.error('Error fetching offers:', err);
            } finally {
                loading.value = false;
            }
        };

        const confirmDelete = async (offerId) => {
            if (confirm('¿Estás seguro de que quieres eliminar esta oferta? Esta acción no se puede deshacer.')) {
                await deleteOffer(offerId);
            }
        };

        const deleteOffer = async (offerId) => {
            try {
                await axios.delete(`/api/offers/${offerId}`);
                offers.value = offers.value.filter(o => o.id !== offerId);
                alert('Oferta eliminada exitosamente.');
            } catch (err) {
                console.error('Error deleting offer:', err);
                let errorMessage = 'Error al eliminar la oferta.';
                if (err.response) {
                    if (err.response.status === 403) {
                        errorMessage = 'No tienes permiso para eliminar esta oferta.';
                    } else if (err.response.data && err.response.data.message) {
                        errorMessage = err.response.data.message;
                    }
                }
                alert(errorMessage);
            }
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateString).toLocaleDateString(undefined, options);
        };

        onMounted(fetchOffers);

        return {
            offers,
            loading,
            error,
            confirmDelete,
            formatDate,
        };
    }
};
</script>

<style scoped>
.offer-list {
    max-width: var(--max-width-lg);
    margin: 0 auto;
    padding: var(--spacing-xl);
}

.header-actions {
    margin-bottom: var(--spacing-xl);
    display: flex;
    justify-content: flex-end;
}

.create-button {
    display: inline-block;
    background-color: var(--color-success);
    color: white;
    margin-right: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-xs) var(--spacing-xl);
    border-radius: 5px;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.create-button:hover {
    background-color: #218838;
}

.loading-message,
.error-message,
.no-offers-message {
    padding: var(--spacing-lg);
    border-radius: 5px;
    margin-bottom: var(--spacing-xl);
    text-align: center;
    font-weight: bold;
}

.loading-message {
    background-color: #e0f7fa;
    color: #00796b;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.no-offers-message {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

.offers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.offer-card {
    background-color: var(--color-background-light);
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease-in-out;
    display: flex;
    flex-direction: column;
}

.offer-card:hover {
    transform: translateY(-5px);
}

.offer-details {
    padding: var(--spacing-xl);
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.offer-details h2 {
    font-size: 1.6em;
    color: #333;
    margin-top: 0;
    margin-bottom: var(--spacing-md);
    line-height: 1.3;
}

.offer-description {
    color: #666;
    font-size: var(--font-size-base);
    margin-bottom: var(--spacing-lg);
    line-height: 1.5;
    flex-grow: 1;
}

.offer-price {
    font-size: var(--font-size-base);
    font-weight: bold;
    color: var(--color-success);
    margin-bottom: var(--spacing-md);
}

.offer-dates,
.offer-status,
.offer-product {
    font-size: var(--font-size-md);
    color: #888;
    margin-bottom: var(--spacing-xs);
}

.offer-actions {
    display: flex;
    gap: 10px;
    margin-top: var(--spacing-lg);
}

.edit-button,
.delete-button {
    padding: var(--spacing-xs) var(--spacing-lg);
    border-radius: 5px;
    text-decoration: nvar(--spacing-lg);
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: var(--font-size-md);
}

.edit-button {
    background-color: var(--color-primary);
    color: white;
    border: nonevar(--spacing-lg);
}

.edit-button:hover {
    background-color: var(--color-primary-hover);
}

.delete-button {
    background-color: var(--color-danger);
    color: white;
    border: none;
}

.delete-button:hover {
    background-color: var(--color-danger-hover);
}
</style>