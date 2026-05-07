@echo off
chcp 65001 >nul
rem Ejecutado desde PHP (usuario 878): mismo directorio trabajo que otros launchers API.
cd /d "%~dp0.."

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0web-api-1click-parar.ps1" -ApiDir "%CD%"
exit /b 0
