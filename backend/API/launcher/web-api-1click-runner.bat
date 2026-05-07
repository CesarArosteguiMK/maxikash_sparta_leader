@echo off
chcp 65001 >nul
setlocal

rem ---------------------------------------------------------------------
rem Punto de entrada cuando el usuario 878 pulsa «API» en Inicio:
rem   Controller\Inicio::apiDocOneClickIniciar → este .bat →
rem   Iniciar-API-Verificacion.bat (doctor + install si falta + arranque oculto).
rem No bifurcar otro camino desde la UI; así el polling y logs web-api-1click-*.log cuadran.
rem ---------------------------------------------------------------------

call "%~dp0Iniciar-API-Verificacion.bat"
set "RC=%ERRORLEVEL%"
echo __FIN__:%RC%
exit /b %RC%
