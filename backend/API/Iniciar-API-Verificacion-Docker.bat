@echo off
:: API Python con Docker + Postgres + Redis (opcional; requiere Docker Desktop).
start "API Verificación Documentos (Docker)" cmd /k "cd /d "%~dp0" && call "%~dp0Iniciar-API-Verificacion-Core.bat""
