# Imagen base con PHP y Apache
FROM php:8.2-apache

# Instalar extensiones y utilidades necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer (desde la imagen oficial)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite en Apache
RUN a2enmod rewrite

# Configurar Apache para usar el directorio public/
RUN echo '<VirtualHost *:8080>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Cambiar configuración del puerto a 8080 (Cloud Run usa $PORT)
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Establecer variable de entorno del puerto
ENV PORT=8080

# Copiar archivos de la aplicación
COPY --chown=www-data:www-data . /var/www/html/

# Establecer permisos para Apache
RUN chown -R www-data:www-data /var/www/html

# Directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto que Cloud Run espera
EXPOSE 8080

# Comando por defecto (Apache en primer plano)
CMD ["apache2-foreground"]
