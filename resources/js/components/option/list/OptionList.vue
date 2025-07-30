<template>
    <div class="option-list-container">
        <h1>Listado de Opciones</h1>
        <router-link to="/options/create" class="create-button">
            Crear Nueva Opción
        </router-link>

        <div v-if="loading" class="loading-message">Cargando opciones...</div>
        <div v-if="error" class="error-message general-error">{{ error }}</div>

        <div v-if="!loading && options.length === 0" class="no-data-message">
            No hay opciones disponibles.
        </div>

        <div v-if="options.length > 0" class="option-cards-grid">
            <div v-for="option in options" :key="option.id" class="option-card">
                <div class="card-image">
                    <img :src="option.image_url || '/images/default_option.png'" alt="Imagen de la Opción" />
                </div>
                <div class="card-content">
                    <h2 class="card-title">{{ option.name || 'Sin Nombre' }}</h2>
                    <p class="card-description">{{ option.description || 'Sin descripción.' }}</p>
                    <div class="card-detail">
                        <strong>Producto:</strong>&nbsp;
                        <span v-if="option.product_name">{{ option.product_name }}</span>
                        <span v-else>N/A</span>
                    </div>
                    <div class="card-detail"><strong>Precio:</strong> {{ option.price ? `${option.price} €` : 'N/A' }}
                    </div>
                    <div class="card-detail"><strong>Estado:</strong> {{ getStatusDisplay(option.status) }}</div>
                </div>
                <div class="card-actions">
                    <router-link :to="`/options/${option.id}/edit`" class="edit-button">
                        Editar
                    </router-link>
                    <button @click="confirmDelete(option.id)" class="delete-button">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'OptionList',
    setup() {
        const options = ref([]);
        const loading = ref(true);
        const error = ref(null);

        const fetchOptions = async () => {
            loading.value = true;
            error.value = null;
            try {
                const response = await axios.get('/api/options');
                options.value = response.data;
            } catch (err) {
                console.error('Error fetching options:', err);
                error.value = 'Error al cargar las opciones. Por favor, inténtalo de nuevo.';
            } finally {
                loading.value = false;
            }
        };

        const confirmDelete = async (optionId) => {
            if (confirm('¿Estás seguro de que quieres eliminar esta opción? Esta acción es irreversible.')) {
                try {
                    await axios.delete(`/api/options/${optionId}`);
                    options.value = options.value.filter(o => o.id !== optionIdId);
                    alert('Opción eliminada exitosamente.');
                } catch (err) {
                    console.error('Error deleting option:', err);
                    alert('Error al eliminar la opción. Por favor, inténtalo de nuevo.');
                }
            }
        };

        const getStatusDisplay = (status) => {
            switch (status) {
                case 'active':
                    return 'Activa';
                case 'inactive':
                    return 'Inactiva';
                default:
                    return status;
            }
        };

        onMounted(() => {
            fetchOptions();
        });

        return {
            options,
            loading,
            error,
            confirmDelete,
            getStatusDisplay,
        };
    },
};
</script>

<style scoped>
.option-list-container {
    padding: var(--spacing-xl);
    max-width: var(--max-width-lg);
    margin: 0 auto;
}

.create-button {
    display: inline-block;
    background-color: var(--color-success);
    color: white;
    padding: var(--spacing-xs) var(--spacing-xl);
    margin-right: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
    font-weight: bold;
}

.create-button:hover {
    background-color: #218838;
}

.loading-message,
.no-data-message {
    text-align: center;
    padding: var(--spacing-xl);
    font-size: var(--font-size-lg);
    color: var(--color-text-light);
}

.error-message.general-error {
    text-align: center;
    margin-top: var(--spacing-xl);
    padding: var(--spacing-sm);
    background-color: var(--color-background-error);
    border: 1px solid var(--color-border-error);
    border-radius: 5px;
    color: var(--color-danger);
}

/* --- ESTILOS PARA LAS TARJETAS (CARDS) --- */
.option-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(288px, 1fr));
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.option-card {
    background-color: #fefefe;
    border: 1px solid var(--color-border-medium);
    border-radius: var(--spacing-sm);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.option-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.card-image {
    width: 100%;
    height: 160px;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f0f0f0;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.card-content {
    padding: 1em;
    flex-grow: 1;
}

.card-title {
    font-size: var(--font-size-h3);
    font-weight: bold;
    color: #333;
    margin-bottom: var(--spacing-sm);
}

.card-description {
    font-size: 1em;
    color: var(--color-text-light);
    margin-bottom: var(--spacing-md);
    line-height: 1.4;
    height: 2.75em;
    overflow: hidden;
    text-overflow: ellipsis;
    /* Añade puntos suspensvar(--spacing-sm)si se corta */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    /* Limita a 2 líneas */
    -webkit-box-orient: vertical;
}

.card-detail {
    font-size: var(--font-size-md);
    color: #444;
    margin-bottom: var(--spacing-sm);
}

.card-actions {
    display: flex;
    justify-content: space-around;
    padding: var(--spacing-md) var(--spacing-lg) var(--spacing-lg);
    border-top: 1px solid #eee;
    background-color: #f7f7f7;
}

.edit-button,
.delete-button {
    flex: 1;
    padding: var(--spacing-sm) var(--spacing-lg);
    margin: 0 var(--spacing-xs);
    border:var(--spacing-sm);
    border-radius: 5px;
    cursor: pointer;
    font-size: var(--font-size-md);
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease;
}

.edit-button {
    background-color: var(--color-primary);
    color: white;
}

.edit-button:hover {
    background-color: var(--color-primary-hover);
}

.delete-button {
    background-color: var(--color-danger);
    color: white;
}

.delete-button:hover {
    background-color: var(--color-danger-hover);
}
</style>