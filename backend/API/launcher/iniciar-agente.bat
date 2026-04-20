@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"

set "PYTHON_EXE=%API_DIR%\venv\Scripts\python.exe"
set "PYTHON_EXE_ARG="
set "PY_SOURCE=venv"

if not exist "%PYTHON_EXE%" (
    py -3 -c "import sys" >nul 2>&1
    if not errorlevel 1 (
        set "PYTHON_EXE=py"
        set "PYTHON_EXE_ARG=-3"
        set "PY_SOURCE=global (py -3)"
    ) else (
        python -c "import sys" >nul 2>&1
        if not errorlevel 1 (
            set "PYTHON_EXE=python"
            set "PYTHON_EXE_ARG="
            set "PY_SOURCE=global (python)"
        ) else (
            echo [ERROR] No hay venv ni Python global disponible.
            echo         Ejecute launcher\instalar-agente.bat o instale Python 3 en PATH.
            pause
            exit /b 1
        )
    )
)

if not exist "%API_DIR%\app\main.py" (
    echo [ERROR] No esta app\main.py. Revise la carpeta API.
    pause
    exit /b 1
)

netstat -ano 2>nul | findstr ":8000" 2>nul | findstr "LISTENING" >nul
if %errorlevel%==0 (
    echo La API ya esta en marcha en el puerto 8000.
    ping 127.0.0.1 -n 3 >nul
    exit /b 0
)

set "PYTHON_EXE=%PYTHON_EXE%"
set "PYTHON_EXE_ARG=%PYTHON_EXE_ARG%"
set "API_DIR=%API_DIR%"
powershell -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "%~dp0iniciar-agente.ps1"
if errorlevel 1 (
    echo [ERROR] No se pudo arrancar uvicorn. Revise dependencias o use launcher\instalar-agente.bat.
    pause
    exit /b 1
)

echo API verificacion documentos iniciada con %PY_SOURCE%: http://127.0.0.1:8000  ^(docs: /docs^)
echo Para detener: launcher\cerrar-agente.bat o launcher\Detener-API-Verificacion.bat
ping 127.0.0.1 -n 3 >nul
