
# 🚀 Administración de Productos, Ofertas y Opciones

Este proyecto es una aplicación web que combina un **backend robusto desarrollado con Laravel** (PHP) y un **frontend dinámico construido con Vue.js 3** (JavaScript), utilizando Vite para un desarrollo rápido y eficiente. Está diseñado para la gestión de productos, ofertas y opciones, incluyendo soporte para traducciones en múltiples idiomas.

La aplicación permite:

  * Gestionar productos con diferentes tipos (simples, grupos de opciones, packs), estados e imágenes.
  * Administrar ofertas especiales vinculadas a productos.
  * Controlar opciones específicas para productos de tipo 'grupo de opción', también con soporte para traducciones.
  * Todo ello a través de una API RESTful en el backend y una interfaz de usuario intuitiva en el frontend.

-----

## 💻 Requisitos del Sistema

Antes de empezar, asegúrate de tener instalado lo siguiente en tu sistema:

  * **PHP**: Versión 8.2 o superior (la que uses para Laravel).
  * **Composer**: Gestor de dependencias de PHP.
  * **Node.js**: Versión 18 o superior (LTS recomendado).
  * **npm** o **Yarn**: Gestor de paquetes de Node.js (se recomienda Yarn para este proyecto).
  * **Base de Datos**: MySQL, PostgreSQL, SQLite, etc. (MySQL es común para Laravel).
  * **Servidor Web (para producción)**: Apache con `mod_rewrite` habilitado o Nginx. Para desarrollo local, `php artisan serve` y Vite son suficientes.

-----

## ⚙️ Configuración del Proyecto

Sigue estos pasos para poner el proyecto en marcha en tu entorno local:

### 1\. Clonar el Repositorio

Primero, clona el repositorio a tu máquina local:

```bash
git clone <url_del_repositorio>
cd <nombre_del_directorio_del_proyecto>
```

### 2\. Configuración del Backend (Laravel)

#### a. Instalación de Dependencias de PHP

Instala las dependencias de Laravel usando Composer:

```bash
composer install
```

#### b. Configuración del Entorno (`.env`)

Copia el archivo de configuración de ejemplo y genera una clave de aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

Abre el archivo `.env` y configura tu conexión a la base de datos (DB\_DATABASE, DB\_USERNAME, DB\_PASSWORD, etc.):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario_db
DB_PASSWORD=tu_password_db
```

#### c. Ejecutar Migraciones y Seeds (Opcional)

Ejecuta las migraciones para crear las tablas en tu base de datos. Si tienes seeders para datos de prueba, también puedes ejecutarlos:

```bash
php artisan migrate
php artisan db:seed # Opcional: Si tienes seeders para datos de prueba
```

### 3\. Configuración del Frontend (Vue.js + Vite)

#### a. Instalación de Dependencias de JavaScript

Instala las dependencias de Node.js usando Yarn (recomendado) o npm:

```bash
yarn install
# o si usas npm
# npm install
```

#### b. Construcción de Assets (Opcional, para producción)

Para construir los assets de frontend para producción:

```bash
yarn build
# o
# npm run build
```

-----

## ▶️ Levantar el Proyecto en Local

Para tener la aplicación funcionando en tu entorno de desarrollo, necesitarás ejecutar dos comandos simultáneamente: uno para el backend de Laravel y otro para el frontend de Vue/Vite.

### 1\. Iniciar el Servidor de Laravel (Backend)

En una terminal, ve al directorio raíz del proyecto y ejecuta:

```bash
php artisan serve
```

Esto iniciará el servidor de desarrollo de Laravel, generalmente en `http://127.0.0.1:8000`.

### 2\. Iniciar el Servidor de Desarrollo de Vite (Frontend)

En **otra terminal**, ve al directorio raíz del proyecto y ejecuta:

```bash
yarn dev
# o si usas npm
# npm run dev
```

Esto iniciará el servidor de desarrollo de Vite para tu frontend, generalmente en `http://localhost:5173`.

### 3\. Acceder a la Aplicación

Una vez que ambos servidores estén ejecutándose, abre tu navegador web y navega a la URL del servidor de Laravel:

```
http://127.0.0.1:8000
```

Tu aplicación Vue.js se cargará a través de la vista Blade servida por Laravel, y Vite se encargará del Hot Module Replacement (HMR) para los cambios en el frontend.

**Nota sobre los errores 404 al recargar:** Si al recargar una página del frontend (ej. `http://127.0.0.1:8000/products`) obtienes un error 404, es un comportamiento esperado en este entorno de desarrollo. Para solucionarlo, tu `routes/web.php` de Laravel ya incluye una **ruta "catch-all"** (`Route::get('/{any}', ...)->where('any', '.*');`) que redirige todas las peticiones no coincidentes a la vista principal de tu SPA (`welcome.blade.php`), permitiendo a Vue Router manejar la navegación. Asegúrate de que esta ruta esté al final de tu `routes/web.php`.

-----

## 📂 Estructura del Proyecto (Relevante)

  * `app/Models/`: Modelos de Eloquent (`Product`, `Offer`, `Option`, `ProductTranslation`, `OptionTranslation`).
  * `app/Http/Controllers/Api/`: Controladores API para los recursos (`ProductController`, `OfferController`, `OptionController`).
  * `app/Http/Requests/`: Form Requests para la validación de peticiones (`StoreProductRequest`, `UpdateProductRequest`, etc.).
  * `database/migrations/`: Archivos de migración para la base de datos.
  * `resources/js/`: Código fuente de tu aplicación Vue.js.
      * `resources/js/app.js`: Punto de entrada principal de Vue.
      * `resources/js/router/index.js`: Definición de rutas de Vue Router.
      * `resources/js/components/`: Componentes Vue.js (Navbar, ProductList, ProductForm, OfferList, OfferForm, OptionList, OptionForm).
  * `routes/api.php`: Rutas de la API RESTful de Laravel.
  * `routes/web.php`: Rutas web de Laravel, incluyendo la ruta "catch-all" para el frontend.
  * `public/images/`: (Asegúrate de tener aquí `default_product.png` y `default_option.png` si los utilizas en los componentes).

-----

## 🛠️ Comandos Útiles

  * **Migrar base de datos:** `php artisan migrate`
  * **Generar nueva migración:** `php artisan make:migration create_xxx_table`
  * **Generar modelo:** `php artisan make:model Xxx`
  * **Generar controlador API:** `php artisan make:controller Api/XxxController --api --model=Xxx`
  * **Generar Form Request:** `php artisan make:request StoreXxxRequest` y `php artisan make:request UpdateXxxRequest`
  * **Iniciar servidor Laravel:** `php artisan serve`
  * **Iniciar servidor de desarrollo Vite:** `yarn dev` (o `npm run dev`)
  * **Compilar assets para producción:** `yarn build` (o `npm run build`)

-----