@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

set "API_DIR=%~dp0"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"

set "SILENT=0"
set "MODE=GLOBAL"
if /i "%~1"=="/SILENT" set "SILENT=1"
if /i "%~2"=="/SILENT" set "SILENT=1"
if /i "%~1"=="/VENV" set "MODE=VENV"
if /i "%~2"=="/VENV" set "MODE=VENV"
if /i "%~1"=="/GLOBAL" set "MODE=GLOBAL"
if /i "%~2"=="/GLOBAL" set "MODE=GLOBAL"

if "%SILENT%"=="0" (
    echo.
    echo ============================================
    echo   API verificacion documentos - instalacion
    echo   Carpeta: %API_DIR%
    echo ============================================
    echo   Modo por defecto: GLOBAL ^(instala en Python de Windows^)
    echo   Opcional: /VENV para entorno virtual
    echo   Tesseract OCR: debe estar instalado en Windows
    echo   ^(no se puede instalar solo con pip^)
    echo ============================================
    echo.
)

if not exist "%API_DIR%\requirements.txt" (
    echo [ERROR] No esta requirements.txt en %API_DIR%
    if "%SILENT%"=="0" pause
    exit /b 1
)

set "PY_CMD="
py -3 -c "import sys" >nul 2>&1
if not errorlevel 1 set "PY_CMD=py -3"
if not defined PY_CMD (
    python -c "import sys" >nul 2>&1
    if not errorlevel 1 set "PY_CMD=python"
)
if not defined PY_CMD (
    echo [ERROR] No se encontro Python 3 ^(pruebe "py -3" o "python" en PATH^).
    echo         Instale Python 3.10+ desde https://www.python.org/downloads/
    if "%SILENT%"=="0" pause
    exit /b 1
)

if "%SILENT%"=="0" echo [OK] Python detectado: %PY_CMD%

if not exist "%API_DIR%\logs" mkdir "%API_DIR%\logs" >nul 2>&1
if not exist "%API_DIR%\.env" (
    if exist "%API_DIR%\.env.example" (
        copy /Y "%API_DIR%\.env.example" "%API_DIR%\.env" >nul
        if "%SILENT%"=="0" echo [OK] Copiado .env desde .env.example
    ) else (
        if "%SILENT%"=="0" echo [AVISO] No hay .env.example; cree .env manualmente si hace falta.
    )
)

if /i "%MODE%"=="VENV" goto :install_venv
goto :install_global

:install_global
echo [pip] Instalando dependencias en Python GLOBAL...
call %PY_CMD% -m pip install --upgrade pip
if errorlevel 1 (
    echo [ERROR] pip upgrade global fallo.
    if "%SILENT%"=="0" pause
    exit /b 1
)
call %PY_CMD% -m pip install -r "%API_DIR%\requirements.txt"
if errorlevel 1 (
    echo [AVISO] Instalacion global fallo. Reintentando con --user...
    call %PY_CMD% -m pip install --user -r "%API_DIR%\requirements.txt"
    if errorlevel 1 (
        echo [ERROR] pip install global/--user fallo. Revise mensajes arriba.
        if "%SILENT%"=="0" pause
        exit /b 1
    )
)
goto :post_install

:install_venv
if not exist "%API_DIR%\venv\Scripts\python.exe" (
    echo [venv] Creando entorno virtual...
    call %PY_CMD% -m venv "%API_DIR%\venv"
    if errorlevel 1 (
        echo [ERROR] No se pudo crear venv. Revise Python.
        if "%SILENT%"=="0" pause
        exit /b 1
    )
)
set "VENV_PY=%API_DIR%\venv\Scripts\python.exe"
if not exist "%VENV_PY%" (
    echo [ERROR] Falta venv\Scripts\python.exe
    if "%SILENT%"=="0" pause
    exit /b 1
)
echo [pip] Instalando dependencias en venv...
"%VENV_PY%" -m pip install --upgrade pip
if errorlevel 1 (
    echo [ERROR] pip upgrade en venv fallo.
    if "%SILENT%"=="0" pause
    exit /b 1
)
"%VENV_PY%" -m pip install -r "%API_DIR%\requirements.txt"
if errorlevel 1 (
    echo [ERROR] pip install en venv fallo. Revise mensajes arriba.
    if "%SILENT%"=="0" pause
    exit /b 1
)

:post_install
set "TESS_OK=0"
if exist "C:\Program Files\Tesseract-OCR\tesseract.exe" set "TESS_OK=1"
if "%TESS_OK%"=="0" if exist "C:\Program Files (x86)\Tesseract-OCR\tesseract.exe" set "TESS_OK=1"

echo.
if "%TESS_OK%"=="1" (
    echo [OK] Tesseract OCR detectado.
) else (
    echo [IMPORTANTE] No se encontro Tesseract en la ruta habitual.
    echo              Descargue el instalador para Windows desde:
    echo              https://github.com/UB-Mannheim/tesseract/wiki
)
echo.
echo [OK] Dependencias Python listas.
echo      Solo vuelva a ejecutar este .bat si cambia requirements.txt o falta un paquete.
echo      Cada dia / cada sesion: iniciar-agente-oculto.vbs ^(o iniciar-agente.bat^) — no hace falta reinstalar.
echo      Instalacion en venv ^(opcional^): instalar-agente.bat /VENV
echo      Docker ^(opcional^): Iniciar-API-Verificacion-Docker.bat
echo.

if "%SILENT%"=="0" pause
exit /b 0
