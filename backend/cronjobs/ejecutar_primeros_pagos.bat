@echo off
setlocal EnableExtensions
REM ============================================================================
REM Lanzador para Programador de tareas u otro software de automatizacion.
REM Un .php no se ejecuta solo en Windows: este .bat invoca php.exe con el script.
REM Programar ESTE archivo cada 1 a 5 minutos (recomendado).
REM Para evitar parpadeos de ventana CMD: use ejecutar_primeros_pagos_oculto.vbs
REM (Programa wscript.exe, argumentos //B //Nologo "ruta\ejecutar_primeros_pagos_oculto.vbs").
REM ============================================================================

cd /d "%~dp0"

REM Opcional: variable de entorno PRIMEROS_PAGOS_PHP_EXE = ruta completa a php.exe
if defined PRIMEROS_PAGOS_PHP_EXE if exist "%PRIMEROS_PAGOS_PHP_EXE%" (
  "%PRIMEROS_PAGOS_PHP_EXE%" "%~dp0enviar_primeros_pagos_lunes.php" %*
  exit /b %ERRORLEVEL%
)

set "PHP_EXE="
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE if exist "%ProgramFiles%\xampp\php\php.exe" set "PHP_EXE=%ProgramFiles%\xampp\php\php.exe"
if not defined PHP_EXE if exist "%ProgramFiles(x86)%\xampp\php\php.exe" set "PHP_EXE=%ProgramFiles(x86)%\xampp\php\php.exe"

if not defined PHP_EXE (
  where php >nul 2>&1
  if not errorlevel 1 (
    for /f "delims=" %%W in ('where php 2^>nul') do (
      set "PHP_EXE=%%W"
      goto :run_php
    )
  )
)

:run_php
if not defined PHP_EXE (
  echo [ERROR] No se encontro php.exe.
  echo Opciones: 1^) Variable de entorno PRIMEROS_PAGOS_PHP_EXE  2^) Instalar XAMPP  3^) Anadir php al PATH  4^) Editar este .bat y fijar SET "PHP_EXE=..." abajo.
  exit /b 1
)

"%PHP_EXE%" "%~dp0enviar_primeros_pagos_lunes.php" %*
exit /b %ERRORLEVEL%

REM Si lo anterior falla, descomenta y pon tu ruta real:
REM set "PHP_EXE=D:\ruta\a\php.exe"
REM "%PHP_EXE%" "%~dp0enviar_primeros_pagos_lunes.php" %*
REM exit /b %ERRORLEVEL%
