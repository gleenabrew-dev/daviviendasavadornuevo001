FROM php:8.2-apache

RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
RUN ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
RUN ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
RUN a2enmod rewrite headers

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
RUN sed -i 's/DirectoryIndex index.html/DirectoryIndex index.php index.html/' /etc/apache2/mods-enabled/dir.conf

COPY . /var/www/html/

RUN printf '[]\n' > /var/www/html/datos.json
RUN mkdir -p /var/www/html/rl
RUN chown -R www-data:www-data /var/www/html
RUN chmod 666 /var/www/html/datos.json

RUN apache2ctl configtest

EXPOSE 80
CMD ["apache2-foreground"]