@echo off
chcp 65001 >nul
setlocal

rem Runner para ejecución desde web (usuario 878):
rem ejecuta el 1-click principal y al final imprime marcador para polling.

call "%~dp0Iniciar-API-Verificacion.bat"
set "RC=%ERRORLEVEL%"
echo __FIN__:%RC%
exit /b %RC%
