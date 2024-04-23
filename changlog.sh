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