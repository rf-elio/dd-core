#!/bin/bash

mkdir build
mkdir prepare
cp -r src/ prepare/src
cp -r tests/ prepare/tests
find . -type f -maxdepth 1 -exec cp {} prepare/ \;