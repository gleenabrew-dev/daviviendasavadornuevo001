FROM php:8.2-cli

COPY . /var/www/html/
WORKDIR /var/www/html

RUN printf '[]\n' > datos.json

EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
