@echo off
setlocal EnableExtensions EnableDelayedExpansion
title KYAF Food Donation - Setup
cd /d "%~dp0"
echo ==================================================
echo   KYAF Food Donation Management System - SETUP
echo   (safe: never deletes your database or data)
echo ==================================================
echo.
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

where composer >nul 2>nul
if errorlevel 1 (
  echo [ERROR] Composer was not found. Install it from https://getcomposer.org/download/
  echo         then close and reopen VS Code and run this file again.
  pause
  exit /b 1
)

if not exist ".env" (
  copy ".env.example" ".env" >nul
  echo Created .env from .env.example  ^(database: kyaf_food_donation, user: root, no password^)
)

echo.
echo [1/5] Installing PHP packages with Composer ^(needs internet, 1-5 minutes^)...
call composer install --no-interaction --prefer-dist
if errorlevel 1 (
  echo [ERROR] composer install failed. Check your internet connection and try again.
  pause
  exit /b 1
)

echo.
echo [2/5] Application key...
findstr /B /C:"APP_KEY=base64:" ".env" >nul
if errorlevel 1 (
  php artisan key:generate --force
) else (
  echo Application key already exists - keeping it.
)

echo.
echo [3/5] Creating database tables ^(migrations^)...
php artisan migrate --force
if errorlevel 1 (
  echo.
  echo [ERROR] Could not connect to the database or run migrations.
  echo   1^) Is WAMP running ^(tray icon GREEN^)?
  echo   2^) Did you create a database named  kyaf_food_donation  in phpMyAdmin?
  echo   3^) Are DB_USERNAME / DB_PASSWORD in the .env file correct? ^(WAMP default: root, empty password^)
  pause
  exit /b 1
)

echo.
echo [4/5] Adding default roles, admin account and demo data...
php artisan db:seed --force
if errorlevel 1 (
  echo [ERROR] Seeding failed. See the message above.
  pause
  exit /b 1
)

echo.
echo [5/5] Clearing caches...
php artisan optimize:clear

echo.
echo ==================================================
echo   SETUP COMPLETE
echo   Next: double-click  start.bat  then open
echo   http://127.0.0.1:8000
echo   Demo logins are listed in DEMO_ACCOUNTS.md
echo ==================================================
set /p GO="Start the website now? (Y/N): "
if /i "!GO!"=="Y" call start.bat
endlocal
