@echo off
setlocal EnableExtensions EnableDelayedExpansion
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

echo Running automated tests (uses an in-memory SQLite database; your MySQL data is NOT touched)...
php vendor\bin\phpunit
pause
endlocal
