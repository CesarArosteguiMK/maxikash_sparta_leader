@echo off
chcp 65001 >nul
title Detener API Verificación Documentos
cd /d "%~dp0"

echo.
echo  Deteniendo API Verificación Documentos...
echo.
docker compose down
echo.
echo  Listo.
echo.
pause
