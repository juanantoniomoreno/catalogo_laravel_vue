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
    margin: 1.25em auto;
    padding: 1.75em;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 1.5em;
    color: #333;
    margin-top: 1.75em;
    margin-bottom: 0.75em;
    border-bottom: 1px solid #eee;
    padding-bottom: 0.25em;
}

h3 {
    font-size: 1.2em;
    color: #555;
    margin-top: 1.25em;
    margin-bottom: 0.625em;
}

.option-form .form-group {
    margin-bottom: 1.25em;
}

.option-form label {
    display: block;
    margin-bottom: 0.5em;
    font-weight: bold;
    color: #555;
}

.option-form input[type="text"],
.option-form input[type="number"],
.option-form textarea,
.option-form select {
    width: 100%;
    padding: 0.5em;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1em;
    box-sizing: border-box;
}

/* Estilo para campos deshabilitados */
.option-form input:disabled,
.option-form textarea:disabled,
.option-form select:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    color: #6c757d;
}

.option-form textarea {
    resize: vertical;
    min-height: 80px;
}

.translation-group {
    border: 1px solid #e0e0e0;
    padding: 1.25em;
    margin-bottom: 1.25em;
    border-radius: 0.5em;
    background-color: #fcfcfc;
}

.add-translation-button,
.remove-translation-button {
    background-color: #007bff;
    color: white;
    padding: 0.5em 1em;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9em;
    margin-top: 0.5em;
    transition: background-color 0.3s ease;
}

.add-translation-button:hover {
    background-color: #0056b3;
}

.remove-translation-button {
    background-color: #dc3545;
    margin-left: 0.5em;
}

.remove-translation-button:hover {
    background-color: #c82333;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75em;
    margin-top: 1.75em;
}

.form-actions button,
.form-actions .cancel-button {
    padding: 0.75em 1.5em;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s ease, color 0.3s ease;
    text-decoration: none;
    text-align: center;
}

.form-actions button {
    background-color: #28a745;
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
    background-color: #6c757d;
    color: white;
    border: none;
}

.form-actions .cancel-button:hover {
    background-color: #5a6268;
}

.error-message {
    color: #dc3545;
    font-size: 0.8em;
    margin-top: 0.25em;
}

.general-error {
    text-align: center;
    margin-top: 1.25em;
    padding: 0.5em;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
}

.loading-products-message,
.error-products-message {
    font-size: 0.9em;
    margin-top: 0.25em;
    padding: 0.25em;
    border-radius: 3px;
}

.loading-products-message {
    color: #00796b;
    background-color: #e0f7fa;
}

.error-products-message {
    color: #721c24;
    background-color: #f8d7da;
}

.info-text {
    font-size: 0.85em;
    color: #888;
    margin-top: 0.25em;
}
</style>