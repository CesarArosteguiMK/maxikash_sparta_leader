@echo off
:: Abre una ventana que permanece abierta (cmd /k)
start "API Verificación Documentos" cmd /k "cd /d "%~dp0" && call "%~dp0Iniciar-API-Verificacion-Core.bat""
