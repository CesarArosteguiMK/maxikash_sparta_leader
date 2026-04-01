@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

title EC — Worker / Excel GC (agente Gastos Cobranza)

rem Esta copia vive en: sparta___SPARTA_SECRET_REDACTED__\backend\services\gastos-cobranza-agent\launcher
rem Subir 4 niveles llega a la raíz del repo (donde está la carpeta tools).
set "HERE=%~dp0"
set "ROOT=%HERE%..\..\..\..\"
set "WORKER=%ROOT%tools\ec-webhook-worker"
set "ENRICH=%ROOT%tools\ec-gc-excel-enrich"

set "PHP="
if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if "!PHP!"=="" (
    for /f "delims=" %%p in ('where php 2^>nul') do (
        set "PHP=%%p"
        goto :phpdone
    )
)
:phpdone
if "!PHP!"=="" (
    echo No se encontro php.exe. Instale XAMPP en C:\xampp o agregue PHP al PATH.
    pause
    exit /b 1
)

echo.
echo === OneDrive ===
echo Si el Excel esta "solo en la nube", descarguelo primero: clic derecho en el archivo,
echo "Mantener siempre en este dispositivo" o abralo en Excel y guarde en una carpeta local.
echo.
echo === Reanudar ===
echo Si se corto la corrida, mire el ultimo [n/total] en la consola: use "Omitir primeros N"
echo con ese n (ej. vio [1216/2779] y termino ahi, ponga N=1216).
echo Si aparecio "MySQL server has gone away" u "ERROR BD", reanude con N = ultimo OK.
echo.
echo === Si pega comandos a CMD usted mismo ===
echo No pegue cd, set y php en una sola linea sin separador: use varias lineas ^(Enter^) o una linea con
echo espacio-^&-espacio entre cada parte:   cd /d ruta ^& set FECHA_CORTE=2026-03-27 ^& c:\xampp\php\php.exe ...
echo ^(Este lanzador ya ejecuta bien los pasos por dentro; esto es solo si copia comandos a mano.^)
echo.

:menu
echo 1 = Worker ^(S2 + BD + Chat^)
echo 2 = Excel enriquecido ^(dos columnas + Chat^)
choice /c 12 /n /m "Elija 1 o 2: "
if errorlevel 2 goto opt_enrich
if errorlevel 1 goto opt_worker
goto menu

:pick
set "PICK_CANCEL="
set "XLSX="
set "PICK=%TEMP%\ec_launcher_xlsx.txt"
del "%PICK%" 2>nul
powershell -NoProfile -ExecutionPolicy Bypass -Command "Add-Type -AssemblyName System.Windows.Forms; $d=New-Object System.Windows.Forms.OpenFileDialog; $d.Filter='Excel (*.xlsx)|*.xlsx'; $d.Title='Elija el archivo Excel'; if($d.ShowDialog() -ne [System.Windows.Forms.DialogResult]::OK){ exit 1 }; $p=[System.IO.Path]::Combine($env:TEMP,'ec_launcher_xlsx.txt'); [System.IO.File]::WriteAllText($p,$d.FileName)"
if errorlevel 1 (
    echo Cancelado.
    set "PICK_CANCEL=1"
    exit /b 0
)
set /p XLSX=<"%PICK%"
del "%PICK%" 2>nul
if not exist "!XLSX!" (
    echo No se puede leer el archivo.
    set "PICK_CANCEL=1"
    pause
    exit /b 0
)
set "PICK_CANCEL="
exit /b 0

:fecha
for /f "usebackq delims=" %%a in (`powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd"`) do set "DEF_FECHA=%%a"
set "FECHA=!DEF_FECHA!"
set /p "FECHA=Fecha YYYY-MM-DD, Enter o hoy [!DEF_FECHA!]: "
if "!FECHA!"=="" set "FECHA=!DEF_FECHA!"
if /i "!FECHA!"=="hoy" set "FECHA=!DEF_FECHA!"
if /i "!FECHA!"=="today" set "FECHA=!DEF_FECHA!"
goto :eof

:omitir
set "OMIT=0"
set /p "OMIT=Omitir primeros N creditos (0=nuevo): "
if "!OMIT!"=="" set "OMIT=0"
goto :eof

:opt_worker
call :pick
if "!PICK_CANCEL!"=="1" goto menu
call :fecha
call :omitir
set "COL=CREDITO"
set /p "COL=Nombre columna del ID en Excel [CREDITO]: "
if "!COL!"=="" set "COL=CREDITO"
echo.
echo Iniciando worker...
echo.
set "FECHA_CORTE=!FECHA!"
pushd "!WORKER!"
"%PHP%" worker.php --ids-xlsx="!XLSX!" --ids-xlsx-column="!COL!" --omitir-primeros=!OMIT!
set "RC=!ERRORLEVEL!"
popd
echo.
if !RC! neq 0 echo Termino con codigo !RC!.
pause
exit /b !RC!

:opt_enrich
call :pick
if "!PICK_CANCEL!"=="1" goto menu
call :fecha
call :omitir
set "XSOLO="
set /p "XSOLO=Solo columnas S2 sin BD S/N [N]: "
set "EXTRA="
if /i "!XSOLO!"=="S" set "EXTRA=--solo-columnas "
echo.
echo Iniciando enrich_gc_excel...
echo.
set "FECHA_CORTE=!FECHA!"
for %%F in ("!XLSX!") do set "OUTEN=%%~dpnF_enriquecido.xlsx"
pushd "!ENRICH!"
"%PHP%" enrich_gc_excel.php --input="!XLSX!" --chat !EXTRA!--omitir-primeros=!OMIT!
set "RC=!ERRORLEVEL!"
popd
if !RC! equ 0 if exist "!OUTEN!" (
    echo.
    echo Salida: !OUTEN!
    explorer /select,"!OUTEN!"
) else (
    echo.
    if !RC! neq 0 echo Termino con codigo !RC!.
)
pause
exit /b !RC!
