# Guía de Despliegue - Google Cloud Platform

## Pre-requisitos

1. **Instalar Google Cloud SDK**:
   ```bash
   # En Linux/Mac
   curl https://sdk.cloud.google.com | bash
   exec -l $SHELL
   
   # O descargar desde: https://cloud.google.com/sdk/docs/install
   ```

2. **Verificar instalación**:
   ```bash
   gcloud --version
   ```

## Paso 1: Configurar la Cuenta de Google Cloud

```bash
# Iniciar sesión en GCP
gcloud auth login

# Configurar el proyecto (reemplaza PROJECT_ID con tu ID de proyecto)
gcloud config set project PROJECT_ID

# Configurar el compilador por defecto
gcloud config set compute/region us-central1
gcloud config set compute/zone us-central1-a
```

## Paso 2: Habilitar APIs Necesarias

```bash
# Habilitar Cloud Build API
gcloud services enable cloudbuild.googleapis.com

# Habilitar Container Registry API
gcloud services enable containerregistry.googleapis.com

# Habilitar Cloud Run API (si usas Cloud Run)
gcloud services enable run.googleapis.com

# Habilitar Cloud SQL Admin API
gcloud services enable sqladmin.googleapis.com
```

## Paso 3: Preparar el Proyecto para GCP

### 3.1 Archivo `.dockerignore`

Ya está creado en el proyecto. Incluye exclusiones para:
- Git y documentación
- Archivos de entorno (.env)
- Logs
- Configuraciones de IDE
- Archivos de sistema

### 3.2 Dockerfile

Ya existe en `docker/Dockerfile` (usado por docker-compose)

### 3.3 Archivo `cloudbuild.yaml`

Ya está creado en el proyecto. Configurado para:
- Build automático de imagen con hash de commit
- Push a Container Registry con múltiples tags
- Despliegue automático en Cloud Run
- Configuración de variables de entorno
- Conexión a Cloud SQL mediante Unix socket

## Paso 4: Configurar Cloud SQL (PostgreSQL)

```bash
# Crear instancia de Cloud SQL PostgreSQL
gcloud sql instances create gestion-academica-db \
    --database-version=POSTGRES_15 \
    --tier=db-f1-micro \
    --region=us-central1 \
    --backup \
    --enable-bin-log

# Crear la base de datos
gcloud sql databases create gestion_academica --instance=gestion-academica-db

# Crear usuario
gcloud sql users create app_user \
    --instance=gestion-academica-db \
    --password=TÚ_PASSWORD_SEGURO
```

## Paso 5: Construir y Subir la Imagen

```bash
# Construir la imagen
docker build -t gcr.io/PROJECT_ID/gestion-academica:latest -f docker/Dockerfile .

# Autenticarse en Google Container Registry
gcloud auth configure-docker

# Push de la imagen
docker push gcr.io/PROJECT_ID/gestion-academica:latest
```

## Paso 6: Desplegar en Cloud Run

```bash
# Desplegar el servicio
gcloud run deploy gestion-academica \
    --image gcr.io/PROJECT_ID/gestion-academica:latest \
    --platform managed \
    --region us-central1 \
    --allow-unauthenticated \
    --add-cloudsql-instances PROJECT_ID:us-central1:gestion-academica-db \
    --set-env-vars DB_HOST=/cloudsql/PROJECT_ID:us-central1:gestion-academica-db,DB_NAME=gestion_academica,DB_USER=app_user,DB_PASSWORD=TÚ_PASSWORD_SEGURO
```

## Paso 7: Configurar Variables de Entorno

```bash
# Configurar variables de entorno en Cloud Run
gcloud run services update gestion-academica \
    --region us-central1 \
    --set-env-vars \
    DB_HOST=/cloudsql/PROJECT_ID:us-central1:gestion-academica-db,\
    DB_NAME=gestion_academica,\
    DB_USER=app_user,\
    DB_PASSWORD=TÚ_PASSWORD_SEGURO,\
    DB_PORT=5432
```

## Paso 8: Inicializar la Base de Datos

```bash
# Obtener la URL del servicio desplegado
gcloud run services describe gestion-academica --region us-central1 --format 'value(status.url)'

# Ejecutar el script de inicialización
# Opción 1: Desde tu máquina local
gcloud sql connect gestion-academica-db --user=app_user
# Luego ejecuta: \i database/init.sql

# Opción 2: Via Cloud SQL Proxy
# Descargar Cloud SQL Proxy
# Conectar y ejecutar init.sql
```

## Paso 9: Configurar Dominio Personalizado (Opcional)

```bash
# Mapear dominio personalizado
gcloud run domain-mappings create \
    --service gestion-academica \
    --domain tudominio.com \
    --region us-central1
```

## Paso 10: Verificar el Despliegue

```bash
# Obtener la URL del servicio
SERVICE_URL=$(gcloud run services describe gestion-academica --region us-central1 --format 'value(status.url)')
echo "Tu aplicación está disponible en: $SERVICE_URL"

# Abrir en el navegador
# (En Linux)
xdg-open $SERVICE_URL
```

## Comandos Útiles

```bash
# Ver logs del servicio
gcloud run services logs read gestion-academica --region us-central1

# Actualizar el servicio
gcloud run deploy gestion-academica \
    --image gcr.io/PROJECT_ID/gestion-academica:latest \
    --region us-central1

# Ver estado del servicio
gcloud run services describe gestion-academica --region us-central1

# Eliminar el servicio
gcloud run services delete gestion-academica --region us-central1

# Conectar a Cloud SQL
gcloud sql connect gestion-academica-db --user=app_user
```

## Costos Estimados

- **Cloud SQL (db-f1-micro)**: ~$7-10/mes
- **Cloud Run**: Primeros 2 millones de requests gratuitos, luego $0.40 por millón
- **Container Registry**: Almacenamiento limitado (primeros 0.5GB gratuitos)
- **Tráfico de red**: Primeros 1GB gratuitos

## Alternativa: Usar Cloud Build para CI/CD

```bash
# Subir código y configurar Cloud Build
git init
git add .
git commit -m "Initial commit"

# Crear trigger en Cloud Build
gcloud builds submit --config cloudbuild.yaml
```

## Notas Importantes

1. **Reemplaza PROJECT_ID** con tu ID de proyecto de GCP
2. **Reemplaza TÚ_PASSWORD_SEGURO** con una contraseña segura
3. **Cloud SQL Proxy**: Si tienes problemas de conexión, usa Cloud SQL Proxy
4. **Variables de entorno**: Ajusta según tu configuración
5. **Backups**: Cloud SQL tiene backups automáticos, configura según necesites

## Troubleshooting

```bash
# Ver logs en tiempo real
gcloud run services logs tail gestion-academica --region us-central1

# Ejecutar comando en el contenedor
gcloud run services update gestion-academica --region us-central1 --exec

# Revisar configuración
gcloud run services describe gestion-academica --region us-central1
```
