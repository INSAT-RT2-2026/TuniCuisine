@echo off
setlocal
cd /d "%~dp0"

set "PHP84=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not exist "%PHP84%" (
  echo Installing PHP 8.4...
  winget install PHP.PHP.8.4 --accept-package-agreements --accept-source-agreements
  set "PHP84=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

if not exist "%PHP84%" (
  echo Could not find PHP 8.4. Install manually: winget install PHP.PHP.8.4
  pause
  exit /b 1
)

if not exist composer.phar (
  echo Downloading Composer...
  "%PHP84%" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  "%PHP84%" composer-setup.php --quiet
  del composer-setup.php
)

echo Using: 
"%PHP84%" -v
echo.

echo Enabling required PHP extensions (fileinfo, mbstring)...
"%PHP84%" bin/enable-php-extensions.php
echo.

echo Installing dependencies...
"%PHP84%" composer.phar install --no-interaction

echo Updating database...
"%PHP84%" bin/console doctrine:migrations:version "DoctrineMigrations\Version20260505201837" --add --no-interaction 2>nul
"%PHP84%" bin/console doctrine:migrations:migrate --no-interaction
if errorlevel 1 (
  echo Applying SQLite schema patch...
  "%PHP84%" bin/patch-sqlite-schema.php
  "%PHP84%" bin/console doctrine:migrations:version "DoctrineMigrations\Version20260524120000" --add --no-interaction
)
"%PHP84%" bin/console doctrine:fixtures:load --no-interaction

echo.
echo Done! Double-click run-server.bat or run: console.bat
pause
