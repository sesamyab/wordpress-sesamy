#!/bin/sh

cd 'src'

# Install composer

EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]
then
    >&2 echo 'ERROR: Invalid installer checksum'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php --quiet
RESULT=$?
rm composer-setup.php

# Install deps with composer (ignore deps on build system that would exist on acutal install)
php composer.phar update --ignore-platform-req=ext-mbstring

rm composer.phar

# Build gutenberg

cd 'admin/gutenberg/sesamy-post-editor'

npm install
npm run build

# Remove node modules
rm -rf ./node_modules

# Go back to root
cd '../../../..'

# Create version file
#package_json_version=$(npm run get-version -silent)
#echo "<?php\n\ndefine( 'SESAMY_VERSION', ${package_json_version} );" | tee src/version.php

# Zip package
mkdir dist
zip -r dist/sesamy-wordpress.zip src

