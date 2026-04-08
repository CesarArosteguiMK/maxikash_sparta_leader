@echo off
chcp 65001 >nul
title Detener API Verificación Documentos
cd /d "%~dp0"

echo.
echo  Deteniendo API Verificación Documentos ^(puerto 8000 + Docker si aplica^)...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0cerrar-agente.ps1" -Silent

docker info >nul 2>&1
if %errorlevel%==0 (
    echo  Docker detectado: docker compose down...
    docker compose down
) else (
    echo  ^(Docker no activo; omitido compose down^)
)

echo.
echo  Listo.
echo.
pause
