@echo off
setlocal

cd /d "%~dp0.."

set "PHP_EXE=%~1"
if "%PHP_EXE%"=="" set "PHP_EXE=php"

for /f "delims=" %%I in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set "TS=%%I"
set "LOG=application\logs\winner-task-%TS%.log"

"%PHP_EXE%" index.php bids run_daily_winner >> "%LOG%" 2>&1
exit /b %errorlevel%
