@echo off
cd /d "%~dp0"
call "%~dp0php.bat" bin/console %*
