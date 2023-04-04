#!/bin/bash
set -e

# Assume we're running in correct workdir set by Dockerfile

# Install wp tests and phpunit
/usr/local/bin/install-wp-tests.sh sesamy_test root root db latest

# Installing wp
rm -f wp-config.php
sudo -u www-data wp config create --dbhost=db:3306 --dbname=sesamy_test --dbuser=root --dbpass=root --locale=sv_SE >> /dev/null
sudo -u www-data wp core install --url=localhost:8001 --title="Sesamy Test" --admin_name=test --admin_password=test --admin_email=test@sesamy.com >> /dev/null

echo "Running phpunit..."

# Run phpunit with CMD as arg, make sure to be in sesamy folder to get phpunit config files
cd ./wp-content/plugins/sesamy/
./vendor/bin/phpunit "$@"