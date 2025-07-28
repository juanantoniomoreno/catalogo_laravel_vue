<template>
	<div class="product-create-form">
		<h1>Crear Nuevo Producto</h1>

		<form @submit.prevent="createProduct" class="product-form">
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

			<button type="submit" :disabled="loading" class="submit-button">
				{{ loading ? 'Creando...' : 'Crear Producto' }}
			</button>
		</form>
	</div>
</template>

<script>
import { ref, reactive } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Importa useRouter para la redirección

export default {
	setup() {
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
			price: {
				price: 0.00,
				currency: 'EUR', // Valor por defecto
			},
		});

		const loading = ref(false);
		const errors = ref({}); // Para almacenar errores de validación
		const successMessage = ref('');
		const errorMessage = ref('');

		const createProduct = async () => {
			loading.value = true;
			errors.value = {}; // Limpiar errores anteriores
			successMessage.value = '';
			errorMessage.value = '';

			try {
				const response = await axios.post('/api/products/create', form);
				successMessage.value = 'Producto creado exitosamente!';
				console.log('Producto creado:', response.data);

				// Opcional: Limpiar el formulario después de un éxito
				form.main_image_url = '';
				form.status = 'inactive';
				form.type = 'simple';
				form.translations.es.name = '';
				form.translations.es.description = '';
				form.price = 0.00;

				// Redirigir al listado de productos después de un breve delay
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
					errorMessage.value = 'Ocurrió un error al crear el producto. Inténtalo de nuevo.';
				}
			} finally {
				loading.value = false;
			}
		};

		return {
			form,
			loading,
			errors,
			successMessage,
			errorMessage,
			createProduct,
		};
	},
};
</script>

<style scoped>
.product-create-form {
	max-width: 800px;
	margin: 20px auto;
	padding: 30px;
	background-color: #fff;
	border-radius: 8px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h1 {
	text-align: center;
	color: #333;
	margin-bottom: 30px;
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