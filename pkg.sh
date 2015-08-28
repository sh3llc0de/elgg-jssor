#!/bin/bash

PKG_PATH=/tmp/jssor.out
VER_MAJOR=1
VER_MINOR=0
VER_REV=$(cat version.txt)

mkdir -p ${PKG_PATH}
git archive --format=tar HEAD | tar Cxf "${PKG_PATH}" -
sed -i -e "s/VER_MAJOR/${VER_MAJOR}/g;s/VER_MINOR/${VER_MINOR}/g;s/VER_REV/${VER_REV}/g" ${PKG_PATH}/manifest.xml

(($VER_REV++))
echo "${VER_REV}" > version.txt
git add version.txt
git commit -m "pkg tag ${VER_MAJOR}.${VER_MINOR}.${VER_REV}"
git tag "${VER_MAJOR}.${VER_MINOR}.${VER_REV}"
#git push --all
