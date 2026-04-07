@echo off
setlocal EnableExtensions
REM =============================================================================
REM Gastos Cobranza — ejecución diaria del agente (POST /run), p. ej. a las 10:00.
REM Programador de tareas (Windows): Diario, 10:00, ejecutar este .bat.
REM Requiere: agente Node en marcha y [gastoscobranza_agent] enabled=1 en config.ini
REM =============================================================================

cd /d "%~dp0"

if defined GASTOS_COBRANZA_PHP_EXE if exist "%GASTOS_COBRANZA_PHP_EXE%" (
  "%GASTOS_COBRANZA_PHP_EXE%" "%~dp0ejecutar_gastos_cobranza_agente_diario.php" %*
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
  echo [ERROR] No se encontro php.exe. Defina GASTOS_COBRANZA_PHP_EXE o instale XAMPP.
  exit /b 1
)

"%PHP_EXE%" "%~dp0ejecutar_gastos_cobranza_agente_diario.php" %*
exit /b %ERRORLEVEL%
