FROM php:8.2-apache

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Включение mod_rewrite
RUN a2enmod rewrite

# Настройка Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Копирование файлов приложения
COPY . /var/www/html/

# Установка прав доступа
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/
RUN chmod -R 777 /var/www/html/uploads/

# Отображение ошибок для разработки
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

EXPOSE 80

CMD ["apache2-foreground"]
