FROM php:8.4-apache

# Render tells the app which port to use through $PORT (defaults to 80 locally)
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
 && echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Keep saved answers private and hide the server version
RUN printf '<Directory /var/www/html/data>\n  Require all denied\n</Directory>\nServerTokens Prod\nServerSignature Off\n' \
      > /etc/apache2/conf-available/date-app.conf \
 && a2enconf date-app

COPY . /var/www/html/
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data
