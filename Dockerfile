# Usa una imagen oficial de PHP con Apache
FROM php:8.1-apache

# Actualiza el repositorio y luego instala Vim
RUN apt-get update && apt-get install -y vim

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# Copiar configuración personalizada de Apache
COPY docker_files/apache/api_task_master.conf /etc/apache2/sites-available/

# Deshabilitar configuración por defecto y habilitar la nueva
RUN a2dissite 000-default.conf && a2ensite api_task_master.conf


# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos del proyecto
COPY . task_master_api

# Entrar a la carpeta del proyecto
WORKDIR /var/www/html/task_master_api

# Instalar dependencias de Laravel
RUN composer install --no-dev --no-interaction --prefer-dist


# Da permisos de escritura al almacenamiento de Laravel
RUN chown -R www-data:www-data /var/www/html/task_master_api/storage
# Expone el puerto 80
EXPOSE 80

# Ejecutar migraciones y luego iniciar Apache
CMD php artisan key:generate && php artisan migrate:fresh --seed  --force && apache2-foreground