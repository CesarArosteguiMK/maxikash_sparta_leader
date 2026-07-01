@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  iniciar-agente-tarea.bat
rem  Entrada para Task Scheduler: uvicorn queda corriendo en primer plano
rem  dentro de esta tarea. No usar iniciar-agente.bat aqui, porque ese
rem  camino lanza un proceso hijo oculto y la tarea puede terminar antes.
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
set "LOG_DIR=%TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1

set "SPARTA_API_NO_PAUSE=1"
set "STDOUT_LOG=%LOG_DIR%\uvicorn-stdout.log"
set "STDERR_LOG=%LOG_DIR%\uvicorn-stderr.log"
set "PADDLE_HOME=%API_DIR%\.paddle_home"
set "USERPROFILE=%PADDLE_HOME%"
set "HOME=%PADDLE_HOME%"
set "XDG_CACHE_HOME=%PADDLE_HOME%\.cache"
set "PADDLE_PDX_CACHE_HOME=%API_DIR%\.paddlex_cache_runtime"
set "PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT=False"
set "FLAGS_use_mkldnn=0"
set "FLAGS_use_onednn=0"

if not exist "%PADDLE_HOME%" mkdir "%PADDLE_HOME%" >nul 2>&1
if not exist "%XDG_CACHE_HOME%" mkdir "%XDG_CACHE_HOME%" >nul 2>&1
if not exist "%PADDLE_PDX_CACHE_HOME%" mkdir "%PADDLE_PDX_CACHE_HOME%" >nul 2>&1

> "%STDOUT_LOG%" echo [%DATE% %TIME%] Task Scheduler iniciando API documental...
> "%STDERR_LOG%" type nul

call "%~dp0iniciar-agente-foreground.bat" >> "%STDOUT_LOG%" 2>> "%STDERR_LOG%"
set "RC=%ERRORLEVEL%"

>> "%STDOUT_LOG%" echo [%DATE% %TIME%] Task Scheduler termino con codigo %RC%.
exit /b %RC%
