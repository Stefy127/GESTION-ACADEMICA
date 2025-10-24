# Gestión Académica

Sistema de gestión académica desarrollado con PHP, PostgreSQL y Docker.

## Características

- ✅ Arquitectura MVC limpia
- ✅ Base de datos PostgreSQL
- ✅ Contenedores Docker
- ✅ PHP 8.2
- ✅ Bootstrap 5 para la interfaz
- ✅ Sistema de rutas personalizado

## Estructura del Proyecto

```
Gestion-Academica/
├── app/
│   ├── controllers/     # Controladores MVC
│   ├── models/         # Modelos MVC
│   └── views/          # Vistas MVC
├── config/             # Configuración de la aplicación
├── database/           # Scripts de base de datos
├── docker/             # Configuración de Docker
├── public/             # Archivos públicos (CSS, JS, imágenes)
└── docker-compose.yml  # Configuración de servicios
```

## 🚀 Instalación y Uso

### Requisitos del Sistema
- Docker >= 20.0
- Docker Compose >= 2.0
- Git >= 2.0

### Instalación Rápida

#### Opción 1: Script de instalación automática
```bash
# Clonar o descargar el proyecto
git clone <url-del-repositorio>
cd Gestion-Academica

# Ejecutar instalación automática
./install.sh
```

#### Opción 2: Instalación manual
```bash
# 1. Clonar el proyecto
git clone <url-del-repositorio>
cd Gestion-Academica

# 2. Configurar variables de entorno
cp .env.example .env

# 3. Iniciar servicios
docker-compose up -d

# 4. Verificar que todo funciona
docker-compose ps
```

### Acceso a la aplicación

- **Aplicación web**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Base de datos PostgreSQL**: localhost:5436

### Credenciales de la base de datos

- **Host**: localhost
- **Puerto**: 5436
- **Base de datos**: gestion_academica
- **Usuario**: gestion_user
- **Contraseña**: gestion_password

## Servicios Docker

- **PHP**: Servidor web con PHP 8.2 y Apache
- **PostgreSQL**: Base de datos PostgreSQL 15
- **phpMyAdmin**: Interfaz web para administrar la base de datos

## 📁 Archivos de Configuración

### Variables de Entorno
- **`.env.example`**: Plantilla con todas las variables de entorno
- **`.env`**: Archivo de configuración local (se crea automáticamente)

### Archivos de Configuración
- **`requirements.txt`**: Dependencias del sistema y PHP
- **`composer.json`**: Dependencias de PHP y autoloader
- **`docker-compose.yml`**: Configuración de servicios Docker
- **`docker/Dockerfile`**: Imagen personalizada de PHP
- **`docker/php.ini`**: Configuración de PHP
- **`config/app.php`**: Configuración de la aplicación

### Scripts de Automatización
- **`install.sh`**: Instalación automática del proyecto
- **`dev.sh`**: Comandos de desarrollo
- **`Makefile`**: Comandos avanzados con make

## 💻 Desarrollo

El proyecto está estructurado siguiendo el patrón MVC:

- **Modelos**: Manejan la lógica de datos y conexión a la base de datos
- **Vistas**: Contienen la presentación HTML
- **Controladores**: Manejan la lógica de la aplicación y coordinan modelos y vistas

## Base de Datos

El sistema incluye las siguientes tablas:

- `estudiantes`: Información de estudiantes
- `profesores`: Información de profesores
- `cursos`: Información de cursos
- `inscripciones`: Relación entre estudiantes y cursos

## 🔧 Comandos Útiles

### Scripts de Desarrollo

#### Script `dev.sh`
```bash
# Iniciar servicios
./dev.sh start

# Detener servicios
./dev.sh stop

# Ver logs en tiempo real
./dev.sh logs

# Abrir shell en contenedor PHP
./dev.sh shell

# Conectar a PostgreSQL
./dev.sh db

# Ver estado de servicios
./dev.sh status

# Limpiar contenedores y volúmenes
./dev.sh clean
```

#### Makefile (comandos avanzados)
```bash
# Ver todos los comandos disponibles
make help

# Instalación completa
make install

# Desarrollo (con logs)
make dev

# Producción (en background)
make prod

# Crear backup de BD
make backup

# Restaurar backup
make restore BACKUP_FILE=backups/backup_20240101_120000.sql
```

### Comandos Docker Compose Directos
```bash
# Iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Reconstruir contenedores
docker-compose up --build -d

# Ver estado de servicios
docker-compose ps

# Ejecutar comando en contenedor PHP
docker-compose exec php php -v

# Conectar a PostgreSQL
docker-compose exec postgres psql -U gestion_user -d gestion_academica
```
