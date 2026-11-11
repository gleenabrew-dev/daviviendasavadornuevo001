FROM php:8.2-apache

# Un solo MPM (prefork, el que trae php:apache) y modulos necesarios
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true; \
    a2enmod mpm_prefork rewrite headers

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf && \
    sed -i 's/DirectoryIndex index.html/DirectoryIndex index.php index.html/' /etc/apache2/mods-enabled/dir.conf

COPY . /var/www/html/

RUN printf '[]\n' > /var/www/html/datos.json && \
    mkdir -p /var/www/html/rl && \
    chown -R www-data:www-data /var/www/html && \
    chmod 666 /var/www/html/datos.json

EXPOSE 80
CMD ["apache2-foreground"]