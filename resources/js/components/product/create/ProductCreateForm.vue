<template>
	<div class="product-create-form">
		<h1>{{isEditing ? 'Editar Producto' : 'Crear Nuevo Producto'}}</h1>

		<div v-if="loadingInitialData" class="loading-message">Cargando datos del producto...</div>
    	<div v-if="fetchError" class="error-message">{{ fetchError }}</div>

		<form v-if="!loadingInitialData && !fetchError" @submit.prevent="submitForm" class="product-form">
			<div v-if="successMessage" class="success-message">
				{{ successMessage }}
			</div>
			<div v-if="errorMessage" class="error-message">
				{{ errorMessage }}
			</div>

			<fieldset class="form-section">
				<legend>Datos Generales</legend>

				<div class="form-group">
					<label for="main_image_url">URL de Imagen Principal:</label>
					<input type="text" id="main_image_url" v-model="form.main_image_url" class="form-control">
					<span v-if="errors.main_image_url" class="error-text">{{ errors.main_image_url[0] }}</span>
				</div>

				<div class="form-group">
					<label for="status">Estado:</label>
					<select id="status" v-model="form.status" class="form-control">
						<option value="active">Activo</option>
						<option value="inactive">Inactivo</option>
					</select>
					<span v-if="errors.status" class="error-text">{{ errors.status[0] }}</span>
				</div>

				<div class="form-group">
					<label for="type">Tipo:</label>
					<select id="type" v-model="form.type" class="form-control">
						<option value="simple">Producto</option>
						<option value="pack">Pack</option>
						<option value="option_group">Grupo</option>
					</select>
					<span v-if="errors.type" class="error-text">{{ errors.type[0] }}</span>
				</div>
			</fieldset>

			<fieldset class="form-section">
				<legend>Traducción (Español)</legend>

				<div class="form-group">
					<label for="name_es">Nombre del Producto (ES):</label>
					<input type="text" id="name_es" v-model="form.translations.es.name" class="form-control">
					<span v-if="errors['translations.es.name']" class="error-text">{{ errors['translations.es.name'][0]
						}}</span>
				</div>

				<div class="form-group">
					<label for="description_es">Descripción (ES):</label>
					<textarea id="description_es" v-model="form.translations.es.description" rows="4"
						class="form-control"></textarea>
					<span v-if="errors['translations.es.description']" class="error-text">{{
						errors['translations.es.description'][0] }}</span>
				</div>
			</fieldset>

			<fieldset class="form-section">
				<legend>Precio</legend>

				<div class="form-group">
					<label for="price_value">Precio:</label>
					<input type="number" id="price_value" v-model.number="form.price" step="0.01" class="form-control">
					<span v-if="errors['price']" class="error-text">{{ errors['price'][0] }}</span>
				</div>
			</fieldset>

			<button type="submit" :disabled="isSubmitting" class="submit-button">
				{{ isSubmitting ? (isEditing ? 'Actualizando...' : 'Creando...') : (isEditing ? 'Actualizar Producto' : 'Crear Producto') }}
			</button>
		</form>
	</div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Importa useRouter para la redirección

export default {
	props: {
		productId: {
			type: [String, Number, null],
			default: null
		}
	},
	setup(props) {
		const router = useRouter(); // Instancia del router

		const form = reactive({
			main_image_url: '',
			status: 'draft', // Valor por defecto
			type: 'digital',   // Valor por defecto
			translations: {
				es: {
					name: '',
					description: '',
					slug: '',
				},
			},
			price: 0.00
		});

		const loadingInitialData = ref(false);
		const isSubmitting = ref(false);      
		const errors = ref({});
		const successMessage = ref('');
		const errorMessage = ref('');
		const fetchError = ref(null);

		const isEditing = computed(() => Boolean(props.productId));

		// Función para limpiar el formulario
		const resetForm = () => {
			form.main_image_url = '';
			form.status = 'inactive';
			form.type = 'simple';
			form.translations.es.name = '';
			form.translations.es.description = '';			
			form.price = 0.00;			
			errors.value = {};
			successMessage.value = '';
			errorMessage.value = '';
			fetchError.value = null;
		};

		// Función para cargar los datos del producto existente (solo en modo edición)
		const fetchProductData = async (id) => {
			loadingInitialData.value = true;
			fetchError.value = null;

			try {
				const response = await axios.get(`/api/products/${id}`);
				const productData = response.data;
				
				form.main_image_url = productData.main_image_url || '';
				form.status = productData.status || 'inactive'
				form.type = productData.type || 'simple';
				
				form.translations.es.name = productData.name || ''; // Accedemos a la propiedad 'name' ya traducida
				form.translations.es.description = productData.description || '';
				form.translations.es.slug = productData.slug || '';

				if (productData.price) {
					form.price = productData.price;					
				} else {
					form.price = 0.00; 					
				}
			} catch (err) {
				fetchError.value = 'Error al cargar los datos del producto. No existe o no tienes permiso.';
				console.error('Error fetching product for edit:', err);
			} finally {
				loadingInitialData.value = false;
			}
		};

		const submitForm = async () => {
			isSubmitting.value = true;
			errors.value = {};
			successMessage.value = '';
			errorMessage.value = '';

			try {
				let response;
				if (isEditing.value) {
					// Si estamos editando
					response = await axios.put(`/api/products/${props.productId}`, form);
          			successMessage.value = 'Producto actualizado exitosamente!';
				} else {
					response = await axios.post('/api/products/create', form);
					successMessage.value = 'Producto creado exitosamente!';
				}
				// console.log('Producto creado:', response.data);				

				// Redirigir al listado de productos
				setTimeout(() => {
					router.push('/');
				}, 1500);

			} catch (error) {
				console.error('Error al crear el producto:', error);
				if (error.response && error.response.status === 422) {
					// Errores de validación de Laravel
					errors.value = error.response.data.errors;
					errorMessage.value = 'Por favor, corrige los errores del formulario.';
				} else if (error.response && error.response.status === 403) {
					// Error de autorización (Forbidden)
					errorMessage.value = error.response.data.message || 'No tienes permiso para realizar esta acción.';
				}
				else {
					errorMessage.value = `Ocurrió un error al ${isEditing.value ? 'actualizar' : 'crear'} el producto. Inténtalo de nuevo.`;
				}
			} finally {
				isSubmitting.value = false;
			}
		};

		// Cuando el componente se inicia o el productId cambia
		onMounted(() => {
			if (isEditing.value) {
				fetchProductData(props.productId);
			} else {				
				resetForm(); // Limpiar el formulario si estamos en modo creación
			}
		});

		// Observa si el productId de la ruta cambia
		watch(() => props.productId, (newId) => {
			if (newId) { 
				fetchProductData(newId);
			} else { // Si el ID es null (pasamos a modo creación)
				resetForm();
			}
		});

		return {
			form,
			isEditing,
			loadingInitialData,
			isSubmitting,
			errors,
			successMessage,
			errorMessage,
			fetchError,
			submitForm,
		};
	},
};
</script>

<style scoped>
.product-create-form {
	max-width: 800px;
	margin: 1.25em auto;
	padding: 1.75empx;
	background-color: #fff;
	border-radius: 0.5em;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h1 {
	text-align: center;
	color: #333;
	margin-bottom: 1.75em;
	font-size: 2em;
}

.product-form {
	display: flex;
	flex-direction: column;
	gap: 25px;
}

.form-section {
	border: 1px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 20px;
	background-color: #f9f9f9;
}

.form-section legend {
	font-size: 1.3em;
	font-weight: bold;
	color: #555;
	padding: 0 10px;
	margin-left: -10px;
	background-color: #f9f9f9;
	border-radius: 4px;
}

.form-group {
	margin-bottom: 15px;
}

.form-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: bold;
	color: #444;
}

.form-control {
	width: calc(100% - 20px);
	/* Ajuste para padding */
	padding: 10px;
	border: 1px solid #ccc;
	border-radius: 5px;
	font-size: 1em;
	box-sizing: border-box;
	/* Incluye padding y border en el ancho total */
}

textarea.form-control {
	resize: vertical;
	/* Permite redimensionar verticalmente */
	min-height: 80px;
}

.form-control:focus {
	border-color: #007bff;
	box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
	outline: none;
}

.error-text {
	color: #dc3545;
	font-size: 0.85em;
	margin-top: 5px;
	display: block;
}

.submit-button {
	background-color: #28a745;
	color: white;
	padding: 12px 25px;
	border: none;
	border-radius: 5px;
	font-size: 1.1em;
	cursor: pointer;
	transition: background-color 0.3s ease, opacity 0.3s ease;
	align-self: center;
	/* Centra el botón */
	width: auto;
	/* Ancho automático */
	min-width: 180px;
}

.submit-button:hover:not(:disabled) {
	background-color: #218838;
}

.submit-button:disabled {
	background-color: #94d3a2;
	cursor: not-allowed;
	opacity: 0.7;
}

.success-message {
	background-color: #d4edda;
	color: #155724;
	border: 1px solid #c3e6cb;
	padding: 12px;
	border-radius: 5px;
	margin-bottom: 20px;
	text-align: center;
}

.error-message {
	background-color: #f8d7da;
	color: #721c24;
	border: 1px solid #f5c6cb;
	padding: 12px;
	border-radius: 5px;
	margin-bottom: 20px;
	text-align: center;
}
</style>