# =====================================================================
#  DOCTOR API verificacion documentos (Sparta Ledger)
# ---------------------------------------------------------------------
#  Diagnostico completo del entorno + auto-fix donde es seguro.
#  Pensado para Windows Server donde NO hay acceso facil a la consola.
#
#  Uso (doble clic): Diagnosticar-API.bat
#  Uso (consola):
#     powershell -NoProfile -ExecutionPolicy Bypass -File doctor-api.ps1
#     powershell -NoProfile -ExecutionPolicy Bypass -File doctor-api.ps1 -Fix
#     powershell -NoProfile -ExecutionPolicy Bypass -File doctor-api.ps1 -Fix -InstallMissing
#
#  Parametros:
#     -Fix              Aplica auto-fix simples (.env, carpeta logs, libera
#                       puerto 8000 si lo tiene un python colgado, etc.)
#     -InstallMissing   Reinstala con pip los paquetes que no se pudieron
#                       importar (uno por uno, log detallado).
#     -KillPort         Mata cualquier proceso en :8000 antes de seguir.
#     -Quiet            Reduce salida a consola (siempre escribe log).
#
#  Salida:
#     - Consola con [OK]/[WARN]/[ERROR]/[FIX]
#     - Log completo en  <API>\logs\doctor-YYYYMMDD-HHMMSS.log
#     - ExitCode 0 = todo OK
#     - ExitCode 1 = hay errores bloqueantes (ver log)
#     - ExitCode 2 = solo avisos (la API puede levantar pero hay algo a revisar)
# =====================================================================

param(
    [switch] $Fix,
    [switch] $InstallMissing,
    [switch] $KillPort,
    [switch] $Quiet
)

$ErrorActionPreference = 'Continue'
$ProgressPreference    = 'SilentlyContinue'

# ---------- Localizar API_DIR ----------
$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
$ApiDir = $here
if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'app\main.py'))) {
    $parent = Split-Path -Parent $here
    if (Test-Path -LiteralPath (Join-Path $parent 'app\main.py')) {
        $ApiDir = $parent
    }
}
$LogsDir = Join-Path $ApiDir 'logs'
if (-not (Test-Path -LiteralPath $LogsDir)) {
    try { New-Item -ItemType Directory -Path $LogsDir -Force | Out-Null } catch {}
}
$stamp   = Get-Date -Format 'yyyyMMdd-HHmmss'
$LogFile = Join-Path $LogsDir "doctor-$stamp.log"

. (Join-Path $here '_resolve_python.ps1')

# ---------- Estado global ----------
$Script:HasErrors   = $false
$Script:HasWarnings = $false
$Script:PythonFreeThreading = $false
$Script:Summary     = New-Object System.Collections.Generic.List[string]
$Script:FixesApplied = New-Object System.Collections.Generic.List[string]
$Script:Recommended  = New-Object System.Collections.Generic.List[string]

# ---------- Helpers ----------
function Out-Log {
    param([string] $Msg, [string] $Color = 'Gray')
    $line = $Msg
    try { Add-Content -LiteralPath $LogFile -Value $line -Encoding UTF8 } catch {}
    if (-not $Quiet) {
        try { Write-Host $line -ForegroundColor $Color } catch { Write-Host $line }
    }
}
function Section { param([string] $T) Out-Log ''; Out-Log ("================ $T ================") 'Cyan' }
function Info    { param([string] $M) Out-Log "       $M" }
function Ok      { param([string] $M) Out-Log "[OK]   $M" 'Green' }
function Warn    { param([string] $M) Out-Log "[WARN] $M" 'Yellow'; $Script:HasWarnings = $true; $null = $Script:Summary.Add("[WARN] $M") }
function Err     { param([string] $M) Out-Log "[ERR ] $M" 'Red';    $Script:HasErrors   = $true; $null = $Script:Summary.Add("[ERR ] $M") }
function Fix     { param([string] $M) Out-Log "[FIX ] $M" 'Magenta'; $null = $Script:FixesApplied.Add($M) }
function Rec     { param([string] $M) $null = $Script:Recommended.Add($M); Out-Log "       -> $M" 'DarkYellow' }

function Invoke-EnsurePipBootstrap {
    # Portable/minimal Python sin Scripts\pip.exe: ensurepip crea pip en Lib\ensurepip.
    param([Parameter(Mandatory)][string]$PyExe, [string[]]$PyArgs = @())
    & $PyExe @PyArgs -m pip --version *> $null
    if ($LASTEXITCODE -eq 0) { return $true }
    if ($Script:PythonFreeThreading) { return $false }
    Out-Log '[bootstrap] pip no operativo; ejecutando ensurepip + pip upgrade...' 'Magenta'
    $null = & $PyExe @PyArgs -m ensurepip --upgrade 2>&1 | ForEach-Object { Out-Log $_ 'DarkGray' }
    $null = & $PyExe @PyArgs -m pip install --upgrade pip 2>&1 | ForEach-Object { Out-Log $_ 'DarkGray' }
    & $PyExe @PyArgs -m pip --version *> $null
    return ($LASTEXITCODE -eq 0)
}

function Invoke-PyCapture {
    # Ejecuta Python con args y captura stdout+stderr a archivos tmp.
    # Devuelve PSCustomObject @{ ExitCode; StdOut; StdErr; All }.
    # Si se pasa -WorkDir, se usa como cwd y ademas se anade a PYTHONPATH para
    # que 'from app.main import app' funcione (el sys.path[0] del script en
    # %TEMP% no incluye la carpeta API por defecto).
    param(
        [Parameter(Mandatory)] [string] $PyExe,
        [string[]] $PyArgs = @(),
        [Parameter(Mandatory)] [string] $Code,
        [string] $WorkDir = $null
    )
    $tmpOut = [System.IO.Path]::GetTempFileName()
    $tmpErr = [System.IO.Path]::GetTempFileName()
    $tmpScript = [System.IO.Path]::GetTempFileName() + '.py'
    $prevPyPath = $env:PYTHONPATH
    try {
        Set-Content -LiteralPath $tmpScript -Value $Code -Encoding UTF8
        if ($WorkDir) {
            if ([string]::IsNullOrEmpty($prevPyPath)) {
                $env:PYTHONPATH = $WorkDir
            } else {
                $env:PYTHONPATH = "$WorkDir;$prevPyPath"
            }
        }
        $argList = @()
        if ($PyArgs.Count -gt 0) { $argList += $PyArgs }
        $argList += $tmpScript
        $spArgs = @{
            FilePath               = $PyExe
            ArgumentList           = $argList
            NoNewWindow            = $true
            Wait                   = $true
            PassThru               = $true
            RedirectStandardOutput = $tmpOut
            RedirectStandardError  = $tmpErr
        }
        if ($WorkDir) { $spArgs.WorkingDirectory = $WorkDir }
        $p = Start-Process @spArgs
        $stdout = (Get-Content -LiteralPath $tmpOut -Raw -ErrorAction SilentlyContinue)
        $stderr = (Get-Content -LiteralPath $tmpErr -Raw -ErrorAction SilentlyContinue)
        return [pscustomobject]@{
            ExitCode = $p.ExitCode
            StdOut   = ($stdout -as [string])
            StdErr   = ($stderr -as [string])
            All      = (($stdout -as [string]) + "`n" + ($stderr -as [string]))
        }
    } finally {
        $env:PYTHONPATH = $prevPyPath
        Remove-Item -LiteralPath $tmpOut, $tmpErr, $tmpScript -ErrorAction SilentlyContinue
    }
}

function Test-PortListening {
    param([int] $Port = 8000)
    try {
        $c = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
        if ($c) { return @($c.OwningProcess | Select-Object -Unique) }
    } catch {}
    $found = @()
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -notmatch 'LISTENING') { continue }
        if ($line -notmatch ":$Port\s") { continue }
        $parts = $line.Trim() -split '\s+'
        if ($parts.Count -lt 1) { continue }
        $last = $parts[$parts.Count - 1]
        if ($last -match '^\d+$') { $found += [int]$last }
    }
    return ($found | Select-Object -Unique)
}

# ---------- Cabecera ----------
Out-Log ('=' * 75) 'Cyan'
Out-Log "  DOCTOR API verificacion documentos  ($(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))" 'Cyan'
Out-Log ('=' * 75) 'Cyan'
Out-Log "  API_DIR : $ApiDir"
Out-Log "  LogFile : $LogFile"
Out-Log "  Modo    : Fix=$Fix  InstallMissing=$InstallMissing  KillPort=$KillPort"

# =====================================================================
# 1) Sistema operativo y PowerShell
# =====================================================================
Section '1. Sistema operativo y PowerShell'
try {
    $os = Get-CimInstance Win32_OperatingSystem -ErrorAction Stop
    Info "OS         : $($os.Caption)  ($($os.Version), build $($os.BuildNumber))"
    Info "Arquitectura: $($os.OSArchitecture)"
} catch { Warn "No se pudo leer Win32_OperatingSystem: $($_.Exception.Message)" }
Info "PowerShell : $($PSVersionTable.PSVersion)"
try {
    $ep = Get-ExecutionPolicy -Scope CurrentUser
    Info "ExecutionPolicy CurrentUser: $ep"
    $epLm = Get-ExecutionPolicy -Scope LocalMachine
    Info "ExecutionPolicy LocalMachine: $epLm"
    if ($ep -eq 'Restricted' -and $epLm -eq 'Restricted') {
        Warn "PowerShell esta en modo Restricted; nuestros .bat usan -ExecutionPolicy Bypass, deberia bastar."
        Rec  "Si algun .ps1 falla con error de politica, ejecute: Set-ExecutionPolicy -Scope CurrentUser RemoteSigned"
    } else { Ok "ExecutionPolicy permite ejecutar scripts." }
} catch { Warn "No se pudo leer ExecutionPolicy: $($_.Exception.Message)" }

# =====================================================================
# 2) Estructura del proyecto
# =====================================================================
Section '2. Estructura del proyecto API'
$mustExist = @(
    'app\main.py',
    'app\api\routes.py',
    'app\core\config.py',
    'requirements.txt'
)
foreach ($rel in $mustExist) {
    $p = Join-Path $ApiDir $rel
    if (Test-Path -LiteralPath $p) { Ok "Existe: $rel" }
    else { Err "FALTA: $rel  (esperado en $p)" }
}
if (-not (Test-Path -LiteralPath $LogsDir)) {
    if ($Fix) { try { New-Item -ItemType Directory -Path $LogsDir -Force | Out-Null; Fix "Creada carpeta logs\" } catch { Err "No se pudo crear logs\: $($_.Exception.Message)" } }
    else { Warn "No existe carpeta logs\ (use -Fix para crearla)" }
} else {
    try {
        $tmpf = Join-Path $LogsDir ".write_test_$([guid]::NewGuid().ToString('N')).tmp"
        'x' | Out-File -LiteralPath $tmpf -Encoding ASCII -ErrorAction Stop
        Remove-Item -LiteralPath $tmpf -ErrorAction SilentlyContinue
        Ok "Carpeta logs\ con permisos de escritura"
    } catch {
        Err "Sin permisos de escritura en logs\ : $($_.Exception.Message)"
        Rec "Ejecute como Administrador o de permisos de escritura al usuario del IIS/servicio sobre $LogsDir"
    }
}

# =====================================================================
# 3) Python disponible (venv > portable en tools/ > py -3 > python)
# =====================================================================
Section '3. Python (interprete a usar)'
$pyExe   = $null
$pyArgs  = @()
$pySrc   = ''
$resolved = Resolve-SpartaApiPython -ApiDir $ApiDir
$venvStale = $false
$venvPathChk = Join-Path $ApiDir 'venv\Scripts\python.exe'
if ((Test-Path -LiteralPath $venvPathChk) -and -not (Test-SpartaPythonViable -PythonExe $venvPathChk -ApiDir $ApiDir)) {
    $venvStale = $true
}
if ($resolved) {
    $pyExe = $resolved.Exe
    $pyArgs = $resolved.Args
    $pySrc = $resolved.Source
    Ok "Python a usar: $pySrc -> $pyExe"
    if ($venvStale -and $pySrc -ne 'venv') {
        Warn 'El venv antiguo (Python incompatible / free-thread) se ignora. Al ejecutar instalacion /VENV o el boton API se borrara venv y se creara bien con Python 3.12 portable si existe.'
    }
}
if (-not $pyExe) {
    Err "No hay Python disponible (ni venv, ni portable en tools\, ni py -3, ni python)."
    Rec 'Opcion A — Sin instalador ni PATH: descomprima Python 3.12 64-bit en API\tools\PythonPortable\ (python.exe ahi).'
    Rec 'Opcion B — Una sola linea con ruta completa del exe en  launcher\PYTHON_EXE.txt  (ej. D:\sitio\...\python.exe).'
    Rec 'Opcion C — Servidor convencional: Python desde python.org y PATH / py launcher.'
} elseif ($pyExe) {
    $pyVer = (& $pyExe @pyArgs -c 'import sys,platform; print(platform.python_version()+"|"+sys.executable+"|"+platform.architecture()[0])' 2>$null)
    if ($pyVer) {
        $parts = $pyVer.Split('|')
        Info "Version    : $($parts[0])"
        Info "Ejecutable : $($parts[1])"
        Info "Arquitectura: $($parts[2])"
        $verNum = $parts[0]
        $major,$minor,$null = $verNum.Split('.')
        $minor = [int]$minor
        if ([int]$major -ne 3) { Err "Python 2.x detectado, no soportado." }
        elseif ($minor -ge 13) {
            Warn ('Python 3.{0} es muy nuevo: paquetes con C/Rust (pdf417decoder, pyzbar, torch, pydantic-core) pueden no tener wheel y exigir compilador (Visual Studio Build Tools).' -f $minor)
            Rec  'Si el instalar-agente.bat falla compilando, instale Python 3.12 (64-bit) y vuelva a ejecutar instalar-agente.bat /VENV'
        }
        elseif ($minor -lt 10) { Warn ('Python 3.{0} es viejo; recomendado 3.11 o 3.12.' -f $minor) }
        else { Ok 'Version de Python adecuada.' }
        if ($parts[2] -ne '64bit') { Warn 'Python NO es 64-bit; varias dependencias (torch, opencv) requieren x64.' }
    } else {
        Warn "No se pudo obtener version de Python."
    }

    $chkPy = Join-Path $here '_check_standard_python.py'
    if (Test-Path -LiteralPath $chkPy) {
        $tmpO = [System.IO.Path]::GetTempFileName()
        $tmpE = [System.IO.Path]::GetTempFileName()
        $chkArgs = @()
        if ($pyArgs.Count -gt 0) { $chkArgs += $pyArgs }
        $chkArgs += $chkPy
        $pChk = Start-Process -FilePath $pyExe -ArgumentList $chkArgs -WorkingDirectory $ApiDir `
            -Wait -PassThru -NoNewWindow `
            -RedirectStandardOutput $tmpO -RedirectStandardError $tmpE
        $chkOut = (Get-Content -LiteralPath $tmpO -Raw -ErrorAction SilentlyContinue) + "`n" + (Get-Content -LiteralPath $tmpE -Raw -ErrorAction SilentlyContinue)
        Remove-Item -LiteralPath $tmpO, $tmpE -ErrorAction SilentlyContinue
        if ($pChk.ExitCode -eq 2) {
            $Script:PythonFreeThreading = $true
            Err "Python FREE-THREADING (sin GIL / Py_GIL_DISABLED): incompatible con compilacion wheel de PyMuPDF y otros."
            foreach ($ln in (($chkOut -split "`r?`n") | Where-Object { $_.Trim() -ne '' })) {
                Out-Log "       $ln" 'Red'
            }
            Rec 'Instale Python 3.12 64-bit ESTANDAR desde python.org; NO use la opcion free-thread ni python*t.exe.'
            Rec "Elimine backend\API\venv y ejecute launcher\instalar-agente.bat /VENV"
        } else {
            Ok "Python no es la variante free-threading conocida incompatible con pymupdf."
        }
    }
}

# =====================================================================
# 4) pip funciona
# =====================================================================
Section '4. pip'
if ($pyExe) {
    & $pyExe @pyArgs -m pip --version *> $null
    $pipWorks = ($LASTEXITCODE -eq 0)
    if (-not $pipWorks -and ($Fix -or $InstallMissing) -and -not $Script:PythonFreeThreading) {
        if (Invoke-EnsurePipBootstrap -PyExe $pyExe -PyArgs $pyArgs) {
            $pipWorks = $true
            Fix 'pip quedo operativo tras ensurepip / upgrade pip.'
        }
    }
    if ($pipWorks) {
        $pipv = (& $pyExe @pyArgs -m pip --version 2>$null)
        Ok "pip OK -> $pipv"
    } else {
        Err "pip no esta operativo en este Python."
        $pipPrefix = "`"$pyExe`""
        if ($pyArgs.Count -gt 0) { $pipPrefix += ' ' + ($pyArgs -join ' ') }
        Rec  "Ejecute: $pipPrefix -m ensurepip --upgrade   y luego   $pipPrefix -m pip install --upgrade pip"
    }
} else { Warn "Sin Python; se omite check de pip." }

# =====================================================================
# 5) Paquetes Python (smoke import)
# =====================================================================
Section '5. Paquetes Python (smoke import)'
# Lista: (modulo_python, nombre_pip)
$pkgs = @(
    @('fastapi',                 'fastapi'),
    @('uvicorn',                 'uvicorn[standard]'),
    @('multipart',               'python-multipart'),
    @('pydantic',                'pydantic'),
    @('pydantic_settings',       'pydantic-settings'),
    @('cv2',                     'opencv-python-headless'),
    @('PIL',                     'Pillow'),
    @('skimage',                 'scikit-image'),
    @('numpy',                   'numpy'),
    @('scipy',                   'scipy'),
    @('pytesseract',             'pytesseract'),
    @('rapidocr_onnxruntime',    'rapidocr-onnxruntime'),
    @('fitz',                    'pymupdf'),
    @('torch',                   'torch'),
    @('torchvision',             'torchvision'),
    @('timm',                    'timm'),
    @('pyzbar',                  'pyzbar'),
    @('pdf417decoder',           'pdf417decoder'),
    @('qrcode',                  'qrcode'),
    @('piexif',                  'piexif'),
    @('hachoir',                 'hachoir'),
    @('slowapi',                 'slowapi'),
    @('loguru',                  'loguru'),
    @('dotenv',                  'python-dotenv')
)
$missing = @()
$brokenImport = @()
if ($pyExe) {
    foreach ($p in $pkgs) {
        $mod = $p[0]; $pip = $p[1]
        $code = @"
import importlib, sys
try:
    m = importlib.import_module('$mod')
    v = getattr(m, '__version__', None) or getattr(m, 'VERSION', None) or '?'
    print('OK|' + str(v))
except Exception as e:
    print('ERR|' + type(e).__name__ + ': ' + str(e)[:300])
"@
        $r = Invoke-PyCapture -PyExe $pyExe -PyArgs $pyArgs -Code $code
        # Buscar la unica linea que empieza por OK| o ERR| (descartando warnings stderr)
        $line = $null
        foreach ($candidate in (($r.StdOut + "`n" + $r.StdErr) -split "`r?`n")) {
            if ($candidate -match '^(OK|ERR)\|') { $line = $candidate; break }
        }
        if (-not $line) { $line = "ERR|sin salida (exit $($r.ExitCode))" }
        if ($line -like 'OK|*') {
            $ver = $line.Substring(3)
            Ok ("{0,-24}  {1}" -f $mod, $ver)
        } else {
            $detail = ($line -replace '^ERR\|','')
            if ($detail -match 'No module named') {
                Err ("Falta paquete: $mod  (pip install $pip)  --  $detail")
                $missing += $pip
            } else {
                Err ("Import fallo: $mod  ->  $detail")
                $brokenImport += [pscustomobject]@{ Module=$mod; Pip=$pip; Detail=$detail }
            }
        }
    }
    if ($missing.Count -gt 0) {
        $pfx = "`"$pyExe`""
        if ($pyArgs.Count -gt 0) { $pfx += ' ' + ($pyArgs -join ' ') }
        Rec ("Instale: $pfx -m pip install " + ($missing -join ' '))
    }
    if ($brokenImport.Count -gt 0) {
        Rec "Algun paquete esta instalado pero falla al importar (DLL ausente, version incompatible, archivo corrupto)."
        foreach ($b in $brokenImport) {
            if ($b.Module -eq 'pyzbar')        { Rec "pyzbar: copie libzbar-64.dll/libiconv.dll a System32 o instale 'zbar' para Windows; el wheel de pyzbar no incluye la DLL." }
            elseif ($b.Module -eq 'cv2')       { Rec "opencv: instale Visual C++ Redistributable 2015-2022 x64 (vc_redist.x64.exe de Microsoft)." }
            elseif ($b.Module -eq 'pdf417decoder') { Rec "pdf417decoder: requiere wheel para su Python; con 3.13/3.14 instale Build Tools o cambie a Python 3.12." }
            elseif ($b.Module -eq 'torch')     { Rec "torch: el wheel ocupa ~600MB; en Python muy nuevo no hay wheel y necesita compilarse. Cambie a 3.12." }
            elseif ($b.Module -eq 'pytesseract' -and $b.Detail -match "find_loader") { Rec "pytesseract: pip install --upgrade pytesseract>=0.3.13 (la 0.3.10 falla en Python 3.12+)." }
        }
    }
} else { Warn "Sin Python; se omite check de paquetes." }

# =====================================================================
# 6) Smoke import de la app (esto reproduce el error real de uvicorn)
# =====================================================================
Section '6. Smoke import de la app (from app.main import app)'
if ($pyExe) {
    # Reusar el script permanente del launcher para tener una sola fuente
    # de verdad sobre como se importa la app.
    $smokePy = Join-Path $here '_smoke_import.py'
    if (Test-Path -LiteralPath $smokePy) {
        $tmpO = [System.IO.Path]::GetTempFileName()
        $tmpE = [System.IO.Path]::GetTempFileName()
        $sArgs = @()
        if ($pyArgs.Count -gt 0) { $sArgs += $pyArgs }
        $sArgs += $smokePy
        $p = Start-Process -FilePath $pyExe -ArgumentList $sArgs -WorkingDirectory $ApiDir `
             -NoNewWindow -Wait -PassThru `
             -RedirectStandardOutput $tmpO -RedirectStandardError $tmpE
        $r = [pscustomobject]@{
            ExitCode = $p.ExitCode
            StdOut   = (Get-Content -LiteralPath $tmpO -Raw -ErrorAction SilentlyContinue)
            StdErr   = (Get-Content -LiteralPath $tmpE -Raw -ErrorAction SilentlyContinue)
        }
        Remove-Item -LiteralPath $tmpO, $tmpE -ErrorAction SilentlyContinue
    } else {
        $code = @"
import traceback, sys
try:
    from app.main import app
except SystemExit:
    raise
except BaseException:
    print('SMOKE_ERR')
    traceback.print_exc()
    sys.exit(1)
else:
    print('SMOKE_OK')
    sys.exit(0)
"@
        $r = Invoke-PyCapture -PyExe $pyExe -PyArgs $pyArgs -Code $code -WorkDir $ApiDir
    }
    $joined = ($r.StdOut + "`n" + $r.StdErr)
    Add-Content -LiteralPath $LogFile -Value '----- smoke import output (stdout) -----' -Encoding UTF8
    Add-Content -LiteralPath $LogFile -Value ($r.StdOut) -Encoding UTF8
    Add-Content -LiteralPath $LogFile -Value '----- smoke import output (stderr) -----' -Encoding UTF8
    Add-Content -LiteralPath $LogFile -Value ($r.StdErr) -Encoding UTF8
    if ($joined -match 'SMOKE_OK') {
        Ok "La app se importa correctamente (uvicorn deberia poder arrancarla)."
    } else {
        Err "La app NO se puede importar. Este es el error real que hace que uvicorn no arranque."
        $lines = ($joined -split "`r?`n") | Where-Object { $_ -ne '' } | Select-Object -Last 30
        foreach ($l in $lines) { Out-Log "       $l" 'Red' }
        Rec "Lea las lineas anteriores; el problema esta en el import. Soluciones tipicas:"
        Rec "  - Falta paquete -> pip install ese paquete"
        Rec "  - 'cannot import name find_loader from pkgutil' -> pip install --upgrade pytesseract"
        Rec "  - 'DLL load failed' -> instale Visual C++ Redistributable x64"
        Rec "  - 'libzbar' -> instale dll de zbar para Windows"
    }
} else { Warn "Sin Python; se omite smoke import." }

# =====================================================================
# 7) Tesseract OCR
# =====================================================================
Section '7. Tesseract OCR'
$tessCandidates = @(
    (Join-Path $ApiDir 'tools\tesseract.exe'),
    (Join-Path $ApiDir 'tools\Tesseract-OCR\tesseract.exe'),
    'C:\Program Files\Tesseract-OCR\tesseract.exe',
    'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
    "$env:LOCALAPPDATA\Programs\Tesseract-OCR\tesseract.exe"
)
$tessFound = $tessCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $tessFound) {
    try { $cmd = (Get-Command tesseract -ErrorAction Stop).Source; if ($cmd) { $tessFound = $cmd } } catch {}
}
if ($tessFound) {
    Ok "Tesseract: $tessFound"
    try { $tv = (& $tessFound --version 2>&1 | Select-Object -First 1); Info "Version    : $tv" } catch {}
    $tessParent = Split-Path -Parent $tessFound
    $tessdataDir = Join-Path $tessParent 'tessdata'
    if (-not (Test-Path -LiteralPath $tessdataDir)) {
        $tessdataDir = Join-Path $ApiDir 'tools\tessdata'
    }
    if (Test-Path -LiteralPath (Join-Path $tessdataDir 'spa.traineddata')) {
        Ok "tessdata   : spa.traineddata presente ($tessdataDir)"
    } else {
        Warn "Falta spa.traineddata en tessdata (la API usa idioma spa+eng). Coloque spa.traineddata en: $tessdataDir"
        Rec  "Descarga: https://github.com/tesseract-ocr/tessdata_best/raw/main/spa.traineddata"
    }
    # Asegurar TESSERACT_CMD en .env
    $envFile = Join-Path $ApiDir '.env'
    if (Test-Path -LiteralPath $envFile) {
        $envTxt = Get-Content -LiteralPath $envFile -Raw
        if ($envTxt -notmatch '(?m)^\s*TESSERACT_CMD\s*=') {
            if ($Fix) {
                Add-Content -LiteralPath $envFile -Value ("`r`nTESSERACT_CMD=" + $tessFound) -Encoding UTF8
                Fix "Agregado TESSERACT_CMD=$tessFound al .env"
            } else {
                Warn ".env no define TESSERACT_CMD (use -Fix para agregarlo automaticamente)."
            }
        }
    }
} else {
    Warn "Tesseract no encontrado. La API arranca, pero la verificacion OCR fallara."
    Rec  "Coloque una copia portable en: $(Join-Path $ApiDir 'tools\tesseract.exe') con carpeta tessdata al lado,"
    Rec  "o instale Tesseract: https://github.com/UB-Mannheim/tesseract/wiki  ('C:\Program Files\Tesseract-OCR\')."
}

# =====================================================================
# 8) Visual C++ Redistributable
# =====================================================================
Section '8. Visual C++ Redistributable x64 (necesario para opencv/pyzbar/torch)'
$vcOk = $false
try {
    $regs = @(
        'HKLM:\SOFTWARE\Microsoft\VisualStudio\14.0\VC\Runtimes\x64',
        'HKLM:\SOFTWARE\WOW6432Node\Microsoft\VisualStudio\14.0\VC\Runtimes\x64'
    )
    foreach ($r in $regs) {
        if (Test-Path $r) {
            $i = Get-ItemProperty -Path $r -ErrorAction SilentlyContinue
            if ($i -and $i.Installed -eq 1) {
                $vcOk = $true
                Ok "VC++ Redist x64 instalado ($($i.Major).$($i.Minor).$($i.Bld))"
                break
            }
        }
    }
} catch {}
if (-not $vcOk) {
    Warn "No se detecto Visual C++ Redistributable x64 (puede causar 'DLL load failed' al importar opencv/pyzbar/torch)."
    Rec  "Descargue e instale: https://aka.ms/vs/17/release/vc_redist.x64.exe"
}

# =====================================================================
# 9) zbar dll para pyzbar
# =====================================================================
Section '9. zbar DLL para pyzbar'
$dllPaths = @(
    "$env:WINDIR\System32\libzbar-64.dll",
    "$env:WINDIR\System32\libzbar-0.dll",
    "$env:WINDIR\SysWOW64\libzbar-64.dll"
)
$zbarFound = $dllPaths | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if ($zbarFound) {
    Ok "zbar dll: $zbarFound"
} else {
    if ($pyExe) {
        & $pyExe @pyArgs -c "from pyzbar.pyzbar import decode" *> $null
        if ($LASTEXITCODE -eq 0) {
            Ok "pyzbar funciona (la dll viaja con el wheel)."
        } else {
            Warn "pyzbar no encuentra libzbar. La verificacion de QR/PDF417 fallara."
            Rec  "Descargue 'zbar' para Windows o copie libzbar-64.dll a $env:WINDIR\System32"
        }
    } else { Warn "Sin Python; no se pudo probar pyzbar." }
}

# =====================================================================
# 10) .env
# =====================================================================
Section '10. Archivo .env'
$envFile = Join-Path $ApiDir '.env'
$envEx   = Join-Path $ApiDir '.env.example'
if (-not (Test-Path -LiteralPath $envFile)) {
    if (Test-Path -LiteralPath $envEx) {
        if ($Fix) {
            Copy-Item -LiteralPath $envEx -Destination $envFile -Force
            Fix "Creado .env desde .env.example"
        } else {
            Warn "No existe .env (puede arrancar con defaults). Use -Fix para copiarlo desde .env.example."
        }
    } else {
        Warn "No existe ni .env ni .env.example. La API arranca con defaults internos."
    }
} else {
    Ok ".env presente."
    $envTxt = Get-Content -LiteralPath $envFile -Raw
    if ($envTxt -match 'CAMBIA_ESTO') {
        Warn ".env contiene 'CAMBIA_ESTO' (SECRET_KEY/MASTER_API_KEY); en produccion definalas con valores reales."
    }
    if ($envTxt -match '(?m)^\s*TEMP_UPLOAD_DIR\s*=\s*/tmp/') {
        Warn "TEMP_UPLOAD_DIR apunta a /tmp/... (ruta Linux). En Windows pongalo en una carpeta valida (ej. C:\Temp\doc_verificacion)."
    }
}

# =====================================================================
# 11) Puerto 8000
# =====================================================================
Section '11. Puerto 8000'
$pidsOnPort = Test-PortListening -Port 8000
if ($pidsOnPort -and $pidsOnPort.Count -gt 0) {
    foreach ($pidPort in $pidsOnPort) {
        $procName = ''
        try { $procName = (Get-Process -Id $pidPort -ErrorAction Stop).ProcessName } catch {}
        Info ("Puerto 8000 ocupado por PID $pidPort  ($procName)")
    }
    if ($KillPort -or $Fix) {
        foreach ($pidPort in $pidsOnPort) {
            try { Stop-Process -Id $pidPort -Force -ErrorAction Stop; Fix "Proceso PID $pidPort terminado (puerto 8000 liberado)." } catch { Err "No se pudo matar PID $pidPort : $($_.Exception.Message)" }
        }
    } else {
        Warn "Puerto 8000 ya esta en uso. Use -KillPort (o -Fix) para liberarlo."
        Rec "Si es uvicorn antiguo: ejecute launcher\\cerrar-agente.bat antes de arrancar."
    }
} else {
    Ok "Puerto 8000 libre."
}

# =====================================================================
# 12) Firewall (informativo)
# =====================================================================
Section '12. Firewall de Windows (informativo)'
try {
    $fwRule = Get-NetFirewallRule -DisplayName 'API verificacion documentos 8000' -ErrorAction SilentlyContinue
    if ($fwRule) {
        Ok "Regla de firewall encontrada para 8000."
    } else {
        Warn "No hay regla de firewall para puerto 8000 (no afecta arranque local; puede afectar acceso desde otra maquina)."
        Rec  "En PowerShell admin:  New-NetFirewallRule -DisplayName 'API verificacion documentos 8000' -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow"
    }
} catch { Info "No se pudo consultar reglas de firewall (no es bloqueante)." }

# =====================================================================
# 13) Test localhost (si la API ya esta arriba)
# =====================================================================
Section '13. Test HTTP a localhost:8000 (si esta arriba)'
try {
    $resp = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/docs' -UseBasicParsing -TimeoutSec 4 -ErrorAction Stop
    if ($resp.StatusCode -eq 200) { Ok "GET /docs respondio 200 (API operativa)." }
    else { Warn "GET /docs respondio $($resp.StatusCode)" }
} catch {
    Info "API no responde en /docs (normal si aun no se ha arrancado)."
}

# =====================================================================
# 14) Auto-fix: instalar paquetes faltantes
# =====================================================================
if ($InstallMissing -and $Script:PythonFreeThreading) {
    Section '14. Auto-fix: instalando paquetes faltantes'
    Warn "Se omite auto-instalacion con pip porque el Python actual es FREE-THREADING (python*t)."
    Rec  "Primero use Python 3.12 estandar (portable en API\\tools\\PythonPortable\\ o PYTHON_EXE.txt), recree venv y luego reintente."
} elseif ($InstallMissing -and $pyExe -and $missing.Count -gt 0) {
    Section '14. Auto-fix: instalando paquetes faltantes'
    & $pyExe @pyArgs -m pip --version *> $null
    if ($LASTEXITCODE -ne 0 -and -not $Script:PythonFreeThreading) {
        if (-not (Invoke-EnsurePipBootstrap -PyExe $pyExe -PyArgs $pyArgs)) {
            Err 'pip sigue sin funcionar; no se pueden instalar paquetes. Ejecute instalar-agente.bat o ensurepip manualmente.'
        } else {
            Fix 'pip operativo antes de pip install (ensurepip).'
        }
    }
    & $pyExe @pyArgs -m pip --version *> $null
    if ($LASTEXITCODE -ne 0) {
        Info 'Omitiendo instalacion por pip (no disponible).'
    } else {
    $pipLog = Join-Path $LogsDir "doctor-pip-$stamp.log"
    foreach ($pkg in $missing) {
        Out-Log "  pip install $pkg ..." 'Magenta'
        $proc = Start-Process -FilePath $pyExe -ArgumentList (@($pyArgs) + @('-m','pip','install','--no-input',$pkg)) `
                -NoNewWindow -Wait -PassThru `
                -RedirectStandardOutput "$pipLog.out" -RedirectStandardError "$pipLog.err"
        Get-Content "$pipLog.out" -ErrorAction SilentlyContinue | Add-Content -LiteralPath $pipLog -Encoding UTF8
        Get-Content "$pipLog.err" -ErrorAction SilentlyContinue | Add-Content -LiteralPath $pipLog -Encoding UTF8
        Remove-Item "$pipLog.out","$pipLog.err" -ErrorAction SilentlyContinue
        if ($proc.ExitCode -eq 0) { Fix "Instalado $pkg" }
        else { Err "Fallo pip install $pkg (vea $pipLog)" }
    }
    Info "Log de pip: $pipLog"
    }
}

# =====================================================================
# RESUMEN FINAL
# =====================================================================
Out-Log ''
Out-Log ('=' * 75) 'Cyan'
Out-Log '  RESUMEN' 'Cyan'
Out-Log ('=' * 75) 'Cyan'

if ($Script:FixesApplied.Count -gt 0) {
    Out-Log "Auto-fix aplicados:" 'Magenta'
    foreach ($f in $Script:FixesApplied) { Out-Log "  + $f" 'Magenta' }
}
if ($Script:Summary.Count -gt 0) {
    Out-Log ''
    Out-Log "Hallazgos:" 'Yellow'
    foreach ($s in $Script:Summary) { Out-Log "  $s" }
}
if ($Script:Recommended.Count -gt 0) {
    Out-Log ''
    Out-Log "Acciones recomendadas:" 'DarkYellow'
    $i = 1
    foreach ($r in ($Script:Recommended | Select-Object -Unique)) {
        Out-Log ("  {0,2}) {1}" -f $i, $r) 'DarkYellow'
        $i++
    }
}
Out-Log ''
Out-Log "Log completo: $LogFile" 'Cyan'

if ($Script:HasErrors) {
    Out-Log ''
    Out-Log '>>> Hay ERRORES bloqueantes. Resuelvalos antes de arrancar la API.' 'Red'
    exit 1
}
if ($Script:HasWarnings) {
    Out-Log ''
    Out-Log '>>> Sin errores bloqueantes, pero hay AVISOS. Revise el resumen.' 'Yellow'
    exit 2
}
Out-Log ''
Out-Log '>>> Todo en orden. Puede arrancar la API.' 'Green'
exit 0
