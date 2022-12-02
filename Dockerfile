FROM wordpress:latest

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Uncomment for production/demo run

#COPY ./src/ /var/www/html/wp-content/plugins/sesamy/
#COPY ./demo/themes/twentytwentytwo-child/ /var/www/html/wp-content/themes/twentytwentytwo-child/

#RUN chown www-data:www-data -R /var/www/html/wp-content/plugins/sesamy/*
#RUN chown www-data:www-data -R /var/www/html/wp-content/themes/twentytwentytwo-child*

EXPOSE 80