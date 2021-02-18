@echo off

SET mypath=%~dp0
cd %mypath:~0,-1%
cd ..

echo(
echo Building new DIPAS release with current config and codebase
echo ===========================================================

rm dipasd8.zip >nul 2>&1

echo(
echo Running NPM build script
cmd /C npm run build >nul

echo(
echo Copying files
echo - Copying configuration files...
cp -R config dist
echo - Copying Drupal codebase...
cp -R drupal dist
echo - Copying vendor libraries...
cp -R vendor dist
echo - Copying needed directories...
cp -R private dist
mkdir dist\tmp
cp LICENSE.txt dist

echo(
echo Finalizing release
find dist -type d -iname .git -exec rm -rf {} ; >nul 2>&1
rm dist/drupal/sites/default/settings.local.php >nul 2>&1
rm dist/drupal/sites/default/services.local.yml >nul 2>&1
rm dist/drupal/.csslintrc >nul 2>&1
rm dist/drupal/.editorconfig >nul 2>&1
rm dist/drupal/.eslintignore >nul 2>&1
rm dist/drupal/.eslintrc.json >nul 2>&1

echo(
echo Creating ZIP archive
cd dist
..\scripts\zip.exe -r ..\dipasd8.zip . >nul
cd ..

echo(
echo Performing cleanup tasks
rm -rf dist

echo(
echo Build ready. Finished.
echo ===========================================================
echo(
