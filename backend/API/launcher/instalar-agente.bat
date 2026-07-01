@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

rem =====================================================================
rem  instalar-agente.bat
rem  Instala dependencias Python de la API verificacion documentos.
rem  - Por defecto: Python GLOBAL (sin venv).
rem  - Opciones:    /VENV  (entorno virtual .\venv)
rem                 /SILENT (no pausa)
rem  Mejora vs version vieja: TODO el output de pip se guarda en
rem  logs\instalar-YYYYMMDD-HHMMSS.log y, si algo falla, se muestran
rem  las ultimas lineas relevantes y se sugiere ejecutar el doctor.
rem
rem  Sin PATH / sin instalador en el servidor: ponga python.exe en
rem    API\tools\PythonPortable\   o una linea con ruta en launcher\PYTHON_EXE.txt
rem =====================================================================

for %%I in ("%~dp0..") do set "API_DIR=%%~fI"
if "%API_DIR:~-1%"=="\" set "API_DIR=%API_DIR:~0,-1%"
cd /d "%API_DIR%"

set "SILENT=0"
set "MODE=GLOBAL"
if /i "%~1"=="/SILENT" set "SILENT=1"
if /i "%~2"=="/SILENT" set "SILENT=1"
if /i "%~1"=="/VENV"   set "MODE=VENV"
if /i "%~2"=="/VENV"   set "MODE=VENV"
if /i "%~1"=="/GLOBAL" set "MODE=GLOBAL"
if /i "%~2"=="/GLOBAL" set "MODE=GLOBAL"

set "LOG_DIR=%TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs"
if not exist "%LOG_DIR%" mkdir "%LOG_DIR%" >nul 2>&1

rem Timestamp YYYYMMDD-HHMMSS sin depender de locale
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value 2^>nul') do set "LDT=%%I"
if "%LDT%"=="" set "LDT=%date:~-4%%date:~3,2%%date:~0,2%-%time:~0,2%%time:~3,2%%time:~6,2%"
set "STAMP=%LDT:~0,8%-%LDT:~8,6%"
set "INST_LOG=%LOG_DIR%\instalar-%STAMP%.log"

if "%SILENT%"=="0" (
    echo.
    echo ============================================
    echo   API verificacion documentos - instalacion
    echo   Carpeta: %API_DIR%
    echo   Modo   : %MODE%   ^(otra opcion: /VENV ^| /GLOBAL^)
    echo   Log    : %INST_LOG%
    echo ============================================
    echo.
    echo Tesseract OCR debe instalarse aparte ^(no se puede con pip^).
    echo.
)

if not exist "%API_DIR%\requirements.txt" (
    echo [ERROR] No esta requirements.txt en %API_DIR%>>"%INST_LOG%"
    echo [ERROR] No esta requirements.txt en %API_DIR%
    if "%SILENT%"=="0" pause
    exit /b 1
)

rem -------- Detectar Python (portable en API primero: sin PATH ni instalador) --------
set "PY_EXE_FULL="
if exist "%API_DIR%\launcher\PYTHON_EXE.txt" (
    for /f "usebackq eol=# tokens=* delims=" %%x in ("%API_DIR%\launcher\PYTHON_EXE.txt") do (
        if exist "%%~x" (
            set "PY_EXE_FULL=%%~x"
            goto :after_py_txt
        )
    )
)
:after_py_txt
if not defined PY_EXE_FULL if exist "%API_DIR%\tools\PythonPortable\python.exe" set "PY_EXE_FULL=%API_DIR%\tools\PythonPortable\python.exe"
if not defined PY_EXE_FULL if exist "%API_DIR%\tools\python312\python.exe" set "PY_EXE_FULL=%API_DIR%\tools\python312\python.exe"
if not defined PY_EXE_FULL if exist "%API_DIR%\tools\Python312\python.exe" set "PY_EXE_FULL=%API_DIR%\tools\Python312\python.exe"

set "PY_CMD="
if defined PY_EXE_FULL (
    if "%SILENT%"=="0" echo [OK] Python portable ^(no requiere PATH^): !PY_EXE_FULL!
    echo Python portable: !PY_EXE_FULL!>>"%INST_LOG%"
    goto :py_found
)
py -3 -c "import sys" >nul 2>&1
if not errorlevel 1 set "PY_CMD=py -3"
if not defined PY_CMD (
    python -c "import sys" >nul 2>&1
    if not errorlevel 1 set "PY_CMD=python"
)
if not defined PY_CMD (
    echo [ERROR] No se encontro Python 3.>>"%INST_LOG%"
    echo [ERROR] No se encontro Python 3.
    echo         Sin PATH: descomprima Python 3.12 en  %API_DIR%\tools\PythonPortable\
    echo         o ponga la ruta completa del exe en  %API_DIR%\launcher\PYTHON_EXE.txt
    echo         Con PATH: use "py -3" o "python" desde https://www.python.org/downloads/windows/
    if "%SILENT%"=="0" pause
    exit /b 1
)
if "%SILENT%"=="0" echo [OK] Python en PATH: %PY_CMD%
echo Python PATH: %PY_CMD%>>"%INST_LOG%"

:py_found
rem -------- Avisar si Python es muy nuevo --------
if defined PY_EXE_FULL (
    for /f "delims=" %%v in ('"!PY_EXE_FULL!" -c "import platform;print(platform.python_version())" 2^>nul') do set "PYVER=%%v"
) else (
    for /f "delims=" %%v in ('%PY_CMD% -c "import platform;print(platform.python_version())" 2^>nul') do set "PYVER=%%v"
)
echo Version Python: %PYVER%>>"%INST_LOG%"
if defined PYVER (
    for /f "tokens=1,2 delims=." %%a in ("%PYVER%") do (
        set "PYMAJ=%%a"
        set "PYMIN=%%b"
    )
    if "%PYMAJ%"=="3" if !PYMIN! GEQ 13 (
        echo.
        echo [AVISO] Python !PYVER! es muy nuevo: paquetes con C/Rust ^(pdf417decoder, torch, pyzbar^)
        echo         pueden NO tener wheel y exigir Visual Studio Build Tools para compilar.
        echo         Si la instalacion falla a la mitad, instale Python 3.12 64-bit y reintente.
        echo.
        echo [AVISO] Python !PYVER! es muy nuevo. Posibles fallos de wheel.>>"%INST_LOG%"
    )
)

rem -------- Python free-thread / sin GIL (PyMuPDF falla: Py_GIL_DISABLED assertion) --------
echo.>>"%INST_LOG%"
echo ===== chequeo Python estandar (no free-thread) =====>>"%INST_LOG%"
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" "%API_DIR%\launcher\_check_standard_python.py" >>"%INST_LOG%" 2>&1
) else (
    call %PY_CMD% "%API_DIR%\launcher\_check_standard_python.py" >>"%INST_LOG%" 2>&1
)
if errorlevel 2 (
    echo.
    echo [ERROR] Python incompatible ^(free-threading / Py_GIL_DISABLED^). Vea arriba y el log: %INST_LOG%
    echo          Instale Python 3.12 ESTANDAR y borre la carpeta venv antes de reintentar.
    if "%SILENT%"=="0" pause
    exit /b 2
)

rem -------- Si el Python portable esta roto (falta _socket/ssl), bootstrapear --------
rem El embeddable mal copiado pierde DLLs criticas; ensurepip no puede funcionar
rem porque socket.py no puede importar _socket.pyd.
echo.>>"%INST_LOG%"
echo ===== chequeo Python tiene _socket/ssl =====>>"%INST_LOG%"
set "NEED_BOOTSTRAP=0"
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" -c "import _socket, ssl" >>"%INST_LOG%" 2>&1
    if errorlevel 1 set "NEED_BOOTSTRAP=1"
)
if "%NEED_BOOTSTRAP%"=="1" (
    echo [bootstrap] Python portable incompleto. Descargando embeddable oficial...
    echo [bootstrap] Python portable incompleto. Descargando embeddable oficial...>>"%INST_LOG%"
    powershell -NoProfile -ExecutionPolicy Bypass -File "%API_DIR%\launcher\bootstrap-python.ps1" -Force >>"%INST_LOG%" 2>&1
    set "BS_RC=!ERRORLEVEL!"
    echo [bootstrap] codigo de salida: !BS_RC!>>"%INST_LOG%"
    if not "!BS_RC!"=="0" (
        echo [ERROR] Bootstrap del Python portable fallo. Vea %INST_LOG%
        if "%SILENT%"=="0" pause
        exit /b 1
    )
    rem Tras el bootstrap el python.exe portable es nuevo.
    set "PY_EXE_FULL=%API_DIR%\tools\PythonPortable\python.exe"
)

rem -------- Bootstrap pip si falta (ensurepip / get-pip si bootstrap ya corrio) --------
echo.>>"%INST_LOG%"
echo ===== bootstrap pip ^(ensurepip si pip no responde^) =====>>"%INST_LOG%"
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" -m pip --version >>"%INST_LOG%" 2>&1
    if errorlevel 1 (
        echo [pip] pip no operativo; ejecutando ensurepip...>>"%INST_LOG%"
        if "%SILENT%"=="0" echo [pip] Activando pip ^(ensurepip^)...
        "!PY_EXE_FULL!" -m ensurepip --upgrade >>"%INST_LOG%" 2>&1
        "!PY_EXE_FULL!" -m pip install --upgrade pip >>"%INST_LOG%" 2>&1
        "!PY_EXE_FULL!" -m pip --version >>"%INST_LOG%" 2>&1
        if errorlevel 1 (
            echo [pip] ensurepip fallo; lanzando bootstrap-python.ps1 -Force ...>>"%INST_LOG%"
            if "%SILENT%"=="0" echo [pip] ensurepip fallo; reinstalando Python portable...
            powershell -NoProfile -ExecutionPolicy Bypass -File "%API_DIR%\launcher\bootstrap-python.ps1" -Force >>"%INST_LOG%" 2>&1
            "!PY_EXE_FULL!" -m pip --version >>"%INST_LOG%" 2>&1
            if errorlevel 1 (
                echo [ERROR] pip sigue sin funcionar tras bootstrap. Vea %INST_LOG%
                if "%SILENT%"=="0" pause
                exit /b 1
            )
        )
        if "%SILENT%"=="0" echo [OK] pip disponible.
        echo [OK] pip disponible.>>"%INST_LOG%"
    )
) else (
    call %PY_CMD% -m pip --version >>"%INST_LOG%" 2>&1
    if errorlevel 1 (
        echo [pip] pip no operativo; ejecutando ensurepip...>>"%INST_LOG%"
        if "%SILENT%"=="0" echo [pip] Activando pip ^(ensurepip^)...
        call %PY_CMD% -m ensurepip --upgrade >>"%INST_LOG%" 2>&1
        call %PY_CMD% -m pip install --upgrade pip >>"%INST_LOG%" 2>&1
        call %PY_CMD% -m pip --version >>"%INST_LOG%" 2>&1
        if errorlevel 1 (
            echo [ERROR] pip sigue sin funcionar tras ensurepip. Vea %INST_LOG%
            if "%SILENT%"=="0" pause
            exit /b 1
        )
        if "%SILENT%"=="0" echo [OK] pip disponible tras ensurepip.
        echo [OK] pip disponible tras ensurepip.>>"%INST_LOG%"
    )
)

rem -------- Crear .env si falta --------
if not exist "%API_DIR%\.env" (
    if exist "%API_DIR%\.env.example" (
        copy /Y "%API_DIR%\.env.example" "%API_DIR%\.env" >nul
        echo [OK] Copiado .env desde .env.example>>"%INST_LOG%"
        if "%SILENT%"=="0" echo [OK] Copiado .env desde .env.example
    ) else (
        echo [AVISO] No hay .env.example; cree .env manualmente si hace falta.>>"%INST_LOG%"
        if "%SILENT%"=="0" echo [AVISO] No hay .env.example; cree .env manualmente si hace falta.
    )
)

if /i "%MODE%"=="VENV" goto :install_venv
goto :install_global

:install_global
echo.>>"%INST_LOG%"
echo ===== pip upgrade (global) =====>>"%INST_LOG%"
echo [pip] Actualizando pip ^(global^)...
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" -m pip install --upgrade pip >>"%INST_LOG%" 2>&1
) else (
    call %PY_CMD% -m pip install --upgrade pip >>"%INST_LOG%" 2>&1
)
if errorlevel 1 (
    echo [ERROR] pip upgrade global fallo. Vea %INST_LOG%
    goto :show_tail_and_fail
)
echo.>>"%INST_LOG%"
echo ===== pip install -r requirements (global) =====>>"%INST_LOG%"
echo [pip] Instalando requirements.txt en Python GLOBAL...
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" -m pip install -r "%API_DIR%\requirements.txt" >>"%INST_LOG%" 2>&1
) else (
    call %PY_CMD% -m pip install -r "%API_DIR%\requirements.txt" >>"%INST_LOG%" 2>&1
)
if errorlevel 1 (
    echo [AVISO] Instalacion global fallo. Reintentando con --user...
    echo.>>"%INST_LOG%"
    echo ===== pip install -r requirements (--user) =====>>"%INST_LOG%"
    if defined PY_EXE_FULL (
        "!PY_EXE_FULL!" -m pip install --user -r "%API_DIR%\requirements.txt" >>"%INST_LOG%" 2>&1
    ) else (
        call %PY_CMD% -m pip install --user -r "%API_DIR%\requirements.txt" >>"%INST_LOG%" 2>&1
    )
    if errorlevel 1 (
        echo [ERROR] pip install global/--user fallo. Vea %INST_LOG%
        goto :show_tail_and_fail
    )
)
goto :post_install

:install_venv
rem Python embeddable (portable) muchas veces no incluye modulo venv.
rem En ese caso, caer a instalacion GLOBAL en vez de fallar.
echo.>>"%INST_LOG%"
echo ===== soporte de modulo venv =====>>"%INST_LOG%"
if defined PY_EXE_FULL (
    "!PY_EXE_FULL!" -m venv --help >>"%INST_LOG%" 2>&1
) else (
    call %PY_CMD% -m venv --help >>"%INST_LOG%" 2>&1
)
if errorlevel 1 (
    echo [AVISO] Este Python no trae modulo venv; se usara modo GLOBAL.>>"%INST_LOG%"
    if "%SILENT%"=="0" echo [AVISO] Este Python no trae modulo venv; cambio a instalacion GLOBAL.
    goto :install_global
)

rem Si ya hay venv pero se creo con Python 3.14t/free-thread ^(pip y PyMuPDF fallan^): borrarlo y recrear con la base portable/PATH actual.
if exist "%API_DIR%\venv\Scripts\python.exe" (
    echo ===== comprobacion venv existente (no free-thread) =====>>"%INST_LOG%"
    "%API_DIR%\venv\Scripts\python.exe" "%API_DIR%\launcher\_check_standard_python.py" >>"%INST_LOG%" 2>&1
    if errorlevel 2 (
        echo [venv] El entorno actual es incompatible ^(ej. Python free-thread en venv^).>>"%INST_LOG%"
        echo [venv] El entorno actual es incompatible ^(ej. Python free-thread^). Recreando venv...
        rmdir /s /q "%API_DIR%\venv" 2>nul
        timeout /t 1 /nobreak >nul
        if exist "%API_DIR%\venv\Scripts\python.exe" (
            echo [ERROR] No se pudo borrar backend\API\venv. Cierre procesos Python y borre esa carpeta a mano.>>"%INST_LOG%"
            echo [ERROR] No se pudo borrar venv — cierre procesos usando Python en esa carpeta y borre backend\API\venv
            if "%SILENT%"=="0" pause
            exit /b 2
        )
        echo [venv] Carpeta venv eliminada; se creara de nuevo con el Python base.>>"%INST_LOG%"
    )
)
if not exist "%API_DIR%\venv\Scripts\python.exe" (
    echo [venv] Creando entorno virtual...
    if defined PY_EXE_FULL (
        "!PY_EXE_FULL!" -m venv "%API_DIR%\venv" >>"%INST_LOG%" 2>&1
    ) else (
        call %PY_CMD% -m venv "%API_DIR%\venv" >>"%INST_LOG%" 2>&1
    )
    if errorlevel 1 (
        echo [ERROR] No se pudo crear venv. Revise Python.
        goto :show_tail_and_fail
    )
)
set "VENV_PY=%API_DIR%\venv\Scripts\python.exe"
if not exist "%VENV_PY%" (
    echo [ERROR] Falta venv\Scripts\python.exe
    goto :show_tail_and_fail
)
echo.>>"%INST_LOG%"
echo ===== chequeo Python venv tras crear/recrear (no free-thread) =====>>"%INST_LOG%"
"%VENV_PY%" "%API_DIR%\launcher\_check_standard_python.py" >>"%INST_LOG%" 2>&1
if errorlevel 2 (
    echo.
    echo [ERROR] El Python del venv sigue siendo incompatible ^(free-threading^).
    echo          Asegure API\tools\PythonPortable con Python 3.12 estandar o PATH con 3.12; borre venv y reintente.
    if "%SILENT%"=="0" pause
    exit /b 2
)
echo.>>"%INST_LOG%"
echo ===== pip upgrade (venv) =====>>"%INST_LOG%"
echo [pip] Actualizando pip en venv...
"%VENV_PY%" -m pip install --upgrade pip >>"%INST_LOG%" 2>&1
if errorlevel 1 (
    echo [ERROR] pip upgrade en venv fallo. Vea %INST_LOG%
    goto :show_tail_and_fail
)
echo.>>"%INST_LOG%"
echo ===== pip install -r requirements (venv) =====>>"%INST_LOG%"
echo [pip] Instalando requirements.txt en venv...
"%VENV_PY%" -m pip install -r "%API_DIR%\requirements.txt" >>"%INST_LOG%" 2>&1
if errorlevel 1 (
    echo [ERROR] pip install en venv fallo. Vea %INST_LOG%
    goto :show_tail_and_fail
)

:post_install
echo.>>"%INST_LOG%"
echo ===== PaddleOCR modelos locales =====>>"%INST_LOG%"
echo [paddleocr] Preparando modelos locales en API\.paddlex_cache_runtime...
set "PADDLE_WARMUP_PY="
if defined VENV_PY if exist "%VENV_PY%" set "PADDLE_WARMUP_PY=%VENV_PY%"
if not defined PADDLE_WARMUP_PY if defined PY_EXE_FULL set "PADDLE_WARMUP_PY=%PY_EXE_FULL%"
if exist "%API_DIR%\scripts\ensure_paddleocr_models.py" (
    if defined PADDLE_WARMUP_PY (
        "!PADDLE_WARMUP_PY!" "%API_DIR%\scripts\ensure_paddleocr_models.py" >>"%INST_LOG%" 2>&1
    ) else (
        call %PY_CMD% "%API_DIR%\scripts\ensure_paddleocr_models.py" >>"%INST_LOG%" 2>&1
    )
    if errorlevel 1 (
        echo [AVISO] PaddleOCR no quedo precargado. La API usara RapidOCR/Tesseract como respaldo hasta corregirlo.
        echo [AVISO] PaddleOCR warmup fallo; revisar detalle arriba.>>"%INST_LOG%"
    ) else (
        echo [OK] PaddleOCR instalado y modelos locales listos.
        echo [OK] PaddleOCR instalado y modelos locales listos.>>"%INST_LOG%"
    )
)

set "TESS_OK=0"
if exist "C:\Program Files\Tesseract-OCR\tesseract.exe" set "TESS_OK=1"
if "%TESS_OK%"=="0" if exist "C:\Program Files (x86)\Tesseract-OCR\tesseract.exe" set "TESS_OK=1"

echo.
if "%TESS_OK%"=="1" (
    echo [OK] Tesseract OCR detectado.
) else (
    echo [IMPORTANTE] No se encontro Tesseract en la ruta habitual.
    echo              Descargue para Windows: https://github.com/UB-Mannheim/tesseract/wiki
    echo              Instalelo en  C:\Program Files\Tesseract-OCR\
)
echo.

rem -------- Diagnostico final automatico --------
echo [doctor] Ejecutando diagnostico final...
echo.>>"%INST_LOG%"
echo ===== doctor-api.ps1 (final) =====>>"%INST_LOG%"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0doctor-api.ps1" -Quiet >>"%INST_LOG%" 2>&1
set "DOC_RC=!ERRORLEVEL!"
if "%DOC_RC%"=="0" (
    echo [OK] Diagnostico final: sin problemas.
) else (
    if "%DOC_RC%"=="2" (
        echo [AVISO] Diagnostico final: hay advertencias ^(codigo 2^). Vea %INST_LOG%.
    ) else (
        echo [ERROR] Diagnostico final detecto problemas ^(codigo %DOC_RC%^).
        echo         Ejecute  launcher\Diagnosticar-API.bat  para verlos en pantalla.
    )
)

echo.
echo [OK] Instalacion finalizada.
echo      Log completo:    %INST_LOG%
echo      Arrancar oculto: launcher\iniciar-agente-oculto.vbs
echo      Arrancar visible:launcher\iniciar-agente-foreground.bat
echo      Diagnostico:     launcher\Diagnosticar-API.bat
echo.

if "%SILENT%"=="0" pause
exit /b 0

:show_tail_and_fail
echo.
echo ============================================
echo   ULTIMAS LINEAS DEL LOG  (%INST_LOG%)
echo ============================================
powershell -NoProfile -Command "Get-Content -LiteralPath '%INST_LOG%' -Tail 40"
echo ============================================
echo.
echo Sugerencia: ejecute  launcher\Diagnosticar-API.bat /INSTALL
echo            para que el doctor reinstale solo lo que falla.
echo.
if "%SILENT%"=="0" pause
exit /b 1
