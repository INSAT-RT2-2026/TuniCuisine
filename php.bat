@echo off
setlocal
set "PHP84=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if exist "%PHP84%" (
  "%PHP84%" %*
  exit /b %ERRORLEVEL%
)

echo.
echo ERROR: PHP 8.4 is required, but the project PHP was not found.
echo Windows is using PHP 8.2 from XAMPP when you type "php" — that is too old for Symfony 8.
echo.
echo Install PHP 8.4 once:
echo   winget install PHP.PHP.8.4
echo.
echo Then use these scripts instead of plain "php":
echo   run-server.bat
echo   console.bat doctrine:migrations:migrate
echo.
exit /b 1
