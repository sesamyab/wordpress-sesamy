#!/bin/bash

cd 'src'

# Define the new version number (you can pass this as an argument or set it dynamically)
# Get the last created tag from GitHub
#new_version=$(curl -s "https://api.github.com/repos/sesamyab/wordpress-sesamy/tags" | jq -r '.[7].name')
#git fetch --depth=1 origin +refs/tags/*:refs/tags/*
#new_version=$(git tag -l --sort=-v:refname | grep '^[0-9]\+\.[0-9]\+\.[0-9]\+$' | head -n 1)
new_version=$1

# Update version in sesamy.php
sed -i "s/define( 'SESAMY_VERSION', '[0-9.]*' );/define( 'SESAMY_VERSION', '$new_version' );/" sesamy.php

# Update version in sesamy.php
sed -i "s/^\s*\*\s*Version:\s*[0-9.]*\s*$/ * Version:           $new_version/" sesamy.php

echo "Version updated to ${new_version}"

