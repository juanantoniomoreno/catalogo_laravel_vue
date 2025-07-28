Gestor de Catálogo de Licencias Digitales

Este proyecto Laravel implementa un sistema para la gestión de un catálogo de licencias de productos digitales, incluyendo soporte multi-idioma, gestión de precios, opciones de productos, packs y ofertas.

🚀 Características Principales
Gestión de Productos: Define productos base con su estado (activo/inactivo) y tipo (simple, grupo_opciones, pack).

Soporte Multi-idioma (i18n): Nombres, descripciones y slugs de productos y opciones disponibles en múltiples idiomas.

Imágenes por URL: Almacenamiento eficiente de imágenes (principal y galería) mediante URLs, delegando el almacenamiento de archivos al sistema de ficheros o CDN.

Precios Flexibles: Precios base para productos y precios específicos para cada opción.

Opciones de Producto: Permite definir variantes para un producto principal (ej., diferentes versiones de un software).

Packs de Productos: Crea paquetes que agrupan múltiples productos individuales con cantidades específicas.

Gestión de Ofertas: Define ofertas con precios especiales y rangos de fechas para productos o packs.

No uso de Enums PHP 8.1+: Los estados y tipos se gestionan mediante cadenas de texto y constantes en los modelos, garantizando compatibilidad.

🛠️ Requisitos del Sistema
Asegúrate de tener instalado lo siguiente:

PHP: >= 8.1 (aunque el código no usa Enums, versiones recientes de Laravel los requieren).

Composer: Gestor de paquetes de PHP.

Node.js & npm/Yarn: Para la gestión de dependencias frontend (Vue.js).

Base de Datos: MySQL (recomendado) u otra base de datos soportada por Laravel (PostgreSQL, SQLite, SQL Server).

⚙️ Instalación y Configuración
Sigue estos pasos para poner el proyecto en marcha en tu entorno local:

Clonar el repositorio:

git clone https://your-repository-url.com
cd your-project-name

Instalar dependencias de Composer:
composer install

Configurar el archivo .env:

Copia el archivo de ejemplo:
cp .env.example .env

Genera una clave de aplicación:
php artisan key:generate
Abre el archivo .env y configura tus credenciales de base de datos (DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).

Fragmento de código

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario_db
DB_PASSWORD=tu_password_db

Ejecutar migraciones de la base de datos:
Esto creará todas las tablas necesarias:

php artisan migrate
Si quieres una base de datos limpia y poblarla con datos de prueba (si tienes seeders):

php artisan migrate:fresh --seed

Configurar el enlace simbólico de almacenamiento:

php artisan storage:link

Instalar dependencias de Node.js y compilar assets (si usas frontend con Vue.js):

npm install
npm run dev # Para desarrollo
# o
npm run build # Para producción
Iniciar el servidor de desarrollo de Laravel:

Bash

php artisan serve
El proyecto estará accesible en http://127.0.0.1:8000 (o el puerto indicado).

📂 Estructura de la Base de Datos
Aquí se detalla la estructura de las tablas principales y sus relaciones:

products: Productos base.

product_translations: Nombres, descripciones y slugs de productos por locale. (Relación 1:N con products)

product_images: URLs de imágenes de galería de productos. (Relación 1:N con products)

product_prices: Precio base de un producto. (Relación 1:1 con products)

options: Variantes de un producto principal. (Relación 1:N con products)

option_translations: Nombres y descripciones de opciones por locale. (Relación 1:N con options)

option_prices: Precio específico de una opción. (Relación 1:1 con options)

pack_products: Tabla pivote para packs, relacionando un product de tipo 'pack' con otros products que son sus ítems. (Relación M:N entre products y products)

offers: Detalles de ofertas aplicables a productos. (Relación 1:N con products)

Puedes visualizar el Diagrama Entidad-Relación (DER) / Relacional en formato Mermaid:

Fragmento de código

erDiagram
    products {
        int id PK
        string main_image_url "URL de la imagen principal"
        string status "('active', 'inactive')"
        string type "('simple', 'option_group', 'pack')"
        timestamp created_at
        timestamp updated_at
    }

    product_translations {
        int id PK
        int product_id FK
        string locale "('es', 'it', 'fr', 'en', 'pt')"
        string name
        text description
        string slug
        timestamp created_at
        timestamp updated_at
    }

    product_images {
        int id PK
        int product_id FK
        string image_url "URL de la imagen de galería"
        int order
        timestamp created_at
        timestamp updated_at
    }

    product_prices {
        int id PK
        int product_id FK
        decimal price
        string currency
        timestamp created_at
        timestamp updated_at
    }

    options {
        int id PK
        int product_id FK "parent_product"
        string image_url "URL de la imagen de la opción"
        string status "('active', 'inactive')"
        timestamp created_at
        timestamp updated_at
    }

    option_translations {
        int id PK
        int option_id FK
        string locale
        string name
        text description
        timestamp created_at
        timestamp updated_at
    }

    option_prices {
        int id PK
        int option_id FK
        decimal price
        timestamp created_at
        timestamp updated_at
    }

    pack_products {
        int pack_id FK "FK_products_pack"
        int product_id FK "FK_products_item"
        int quantity
    }

    offers {
        int id PK
        int product_id FK "applies_to_product_or_option_or_pack"
        decimal offer_price
        datetime start_date
        datetime end_date
        string status "('active', 'inactive', 'scheduled', 'expired')"
        timestamp created_at
        timestamp updated_at
    }

    products ||--o{ product_translations : has
    products ||--o{ product_images : has
    products ||--o{ product_prices : has
    products ||--o{ options : has_options
    options ||--o{ option_translations : has
    options ||--o{ option_prices : has
    products }o--o{ pack_products : contains_products_in_pack
    products ||--o{ offers : has_offers
👩‍💻 Uso y Desarrollo
Modelos Eloquent: Los modelos se encuentran en app/Models/ y definen las relaciones de Eloquent para interactuar con la base de datos.

Controladores: Crea controladores en app/Http/Controllers/ para manejar la lógica de negocio y las operaciones CRUD.

Rutas API: Define las rutas para tu API en routes/api.php para interactuar con el frontend.

Gestión de Imágenes: Implementa la lógica para subir imágenes a storage/app/public y guardar sus URLs en la base de datos.

Frontend (Vue.js): Utiliza Vue I18n para la gestión de traducciones en el lado del cliente. Asegúrate de que el frontend consuma las traducciones desde las tablas _translations del backend.

🤝 Contribuciones
Las contribuciones son bienvenidas. Por favor, abre un "issue" para discutir cambios propuestos o envía un "pull request" con tus mejoras.

📄 Licencia
Este proyecto está bajo la licencia MIT License.