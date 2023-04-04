FROM php:7.4 
# WordPress is still in beta for PHP8

# Setup the OS
RUN apt-get -qq update ; apt-get -y install unzip curl sudo subversion mariadb-client \
        && apt-get autoclean \
        && chsh -s /bin/bash www-data

# Add xdebug so we can debug tests (xdebug-3.1.5 is for php 7.4)
RUN pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug

# Add extensions
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN mkdir -m 777 /wordpress
WORKDIR /wordpress

# Install WP-CLI
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp && chmod +x /usr/local/bin/wp

# Download WP
RUN sudo -u www-data wp core download --locale=sv_SE

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create testing environment
COPY --chmod=755 test/install-wp-tests.sh /usr/local/bin/

# Run install script but skip db creation to have all files in the image for quick startup
RUN /usr/local/bin/install-wp-tests.sh sesamy_test root root db latest true

ENTRYPOINT [ "/usr/local/bin/entrypoint.sh" ]