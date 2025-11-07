# Makefile para Gestión Académica
# Comandos útiles para desarrollo y despliegue

.PHONY: help install start stop restart logs build shell db status clean test

# Comando por defecto
help: ## Mostrar ayuda
	@echo "🔧 Comandos disponibles para Gestión Académica:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Instalar y configurar el proyecto
	@echo "🚀 Instalando Gestión Académica..."
	@./install.sh

start: ## Iniciar servicios Docker
	@echo "🚀 Iniciando servicios..."
	@docker-compose up -d
	@echo "✅ Servicios iniciados"

stop: ## Detener servicios Docker
	@echo "🛑 Deteniendo servicios..."
	@docker-compose down
	@echo "✅ Servicios detenidos"

restart: ## Reiniciar servicios Docker
	@echo "🔄 Reiniciando servicios..."
	@docker-compose restart
	@echo "✅ Servicios reiniciados"

logs: ## Ver logs en tiempo real
	@echo "📋 Mostrando logs..."
	@docker-compose logs -f

build: ## Reconstruir contenedores
	@echo "🔨 Reconstruyendo contenedores..."
	@docker-compose up --build -d
	@echo "✅ Contenedores reconstruidos"

shell: ## Abrir shell en contenedor PHP
	@echo "🐚 Abriendo shell en contenedor PHP..."
	@docker-compose exec php bash

db: ## Conectar a PostgreSQL
	@echo "🗄️ Conectando a PostgreSQL..."
	@docker-compose exec postgres psql -U gestion_user -d gestion_academica

migrate: ## Ejecutar todas las migraciones pendientes
	@echo "🔄 Ejecutando migraciones..."
	@docker-compose exec php php run_migrations.php
	@echo "✅ Migraciones completadas"

migrate-password: ## Ejecutar migración de password_changed (deprecated, usar 'make migrate')
	@echo "🔄 Ejecutando migración de password_changed..."
	@docker-compose exec php php add_password_changed_column.php
	@echo "✅ Migración completada"

status: ## Ver estado de servicios
	@echo "📊 Estado de los servicios:"
	@docker-compose ps

clean: ## Limpiar contenedores y volúmenes
	@echo "🧹 Limpiando contenedores y volúmenes..."
	@docker-compose down -v
	@docker system prune -f
	@echo "✅ Limpieza completada"

test: ## Ejecutar tests (cuando estén implementados)
	@echo "🧪 Ejecutando tests..."
	@echo "⚠️  Tests no implementados aún"

backup: ## Crear backup de la base de datos
	@echo "💾 Creando backup de la base de datos..."
	@mkdir -p backups
	@docker-compose exec postgres pg_dump -U gestion_user gestion_academica > backups/backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup creado en directorio backups/"

restore: ## Restaurar backup de la base de datos (requiere BACKUP_FILE)
	@if [ -z "$(BACKUP_FILE)" ]; then \
		echo "❌ Especifica el archivo de backup: make restore BACKUP_FILE=backups/backup_20240101_120000.sql"; \
		exit 1; \
	fi
	@echo "🔄 Restaurando backup: $(BACKUP_FILE)"
	@docker-compose exec -T postgres psql -U gestion_user -d gestion_academica < $(BACKUP_FILE)
	@echo "✅ Backup restaurado"

dev: ## Modo desarrollo (iniciar con logs para comprobar errores)
	@echo "🔧 Iniciando en modo desarrollo..."
	@docker-compose up

prod: ## Modo producción (iniciar en background)
	@echo "🚀 Iniciando en modo producción..."
	@docker-compose up -d
