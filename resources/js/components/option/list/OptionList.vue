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
    padding: 1.25em;
    max-width: 960px;
    margin: 0 auto;
}

.create-button {
    display: inline-block;
    background-color: #28a745;
    color: white;
    padding: 0.25em 1.25em;
    margin-right: 0.5em;
    margin-bottom: 1.25em;
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
    padding: 1.25em;
    font-size: 1.1em;
    color: #666;
}

.error-message.general-error {
    text-align: center;
    margin-top: 1.25em;
    padding: 0.5em;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    color: #dc3545;
}

/* --- ESTILOS PARA LAS TARJETAS (CARDS) --- */
.option-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(288px, 1fr));
    gap: 1.25em;
    margin-top: 1.25em;
}

.option-card {
    background-color: #fefefe;
    border: 1px solid #eee;
    border-radius: 0.5em;
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
    font-size: 1.25em;
    font-weight: bold;
    color: #333;
    margin-bottom: 0.5em;
}

.card-description {
    font-size: 1em;
    color: #666;
    margin-bottom: 0.8em;
    line-height: 1.4;
    height: 2.75em;
    overflow: hidden;
    text-overflow: ellipsis;
    /* Añade puntos suspensivos si se corta */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    /* Limita a 2 líneas */
    -webkit-box-orient: vertical;
}

.card-detail {
    font-size: 0.9em;
    color: #444;
    margin-bottom: 0.4em;
}

.card-detail strong {
    color: #222;
}

.card-actions {
    display: flex;
    justify-content: space-around;
    padding: 0.8em 1em 1em;
    border-top: 1px solid #eee;
    background-color: #f7f7f7;
}

.edit-button,
.delete-button {
    flex: 1;
    padding: 0.5em 1em;
    margin: 0 0.25em;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    text-decoration: none;
    text-align: center;
    transition: background-color 0.3s ease;
}

.edit-button {
    background-color: #007bff;
    color: white;
}

.edit-button:hover {
    background-color: #0056b3;
}

.delete-button {
    background-color: #dc3545;
    color: white;
}

.delete-button:hover {
    background-color: #c82333;
}
</style>