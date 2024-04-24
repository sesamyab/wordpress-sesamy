#!/bin/bash

cd 'src'

# Define the new version number (you can pass this as an argument or set it dynamically)
# Get the last created tag from GitHub
new_version=$(curl -s "https://api.github.com/repos/sesamyab/wordpress-sesamy/tags" | jq -r '.[7].name')


# Update version in sesamy.php
sed -i "s/define( 'SESAMY_VERSION', '[0-9.]*' );/define( 'SESAMY_VERSION', '$new_version' );/" sesamy.php

# Update version in sesamy.php
sed -i "s/^\s*\*\s*Version:\s*[0-9.]*\s*$/ * Version:           $new_version/" sesamy.php

echo "Version updated to ${new_version}"

