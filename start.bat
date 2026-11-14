@echo off
setlocal EnableExtensions EnableDelayedExpansion
title KYAF Food Donation - Web Server
cd /d "%~dp0"
where php >nul 2>nul
if errorlevel 1 (
  set "FOUNDPHP="
  for /d %%D in ("C:\wamp64\bin\php\php8*") do set "FOUNDPHP=%%D"
  if defined FOUNDPHP (
    set "PATH=!FOUNDPHP!;!PATH!"
    echo PHP found in WAMP: !FOUNDPHP!
  ) else (
    echo [ERROR] PHP was not found. Start WAMP and make sure PHP 8.2 or newer is installed in C:\wamp64\bin\php
    pause
    exit /b 1
  )
)
php -r "exit(version_compare(PHP_VERSION,'8.2.0','<') ? 1 : 0);"
if errorlevel 1 (
  echo [ERROR] PHP 8.2 or newer is required. Your PHP version is:
  php -v
  echo In WAMP: tray icon - PHP - Version - choose 8.2 or newer.
  pause
  exit /b 1
)

if not exist "vendor\autoload.php" (
  echo [ERROR] Packages are not installed yet. Run setup.bat first.
  pause
  exit /b 1
)
php artisan optimize:clear >nul
echo.
echo ==================================================
echo   KYAF is starting...  Open this address in your browser:
echo.
echo        http://127.0.0.1:8000
echo.
echo   Keep this window open. Press CTRL+C to stop the server.
echo ==================================================
echo.
start "KYAF scheduler (expires old donations)" /min cmd /c "php artisan schedule:work"
php artisan serve --host=127.0.0.1 --port=8000
endlocal
