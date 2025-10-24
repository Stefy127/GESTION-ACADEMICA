#!/bin/bash

# Script de instalación para Gestión Académica
# Este script configura el entorno de desarrollo

echo "🚀 Instalando Gestión Académica..."

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker no está instalado. Por favor instala Docker primero."
    echo "   Visita: https://docs.docker.com/get-docker/"
    exit 1
fi

# Verificar si Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose no está instalado. Por favor instala Docker Compose primero."
    echo "   Visita: https://docs.docker.com/compose/install/"
    exit 1
fi

echo "✅ Docker y Docker Compose están instalados"

# Crear archivo .env si no existe
if [ ! -f .env ]; then
    echo "📝 Creando archivo .env desde .env.example..."
    cp .env.example .env
    echo "✅ Archivo .env creado"
else
    echo "✅ Archivo .env ya existe"
fi

# Crear directorio de logs si no existe
if [ ! -d logs ]; then
    echo "📁 Creando directorio de logs..."
    mkdir -p logs
    echo "✅ Directorio de logs creado"
fi

# Construir y ejecutar contenedores
echo "🐳 Construyendo contenedores Docker..."
docker-compose build

echo "🚀 Iniciando servicios..."
docker-compose up -d

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 10

# Verificar que los servicios estén funcionando
echo "🔍 Verificando servicios..."

if docker-compose ps | grep -q "Up"; then
    echo "✅ Servicios iniciados correctamente"
    echo ""
    echo "🌐 Aplicación disponible en: http://localhost:8080"
    echo "🗄️  phpMyAdmin disponible en: http://localhost:8081"
    echo "🐘 PostgreSQL disponible en: localhost:5432"
    echo ""
    echo "📋 Credenciales de la base de datos:"
    echo "   Host: localhost"
    echo "   Puerto: 5432"
    echo "   Base de datos: gestion_academica"
    echo "   Usuario: gestion_user"
    echo "   Contraseña: gestion_password"
    echo ""
    echo "🎉 ¡Instalación completada!"
else
    echo "❌ Error al iniciar los servicios"
    echo "📋 Revisa los logs con: docker-compose logs"
    exit 1
fi
