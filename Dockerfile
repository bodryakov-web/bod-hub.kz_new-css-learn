# Dockerfile для NewCSSLearn
# Базовый образ: PHP 8.2 с Apache

FROM php:8.2-apache

# Установка необходимых расширений PHP
# mysqli - для работы с MySQL базой данных
# pdo pdo_mysql - для работы с базой данных через PDO
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Включение mod_rewrite для Apache (необходимо для URL роутинга)
RUN a2enmod rewrite

# Установка прав на директорию uploads для загрузки изображений
RUN mkdir -p /var/www/html/uploads/lessons && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Копирование конфигурации Apache для включения .htaccess
RUN echo "AllowOverride All" > /etc/apache2/sites-available/000-default.conf

# Установка рабочей директории
WORKDIR /var/www/html
