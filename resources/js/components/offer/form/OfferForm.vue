<template>
    <div class="offer-form-container">
        <h1>{{ offerId ? 'Editar Oferta' : 'Crear Nueva Oferta' }}</h1>

        <form @submit.prevent="submitForm" class="offer-form">
            <div class="form-group">
                <label for="price">Precio de la Oferta (€):</label>
                <input type="number" id="price" v-model.number="form.offer_price" step="0.01" required />
                <span v-if="errors.offer_price" class="error-message">{{ errors.offer_price[0] }}</span>
            </div>

            <div class="form-group">
                <label for="start_date">Fecha de Inicio:</label>
                <input type="date" id="start_date" v-model="form.start_date" required :min="today"/>
                <span v-if="errors.start_date" class="error-message">{{ errors.start_date[0] }}</span>
            </div>

            <div class="form-group">
                <label for="end_date">Fecha de Fin:</label>
                <input type="date" id="end_date" v-model="form.end_date" required :min="today"/>
                <span v-if="errors.end_date" class="error-message">{{ errors.end_date[0] }}</span>
            </div>

            <div class="form-group">
                <label for="status">Estado:</label>
                <select id="status" v-model="form.status" required>
                    <option value="active">Activa</option>
                    <option value="inactive">Inactiva</option>
                    <option value="scheduled">Programada</option>
                </select>
                <span v-if="errors.status" class="error-message">{{ errors.status[0] }}</span>
            </div>

            <div class="form-group" v-if="!offerId">
                <label for="product_id">Producto Asociado (solo productos activos):</label>
                <select id="product_id" v-model="form.product_id" required>
                    <option value="">Selecciona un producto</option>
                    <option v-for="product in simpleProducts" :key="product.id" :value="product.id">
                        {{ product.name }} (Precio: {{ product.price }})
                    </option>
                </select>
                <span v-if="errors.product_id" class="error-message">{{ errors.product_id[0] }}</span>
                <div v-if="loadingProducts" class="loading-products-message">Cargando productos...</div>
                <div v-if="errorProducts" class="error-products-message">{{ errorProducts }}</div>
            </div>
            <div class="form-group" v-else>
                <label>Producto Asociado:</label>
                <p><strong>{{ currentProductName || 'Cargando...' }}</strong></p>
                <p class="info-text">El producto asociado no se puede cambiar en modo edición.</p>
            </div>

            <div class="form-actions">
                <button type="submit" :disabled="loadingSubmit">
                    {{ loadingSubmit ? 'Guardando...' : (offerId ? 'Actualizar Oferta' : 'Crear Oferta') }}
                </button>
                <router-link to="/offers" class="cancel-button">Cancelar</router-link>
            </div>

            <div v-if="generalError" class="error-message general-error">{{ generalError }}</div>
        </form>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

import { formatToInputDate, getTodayDate } from '../../../utils/date';

export default {
    name: 'OfferForm',
    props: {
        offerId: {
            type: [String, Number],
            default: null,
        },
    },
    setup(props) {
        const router = useRouter();
        const form = reactive({
            offer_price: 0.00,
            start_date: '',
            end_date: '',
            status: 'inactive',
            product_id: '',
        });

        const errors = ref({});
        const generalError = ref(null);
        const loadingSubmit = ref(false);

        const simpleProducts = ref([]);
        const loadingProducts = ref(true);
        const errorProducts = ref(null);
        const currentProductName = ref('');
        const today = ref('');        

        // Fetch active products for the dropdown
        const fetchSimpleProducts = async () => {
            loadingProducts.value = true;
            errorProducts.value = null;

            try {
                const response = await axios.get('/api/products/filtered', {
                    params: {
                        type: 'simple'
                    }
                });
                simpleProducts.value = response.data;
            } catch (err) {
                errorProducts.value = 'Error fetching simple products.';
                console.error('Error fetching simple products:', err);
            } finally {
                loadingProducts.value = false;
            }
        };

        const fetchOffer = async (id) => {
            if (!id) return;

            loadingSubmit.value = true;
            generalError.value = null;

            try {
                const response = await axios.get(`/api/offers/${id}`);
                const offerData = response.data;

                // Populate form with fetched data                
                form.offer_price = offerData.offer_price;
                form.start_date = formatToInputDate(offerData.start_date);
                form.end_date = formatToInputDate(offerData.end_date);
                form.status = offerData.status;
                form.product_id = offerData.product_id;

                currentProductName.value = offerData.product_name;
            } catch (err) {
                console.error('Error fetching offer:', err);
                generalError.value = 'Error al cargar los datos de la oferta para edición.';
            } finally {
                loadingSubmit.value = false;
            }
        };

        const submitForm = async () => {
            loadingSubmit.value = true;
            errors.value = {};
            generalError.value = null;

            try {
                if (props.offerId) {
                    const response = await axios.put(`/api/offers/${props.offerId}`, form);
                    alert('Oferta actualizada exitosamente.');
                } else {
                    const response = await axios.post('/api/offers', form);
                    alert('Oferta creada exitosamente.');
                }
                router.push('/offers');
            } catch (err) {
                console.error('Error submitting form:', err);
                if (err.response && err.response.status === 422) {
                    errors.value = err.response.data.errors;
                } else {
                    generalError.value = 'Ha ocurrido un error al procesar tu solicitud. Por favor, inténtalo de nuevo.';
                }
            } finally {
                loadingSubmit.value = false;
            }
        };

        onMounted(() => {
            today.value = getTodayDate();
            if (!props.offerId) {
                fetchSimpleProducts();
            }
        });

        // Watch for offerId changes to refetch data in edit mode
        watch(() => props.offerId, (newId) => {
            if (newId) {
                fetchOffer(newId);
            }
        }, { immediate: true });

        return {
            form,
            errors,
            generalError,
            loadingSubmit,
            simpleProducts,
            loadingProducts,
            errorProducts,
            currentProductName,
            submitForm,
            offerId: props.offerId,
            today
        };
    },
};
</script>

<style scoped>
.offer-form-container {
    max-width: 600px;
    margin: var(--spacing-xl) auto;
    padding: var(--spacing-xl);
    background-color: var(--color-background-light);
    border-radius: 8px;
    box-shadow: var(--box-shadow-base);
}

h1 {
    text-align: center;
    color: var(--color-text-dark);
    margin-bottom: var(--spacing-xxl);
    font-size: 2em;
}

.offer-form .form-group {
    margin-bottom: var(--spacing-xl);
}

.offer-form label {
    display: block;
    margin-bottom: var(--spacing-sm);
    font-weight: bold;
    color: var(--color-text-medium);
}

.offer-form input[type="text"],
.offer-form input[type="number"],
.offer-form input[type="date"],
.offer-form textarea,
.offer-form select {
    width: 100%;
    padding: var(--spacing-sm);
    border: 1px solid var(--color-border-light);
    border-radius: 5px;
    font-size: var(--font-size-base);
    box-sizing: border-box;
}

.offer-form textarea {
    resize: vertical;
    min-height: 5em;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1em;
    margin-top: var(--spacing-xxl);
}

.form-actions button,
.form-actions .cancel-button {
    padding: var(--spacing-md) var(--spacing-xl);
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    text-decoration: none;
    text-align: center;
}

.form-actions button {
    background-color: var(--color-success);
    color: white;
    border: none;
}

.form-actions button:hover:not(:disabled) {
    background-color: var(--color-success-hover);
}

.form-actions button:disabled {
    background-color: #94d3a2;
    cursor: not-allowed;
}

.form-actions .cancel-button {
    background-color: var(--color-secondary);
    color: white;
    border: none;
}

.form-actions .cancel-button:hover {
    background-color: #5a6268;
}

.error-message {
    color: var(--color-danger);
    font-size: var(--font-size-sm);
    margin-top: var(--spacing-xs);
}

.general-error {
    text-align: center;
    margin-top: var(--spacing-xl);
    padding: var(--spacing-sm);
    background-color: var(--color-background-error);
    border: 1px solid var(--color-border-error);
    border-radius: 5px;
}

.loading-products-message,
.error-products-message {
    font-size: var(--font-size-md);
    margin-top: var(--spacing-xs);
    padding: var(--spacing-xs);
    border-radius: 3px;
}

.loading-products-message {
    color: var(--color-text-info);
    background-color: var(--color-background-info);
}

.error-products-message {
    color: #721c24;
    background-color: var(--color-background-error);
}

.info-text {
    font-size: var(--font-size-sm);
    color: var(--color-text-lighter);
    margin-top: var(--spacing-xs);
}
</style>