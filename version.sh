#!/bin/bash

cd 'src'

# Define the new version number (you can pass this as an argument or set it dynamically)
# Get the last created tag from GitHub
latest_tag=$(curl -s "https://api.github.com/repos/sesamyab/wordpress-sesamy/tags" | jq -r '.[7].name')

# Parse the tag into major, minor, and patch components
major=$(echo $latest_tag | cut -d. -f1)
minor=$(echo $latest_tag | cut -d. -f2)
patch=$(echo $latest_tag | cut -d. -f3)

# Increment the patch version
((patch++))

# Get the last commit message
commit_message=$(git log -1 --pretty=%b)

# Check if the commit message contains keywords and adjust version accordingly
if [[ $commit_message == *"major"* ]]; then
    major=$((major + 1))
elif [[ $commit_message == *"minor"* ]]; then
    minor=$((minor + 1))
elif [[ $commit_message == *"patch"* ]]; then
    patch=$((patch + 1))
fi

# Construct the new version tag
new_version="$major.$minor.$patch"

# Update version in sesamy.php
sed -i "s/define( 'SESAMY_VERSION', '[0-9.]*' );/define( 'SESAMY_VERSION', '$new_version' );/" sesamy.php

# Update version in sesamy.php
sed -i "s/^\s*\*\s*Version:\s*[0-9.]*\s*$/ * Version:           $new_version/" sesamy.php

echo "Version updated to ${new_version}"

