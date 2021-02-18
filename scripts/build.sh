#!/usr/bin/env bash

ZIP_DIR="tmp_zip"
ZIP_NAME="dipasd8.zip"

echo "Check for prevouis build"
if [ -f $ZIP_NAME ]; then
  echo "Remove old build"
  rm $ZIP_NAME
fi

if [ -d $ZIP_DIR ]; then
  echo "Remove old tmp dir"
  rm -rf $ZIP_DIR
fi


echo "Build JS Sources"
npm install
if [ -f src/config.js ]; then
  cp src/example.config.js src/config.js
fi
npm run build


echo "Create tmp dir for bundling"
mkdir $ZIP_DIR

echo "Copy JS dist"
cp -R dist/* $ZIP_DIR/

echo "Copy Config files"
cp -R config $ZIP_DIR
echo "Copy Drupal files"
rsync -r --stats --human-readable --exclude="drupal/sites/default/files/*" --exclude="drupal/sites/default/*.local.php" drupal $ZIP_DIR
echo "Copy vendor files"
cp -R vendor $ZIP_DIR

echo "Set GIT SHA"
git rev-parse HEAD > $ZIP_DIR/COMMIT.txt

cd $ZIP_DIR
echo "ZIP it"
zip -rqy ../$ZIP_NAME ./*

