@echo off
:: API Python con Docker (solo contenedor API; requiere Docker Desktop).
start "API Verificación Documentos (Docker)" cmd /k "cd /d "%~dp0" && call "%~dp0Iniciar-API-Verificacion-Core.bat""
