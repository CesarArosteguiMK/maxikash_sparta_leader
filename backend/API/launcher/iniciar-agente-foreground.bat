@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  iniciar-agente-foreground.bat
rem  Arranca uvicorn EN ESTA MISMA CONSOLA, sin ocultar nada, para poder
rem  VER en vivo el error si la API no levanta.
rem  No usar como arranque automatico: si cierra la ventana, se cierra la API.
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"

rem -------- Resolver Python: venv > portable sin PATH > py -3 > python --------
set "PY_EXE="
set "PY_ARG="
set "PY_SRC="

rem venv solo si NO es Python free-threading (venv viejo hecho con 3.14t rompe deps)
if exist "%API_DIR%\venv\Scripts\python.exe" (
    "%API_DIR%\venv\Scripts\python.exe" "%API_DIR%\launcher\_check_standard_python.py" >nul 2>&1
    if not errorlevel 2 (
        set "PY_EXE=%API_DIR%\venv\Scripts\python.exe"
        set "PY_ARG="
        set "PY_SRC=venv"
        goto :have_py
    )
)

if exist "%API_DIR%\launcher\PYTHON_EXE.txt" (
    for /f "usebackq eol=# tokens=* delims=" %%x in ("%API_DIR%\launcher\PYTHON_EXE.txt") do (
        if exist "%%~x" (
            set "PY_EXE=%%~x"
            set "PY_ARG="
            set "PY_SRC=PYTHON_EXE.txt"
            goto :have_py
        )
    )
)
if exist "%API_DIR%\tools\PythonPortable\python.exe" (
    set "PY_EXE=%API_DIR%\tools\PythonPortable\python.exe"
    set "PY_SRC=portable PythonPortable"
    goto :have_py
)
if exist "%API_DIR%\tools\python312\python.exe" (
    set "PY_EXE=%API_DIR%\tools\python312\python.exe"
    set "PY_SRC=portable python312"
    goto :have_py
)
if exist "%API_DIR%\tools\Python312\python.exe" (
    set "PY_EXE=%API_DIR%\tools\Python312\python.exe"
    set "PY_SRC=portable Python312"
    goto :have_py
)

py -3 -c "import sys" >nul 2>&1
if not errorlevel 1 (
    set "PY_EXE=py"
    set "PY_ARG=-3"
    set "PY_SRC=global py -3"
    goto :have_py
)

python -c "import sys" >nul 2>&1
if not errorlevel 1 (
    set "PY_EXE=python"
    set "PY_ARG="
    set "PY_SRC=global python"
    goto :have_py
)

echo [ERROR] No hay Python: ni venv, ni portable en tools\, ni py -3, ni python en PATH.
echo         Sin instalador ni PATH: carpeta python.exe en API\tools\PythonPortable\ o launcher\PYTHON_EXE.txt
echo         Ejecute  launcher\Diagnosticar-API.bat  para ver el detalle.
if not "%SPARTA_API_NO_PAUSE%"=="1" pause
exit /b 1

:have_py
if not exist "%API_DIR%\app\main.py" (
    echo [ERROR] No esta app\main.py en %API_DIR%
    if not "%SPARTA_API_NO_PAUSE%"=="1" pause
    exit /b 1
)

netstat -ano 2>nul | findstr ":8000" | findstr "LISTENING" >nul
if !errorlevel! EQU 0 (
    echo [AVISO] Puerto 8000 ya esta en LISTEN.
    echo         Si quiere reiniciar, ejecute primero  launcher\cerrar-agente.bat
    echo.
    if not "%SPARTA_API_NO_PAUSE%"=="1" pause
    exit /b 0
)

echo.
echo ============================================
echo   API verificacion documentos -- FOREGROUND
echo   Carpeta : %API_DIR%
echo   Python  : %PY_SRC%
echo   URL     : http://127.0.0.1:8000   docs en /docs
echo   Detener : Ctrl+C  o cerrar esta ventana
echo ============================================
echo.

rem -------- Smoke import previo --------
echo [doctor] Probando import de la app antes de arrancar uvicorn...
"%PY_EXE%" %PY_ARG% "%~dp0_smoke_import.py"
if errorlevel 1 (
    echo.
    echo ============================================
    echo  [ERROR] La app NO se importa. Vea el traceback arriba.
    echo  SUGERENCIA:
    echo     launcher\Diagnosticar-API.bat /FIX
    echo     launcher\Diagnosticar-API.bat /INSTALL
    echo ============================================
    echo.
    if not "%SPARTA_API_NO_PAUSE%"=="1" pause
    exit /b 1
)
echo [doctor] OK -- arrancando uvicorn (Ctrl+C para detener).
echo.

"%PY_EXE%" %PY_ARG% -m uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers 1
set "RC=%ERRORLEVEL%"

echo.
echo ============================================
echo  uvicorn termino con codigo %RC%
echo ============================================
if not "%SPARTA_API_NO_PAUSE%"=="1" pause
exit /b %RC%
