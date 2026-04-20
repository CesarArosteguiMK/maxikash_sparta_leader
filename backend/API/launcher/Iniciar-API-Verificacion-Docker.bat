@echo off
:: API Python con Docker (solo contenedor API; requiere Docker Desktop).
for %%I in ("%~dp0..") do set "API_ROOT=%%~fI"
if "%API_ROOT:~-1%"=="\" set "API_ROOT=%API_ROOT:~0,-1%"
start "API Verificación Documentos (Docker)" cmd /k "cd /d "%API_ROOT%" && call "%~dp0Iniciar-API-Verificacion-Core.bat""
