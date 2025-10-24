#!/bin/bash

# Script de desarrollo para Gestión Académica
# Comandos útiles para el desarrollo

case "$1" in
    "start")
        echo "🚀 Iniciando servicios..."
        docker-compose up -d
        echo "✅ Servicios iniciados"
        ;;
    "stop")
        echo "🛑 Deteniendo servicios..."
        docker-compose down
        echo "✅ Servicios detenidos"
        ;;
    "restart")
        echo "🔄 Reiniciando servicios..."
        docker-compose restart
        echo "✅ Servicios reiniciados"
        ;;
    "logs")
        echo "📋 Mostrando logs..."
        docker-compose logs -f
        ;;
    "build")
        echo "🔨 Reconstruyendo contenedores..."
        docker-compose up --build -d
        echo "✅ Contenedores reconstruidos"
        ;;
    "shell")
        echo "🐚 Abriendo shell en contenedor PHP..."
        docker-compose exec php bash
        ;;
    "db")
        echo "🗄️ Conectando a PostgreSQL..."
        docker-compose exec postgres psql -U gestion_user -d gestion_academica
        ;;
    "status")
        echo "📊 Estado de los servicios:"
        docker-compose ps
        ;;
    "clean")
        echo "🧹 Limpiando contenedores y volúmenes..."
        docker-compose down -v
        docker system prune -f
        echo "✅ Limpieza completada"
        ;;
    *)
        echo "🔧 Comandos disponibles:"
        echo "  start    - Iniciar servicios"
        echo "  stop     - Detener servicios"
        echo "  restart  - Reiniciar servicios"
        echo "  logs     - Ver logs en tiempo real"
        echo "  build    - Reconstruir contenedores"
        echo "  shell    - Abrir shell en contenedor PHP"
        echo "  db       - Conectar a PostgreSQL"
        echo "  status   - Ver estado de servicios"
        echo "  clean    - Limpiar contenedores y volúmenes"
        echo ""
        echo "Uso: ./dev.sh [comando]"
        ;;
esac
