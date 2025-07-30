<template>
    <div class="option-form-container">
        <h1>{{ optionId ? 'Editar Opción' : 'Crear Nueva Opción' }}</h1>

        <form @submit.prevent="submitForm" class="option-form">
            <div class="form-group">
                <label for="product_id">Producto Asociado:</label>
                <select id="product_id" v-model="form.product_id" :disabled="!!optionId || loadingProducts" required>
                    <option value="">Selecciona un producto</option>
                    <option v-for="product in optionGroupProducts" :key="product.id" :value="product.id">
                        {{ product.name }}
                    </option>
                </select>
                <span v-if="errors.product_id" class="error-message">{{ errors.product_id[0] }}</span>
                <div v-if="loadingProducts" class="loading-products-message">Cargando productos...</div>
                <div v-if="errorProducts" class="error-products-message">{{ errorProducts }}</div>
                <div v-if="optionId" class="info-text">
                    El producto asociado no se puede cambiar en modo edición.
                </div>
            </div>

            <div class="form-group">
                <label for="image_url">URL de la Imagen:</label>
                <input type="text" id="image_url" v-model="form.image_url" />
                <span v-if="errors.image_url" class="error-message">{{ errors.image_url[0] }}</span>
            </div>

            <div class="form-group">
                <label for="price">Precio de la Opción (€):</label>
                <input type="number" id="price" v-model.number="form.price" step="0.01" required />
                <span v-if="errors.price" class="error-message">{{ errors.price[0] }}</span>
            </div>

            <div class="form-group">
                <label for="status">Estado:</label>
                <select id="status" v-model="form.status" required>
                    <option value="active">Activa</option>
                    <option value="inactive">Inactiva</option>
                </select>
                <span v-if="errors.status" class="error-message">{{ errors.status[0] }}</span>
            </div>

            <div v-for="(translation, index) in form.translations" :key="index" class="translation-group">
                <div class="form-group">
                    <label :for="`name-${index}`">Nombre de la Opción:</label>
                    <input type="text" :id="`name-${index}`" v-model="translation.name" required />
                    <span v-if="errors[`translations.${index}.name`]" class="error-message">{{
                        errors[`translations.${index}.name`][0] }}</span>
                </div>
                <div class="form-group">
                    <label :for="`description-${index}`">Descripción:</label>
                    <textarea :id="`description-${index}`" v-model="translation.description"></textarea>
                    <span v-if="errors[`translations.${index}.description`]" class="error-message">{{
                        errors[`translations.${index}.description`][0] }}</span>
                </div>
            </div>
            <span v-if="errors.translations" class="error-message">{{ errors.translations[0] }}</span>

            <div class="form-actions">
                <button type="submit" :disabled="loadingSubmit">
                    {{ loadingSubmit ? 'Guardando...' : (optionId ? 'Actualizar Opción' : 'Crear Opción') }}
                </button>
                <router-link to="/options" class="cancel-button">Cancelar</router-link>
            </div>

            <div v-if="generalError" class="error-message general-error">{{ generalError }}</div>
        </form>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
    name: 'OptionForm',
    props: {
        optionId: {
            type: [String, Number],
            default: null,
        },
    },
    setup(props) {
        const router = useRouter();
        const form = reactive({
            product_id: '',
            image_url: '',
            status: 'active', // Default status for new options
            price: null,
            translations: [
                { locale: 'es', name: '', description: '' }
            ],
        });

        const errors = ref({});
        const generalError = ref(null);
        const loadingSubmit = ref(false);

        const optionGroupProducts = ref([]);
        const loadingProducts = ref(true);
        const errorProducts = ref(null);

        const fetchOptionGroupProducts = async () => {
            loadingProducts.value = true;
            errorProducts.value = null;
            try {
                const response = await axios.get('/api/products/filtered', {
                    params: { type: 'simple', status: 'active' }
                });
                optionGroupProducts.value = response.data;
            } catch (err) {
                errorProducts.value = 'Error al cargar los productos de grupo de opción.';
                console.error('Error fetching option group products:', err);
            } finally {
                loadingProducts.value = false;
            }
        };

        const fetchOption = async (id) => {
            if (!id) return;

            loadingSubmit.value = true;
            generalError.value = null;
            try {
                const response = await axios.get(`/api/options/${id}`);
                const optionData = response.data;

                form.product_id = optionData.product_id;
                form.image_url = optionData.image_url || '';
                form.status = optionData.status;
                form.price = parseFloat(optionData.price); // Ensure price is a number

                // Populate translations
                if (optionData.translations && optionData.translations.length > 0) {
                    form.translations = optionData.translations.map(t => ({
                        locale: t.locale,
                        name: t.name,
                        description: t.description || ''
                    }));
                } else {
                    // If no translations exist, ensure default empty translation structure
                    form.translations = [{ locale: 'es', name: '', description: '' }];
                }

            } catch (err) {
                console.error('Error fetching option:', err);
                generalError.value = 'Error al cargar los datos de la opción para edición.';
            } finally {
                loadingSubmit.value = false;
            }
        };

        const submitForm = async () => {
            loadingSubmit.value = true;
            errors.value = {};
            generalError.value = null;

            try {
                if (props.optionId) {
                    // Update existing option
                    // Send only the fields that are allowed to be updated based on API (excluding product_id if not changed in API)
                    // For now, we allow sending all relevant fields as 'sometimes' is used in backend
                    await axios.put(`/api/options/${props.optionId}`, form);
                    alert('Opción actualizada exitosamente.');
                } else {
                    // Create new option
                    await axios.post('/api/options', form);
                    alert('Opción creada exitosamente.');
                }
                router.push('/options');
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
            fetchOptionGroupProducts(); // Always fetch products for selection/display
        });

        watch(() => props.optionId, (newId) => {
            if (newId) {
                fetchOption(newId);
            }
        }, { immediate: true });

        return {
            form,
            errors,
            generalError,
            loadingSubmit,
            optionGroupProducts,
            loadingProducts,
            errorProducts,
            optionId: props.optionId,
            submitForm,
        };
    },
};
</script>

<style scoped>
.option-form-container {
    max-width: 600px;
    margin: var(--spacing-xl) auto;
    padding: var(--spacing-xxl);
    background-color: var(--color-background-light);
    border-radius: 8px;
    box-shadow: var(--box-shadow-base);
}

h2 {
    font-size: var(--font-size-h2);
    color: var(--color-text-dark);
    margin-top: var(--spacing-xxl);
    margin-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--color-border-medium);
    padding-bottom: var(--spacing-xs);
}

h3 {
    font-size: var(--font-size-h3);
    color: var(--color-text-medium);
    margin-top: var(--spacing-xl);
    margin-bottom: var(--spacing-sm);
}

.option-form .form-group {
    margin-bottom: var(--spacing-xl);
}

.option-form label {
    display: block;
    margin-bottom: var(--spacing-sm);
    font-weight: bold;
    color: var(--color-text-medium);
}

.option-form input[type="text"],
.option-form input[type="number"],
.option-form textarea,
.option-form select {
    width: 100%;
    padding: var(--spacing-sm);
    border: 1px solid var(--color-border-light);
    border-radius: 5px;
    font-size: var(--font-size-base);
    box-sizing: border-box;
}

/* Estilo para campos deshabilitados */
.option-form input:disabled,
.option-form textarea:disabled,
.option-form select:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    color: var(--color-secondary);
}

.option-form textarea {
    resize: vertical;
    min-height: 80px;
}

.translation-group {
    border: 1px solid #e0e0e0;
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    border-radius: var(--spacing-sm);
    background-color: #fcfcfc;
}

.add-translation-button,
.remove-translation-button {
    background-color: var(--color-primary);
    color: white;
    padding: var(--spacing-sm) var(--spacing-lg);
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: var(--font-size-md);
    margin-top: var(--spacing-sm);
    transition: background-color 0.3s ease;
}

.add-translation-button:hover {
    background-color: var(--color-primary-hover);
}

.remove-translation-button {
    background-color: var(--color-danger);
    margin-left: var(--spacing-xs);
}

.remove-translation-button:hover {
    background-color: var(--color-danger-hover);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xs);
}

.form-actions button,
.form-actions .cancel-button {
    padding: var(--spacing-md);
    border-radius: 8px;
    font-size: var(--font-size-base);
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
    background-color: #218838;
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
    font-size: 0.9em;
    margin-top: var(--spacing-xs);
    padding: var(--spacing-xs);
    border-radius: 3px;
}

.loading-products-message {
    color: #00796b;
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