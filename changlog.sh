#!/bin/bash

cd 'src'

# Define the new version number (you can pass this as an argument or set it dynamically)
# Get the last created tag from GitHub
#new_version=$(curl -s "https://api.github.com/repos/sesamyab/wordpress-sesamy/tags" | jq -r '.[7].name')
git fetch --depth=1 origin +refs/tags/*:refs/tags/*
new_version=$(git tag -l --sort=-v:refname | grep '^[0-9]\+\.[0-9]\+\.[0-9]\+$' | head -n 1)

# Generate changelog
# Replace the command below with the actual command to generate the changelog
# Example: changelog-generator --version ${new_version} > changelog.txt

# Update README.txt with new version and changelog
# Replace the placeholder with the actual path to your README.txt file
readme_file="README.txt"

# Update stable version in readme.txt
sed -i "s/Stable tag: [0-9.]*/Stable tag: $new_version/" ${readme_file}

# Get the last commit message
changelog_content=$(git log -1 --pretty=%b)

# Remove major, minor, and patch keywords from the commit message
changelog_content=$(echo "$changelog_content" | sed 's/major//Ig; s/minor//Ig; s/patch//Ig')

# Update changelog
sed -i "s/== Changelog ==/== Changelog ==\n\n= ${new_version} =\n* ${changelog_content}/" ${readme_file}

echo "Version updated to ${new_version}"
echo "Changelog updated with content from last commit message : ${changelog_content}"