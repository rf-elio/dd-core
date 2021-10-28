#!/bin/bash

mkdir build
cp -r src/ build/src
cp -r tests/ build/tests
find . -type f -maxdepth 1 -exec cp {} build/ \;