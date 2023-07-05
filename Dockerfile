FROM wordpress:latest

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Uncomment for production/demo run

#COPY ./src/ /var/www/html/wp-content/plugins/sesamy/
#COPY ./demo/themes/twentytwentytwo-child/ /var/www/html/wp-content/themes/twentytwentytwo-child/

#RUN chown www-data:www-data -R /var/www/html/wp-content/plugins/sesamy/*
#RUN chown www-data:www-data -R /var/www/html/wp-content/themes/twentytwentytwo-child*


ARG PLUGIN_NAME=sesamy


# Install wp-cli
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp-cli.phar \
        && echo "#!/bin/bash" > /usr/local/bin/wp-cli \
        && echo "su www-data -c \"/usr/local/bin/wp-cli.phar --path=/var/www/html \$*\"" >> /usr/local/bin/wp-cli \
        && chmod 755 /usr/local/bin/wp-cli* \
        && echo "*** wp-cli command installed"

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
        && php composer-setup.php \
        && php -r "unlink('composer-setup.php');" \
        && mv composer.phar /usr/local/bin/ \
        && echo "#!/bin/bash" > /usr/local/bin/composer \
        && echo "su www-data -c \"/usr/local/bin/composer.phar --working-dir=/var/www/html/wp-content/plugins/${PLUGIN_NAME} \$*\"" >> /usr/local/bin/composer \
        && chmod ugo+x /usr/local/bin/composer \
        && echo "*** composer command installed"


EXPOSE 80