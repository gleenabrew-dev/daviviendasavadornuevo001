FROM php:8.2-apache
RUN a2enmod rewrite headers \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
 && sed -i 's/DirectoryIndex index.html/DirectoryIndex index.php index.html/' /etc/apache2/mods-enabled/dir.conf
COPY . /var/www/html/
RUN echo "[]" > /var/www/html/datos.json \
 && chown -R www-data:www-data /var/www/html \
 && chmod 666 /var/www/html/datos.json
EXPOSE 80
