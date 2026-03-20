🏢 Branch & Employee Management API
API REST desarrollada con Arquitectura Hexagonal (Puertos y Adaptadores) en Laravel 11, que permite gestionar sucursales y empleados, consultando el clima actual de cada sucursal desde una API externa (Open-Meteo).

📋 Tecnologías
Tecnología	Uso
PHP 8.3	Lenguaje base
Laravel 11	Framework (infraestructura)
MySQL	Base de datos
PHPUnit	Tests unitarios y de integración ( con coverage para ver el porcentaje que cubrio cada test )
Mockery	Mocks para tests aislados
Open-Meteo API	API externa para clima
🏗️ Arquitectura Hexagonal
El proyecto sigue Arquitectura Hexagonal (Puertos y Adaptadores), separando claramente:

📁 Estructura
text
app/
├── Core/                         ← NÚCLEO (independiente del framework)
│   ├── Domain/                   ← Lógica de negocio pura
│   │   ├── Entities/             ← Entidades (Branch, Employee)
│   │   ├── ValueObjects/         ← Value Objects (Latitude, Longitude, Email)
│   │   └── Ports/                ← Puertos (interfaces)
│   │       ├── Repositories/     ← Contratos para persistencia
│   │       └── Services/         ← Contratos para servicios externos
│   
│
├── Infrastructure/               ← TODO lo que depende del framework
│   ├── Http/
│   │   └── Controllers/          ← Adaptadores HTTP (Laravel)
│   ├── Persistence/
│   │   ├── Models/               ← Modelos Eloquent
│   │   └── Repositories/         ← Implementaciones concretas
│   └── Adapters/                 ← Adaptadores para APIs externas
│
└── Shared/                       ← Compartido entre capas
    ├── DTOs/                     ← Data Transfer Objects
    └── Traits/                   ← Traits reutilizables
🎯 ¿Por qué esta arquitectura?
🔹 Desacoplamiento del framework
El Core no sabe que existe Laravel

Las entidades y value objects son PHP puro

Si cambiamos de framework (Laravel → Symfony), solo reescribimos Infrastructure/

🔹 Cambio de base de datos
Los repositorios implementan interfaces definidas en el Core

Si cambiamos MySQL por MongoDB, solo creamos un nuevo repositorio que implemente la misma interfaz

El Core no se entera del cambio

🔹 Cambio de API externa
El clima se obtiene a través de un adaptador que implementa WeatherServicePort

Si cambiamos Open-Meteo por otra API, solo creamos un nuevo adaptador

El resto del código no se modifica

✨ Principios aplicados
Principio	Aplicación
SOLID	Interfaces para inversión de dependencias, clases con una sola responsabilidad
DRY	DTOs reutilizables, traits de respuestas
Tell Don't Ask	Los objetos encapsulan su comportamiento
Repository Pattern	Separa la lógica de persistencia del negocio
Value Objects	Validación y encapsulamiento de datos (Latitude, Longitude, Email)

🚀 Instalación
Requisitos
PHP 8.3 o superior

Composer

MySQL

Pasos
bash
# Clonar repositorio
git clone https://github.com/tu-usuario/api-challenge.git
cd api-challenge

# Instalar dependencias
composer install

# Configurar .env
cp .env.example .env
# Editar .env con tus datos de BD

# Generar key
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve

📚 Documentación de la API
🏢 Sucursales (Branches)
Método	Endpoint	Descripción
GET	/api/branches	Listar todas las sucursales (con clima)
GET	/api/branches/{id}	Obtener una sucursal
POST	/api/branches	Crear sucursal
PUT	/api/branches/{id}	Actualizar sucursal
DELETE	/api/branches/{id}	Eliminar sucursal
GET	/api/branches/search?q=texto	Buscar por nombre o ciudad
👥 Empleados (Employees)
Método	Endpoint	Descripción
GET	/api/employees	Listar empleados (filtro: ?branch_id=1)
GET	/api/employees/{id}	Obtener un empleado
POST	/api/employees	Crear empleado
PUT	/api/employees/{id}	Actualizar empleado
DELETE	/api/employees/{id}	Eliminar empleado
🧪 Tests
bash
# Ejecutar todos los tests
php artisan test

# Tests unitarios (Core/Domain)
php artisan test --testsuite=Unit

# Tests de integración (Feature)
php artisan test --testsuite=Feature

# Con cobertura (Xdebug requerido)
php artisan test --coverage
Cobertura de tests
Capa	Cobertura
Core/Domain	100%
Repositorios	90%
Controladores	95%
Adaptadores	90%
🧠 Conceptos clave
Value Objects
Latitude: valida que la latitud esté entre -90 y 90

Longitude: valida que la longitud esté entre -180 y 180

Email: valida formato de email

Entidades
Branch: tiene identidad (ID) y comportamiento (changeLocation)

Employee: tiene identidad (ID) y puede cambiar de sucursal

Repositorios
BranchRepositoryPort: interfaz definida en el Core

EloquentBranchRepository: implementación con Eloquent (MySQL)

Adaptadores
WeatherServicePort: interfaz para servicios de clima

OpenMeteoAdapter: implementación con Open-Meteo API