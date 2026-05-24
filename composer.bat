@echo off
set PHP84=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
if exist "%PHP84%" (
  "%PHP84%" "%~dp0composer.phar" %*
) else (
  php "%~dp0composer.phar" %*
)
