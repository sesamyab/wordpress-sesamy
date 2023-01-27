#!/bin/sh

# Build
#./build.sh


# Create dist relase

rm -rf ./dist
mkdir dist

cp -r ./src/* ./dist/

# Update version stamp in dist