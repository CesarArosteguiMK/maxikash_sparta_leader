@echo off
:: API local: Python venv + uvicorn (sin Docker). Por defecto SIN ventana (igual que iniciar-agente-oculto.vbs).
:: Consola visible:  Iniciar-API-Verificacion.bat /CONSOLA
:: Docker:            Iniciar-API-Verificacion-Docker.bat
cd /d "%~dp0"
if /i "%~1"=="/CONSOLA" (
    start "API Verificación Documentos" cmd /k "cd /d "%~dp0" && call "%~dp0iniciar-agente.bat""
    exit /b 0
)
wscript //nologo "%~dp0iniciar-agente-oculto.vbs"
exit /b 0
