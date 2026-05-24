@echo off
cd /d "%~dp0"
echo Starting TuniCuisine at http://localhost:8000
call "%~dp0php.bat" -S localhost:8000 -t public
