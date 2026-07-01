# =====================================================================
#  bootstrap-python.ps1
#  Repara / instala Python portable en backend\API\tools\PythonPortable
#  cuando esta incompleto (falta _socket.pyd, pip no funciona, etc.).
#
#  Estrategia:
#    1) Descargar el "embeddable amd64" oficial de python.org (zip).
#    2) Extraerlo encima de tools\PythonPortable\.
#    3) Habilitar 'import site' en el archivo pythonNNN._pth (necesario
#       para que pip pueda encontrar Lib\site-packages).
#    4) Descargar get-pip.py y ejecutarlo para instalar pip.
#    5) Verificar 'python -m pip --version'.
#
#  Uso:
#     powershell -NoProfile -ExecutionPolicy Bypass -File bootstrap-python.ps1
#     powershell -NoProfile -ExecutionPolicy Bypass -File bootstrap-python.ps1 -Force
#     powershell -NoProfile -ExecutionPolicy Bypass -File bootstrap-python.ps1 -Version 3.12.10
#
#  Salida:
#     0  -> Python operativo con pip
#     1  -> Algo fallo (ver log)
#
#  Esto NO instala fastapi/torch/etc.; eso lo hace instalar-agente.bat
#  despues, una vez pip funciona.
# =====================================================================

param(
    [switch] $Force,
    [string] $Version = '3.12.10',
    [string] $LogFile,
    [switch] $Quiet
)

$ErrorActionPreference = 'Stop'
$ProgressPreference    = 'SilentlyContinue'

# ---------- Localizar API_DIR ----------
$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
$ApiDir = $here
if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'app\main.py'))) {
    $parent = Split-Path -Parent $here
    if (Test-Path -LiteralPath (Join-Path $parent 'app\main.py')) { $ApiDir = $parent }
}
$LogsDir = Join-Path ([System.IO.Path]::GetTempPath()) 'sparta___SPARTA_SECRET_REDACTED___api_logs'
if (-not (Test-Path -LiteralPath $LogsDir)) {
    try { New-Item -ItemType Directory -Path $LogsDir -Force | Out-Null } catch {}
}
if (-not $LogFile) {
    $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
    $LogFile = Join-Path $LogsDir "bootstrap-python-$stamp.log"
}

function Log {
    param([string] $Msg, [string] $Color = 'Gray')
    try { Add-Content -LiteralPath $LogFile -Value $Msg -Encoding UTF8 } catch {}
    if (-not $Quiet) { try { Write-Host $Msg -ForegroundColor $Color } catch { Write-Host $Msg } }
}

Log ('=' * 70) 'Cyan'
Log "  BOOTSTRAP Python portable  ($(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))" 'Cyan'
Log ('=' * 70) 'Cyan'
Log "  API_DIR : $ApiDir"
Log "  LogFile : $LogFile"
Log "  Version : $Version"
Log ''

$Dest    = Join-Path $ApiDir 'tools\PythonPortable'
$ToolsDir = Join-Path $ApiDir 'tools'
if (-not (Test-Path -LiteralPath $ToolsDir)) {
    try { New-Item -ItemType Directory -Path $ToolsDir -Force | Out-Null } catch {}
}

# ---------- 1) Comprobar si ya esta sano ----------
function Test-PythonSano {
    param([string]$Exe)
    if (-not (Test-Path -LiteralPath $Exe)) { return $false }
    try {
        $out = & $Exe -c "import _socket, ssl, ctypes, hashlib; print('PYOK')" 2>&1
        if ($LASTEXITCODE -ne 0) { return $false }
        return ([string]::Join("`n", @($out)) -match 'PYOK')
    } catch {
        return $false
    }
}
function Test-PipSano {
    param([string]$Exe)
    if (-not (Test-Path -LiteralPath $Exe)) { return $false }
    try {
        & $Exe -m pip --version *> $null
        return ($LASTEXITCODE -eq 0)
    } catch { return $false }
}

$pyExe = Join-Path $Dest 'python.exe'

if (-not $Force -and (Test-PythonSano -Exe $pyExe) -and (Test-PipSano -Exe $pyExe)) {
    Log "[OK] Python portable ya esta operativo (con _socket y pip). Nada que hacer." 'Green'
    Log "     $pyExe"
    exit 0
}

if (Test-Path -LiteralPath $pyExe) {
    Log "[i] Python portable presente pero incompleto / pip no funciona. Se va a re-instalar." 'Yellow'
} else {
    Log "[i] Python portable no existe. Instalando desde python.org embeddable..." 'Yellow'
}

# ---------- 2) Descarga embeddable ZIP ----------
$zipUrl  = "https://www.python.org/ftp/python/$Version/python-$Version-embed-amd64.zip"
$zipFile = Join-Path $env:TEMP "python-$Version-embed-amd64.zip"

# Forzar TLS 1.2 (necesario en Server 2019 con .NET viejo)
try { [Net.ServicePointManager]::SecurityProtocol = [Net.ServicePointManager]::SecurityProtocol -bor [Net.SecurityProtocolType]::Tls12 } catch {}

Log "[1/4] Descargando $zipUrl ..." 'Cyan'
try {
    if (Test-Path -LiteralPath $zipFile) { Remove-Item -LiteralPath $zipFile -Force -ErrorAction SilentlyContinue }
    Invoke-WebRequest -Uri $zipUrl -OutFile $zipFile -UseBasicParsing -TimeoutSec 300
} catch {
    Log "[ERR] Fallo descargando Python embeddable: $($_.Exception.Message)" 'Red'
    Log "      Verifique acceso saliente a python.org desde el servidor."
    exit 1
}
$zipSize = (Get-Item -LiteralPath $zipFile).Length
Log "      Descargado: $zipFile  ($zipSize bytes)" 'DarkGray'

# ---------- 3) Extraer al destino ----------
Log "[2/4] Extrayendo a $Dest ..." 'Cyan'
try {
    if (-not (Test-Path -LiteralPath $Dest)) {
        New-Item -ItemType Directory -Path $Dest -Force | Out-Null
    }
    # Si quedo un python roto, intentar borrarlo todo. Si esta en uso, reintentar.
    if ($Force -or -not (Test-PythonSano -Exe $pyExe)) {
        $maxIntentos = 3
        for ($i=1; $i -le $maxIntentos; $i++) {
            try {
                Get-ChildItem -LiteralPath $Dest -Force -ErrorAction SilentlyContinue |
                    Remove-Item -Recurse -Force -ErrorAction Stop
                break
            } catch {
                Log "      Intento $i de borrar destino fallo: $($_.Exception.Message)" 'DarkYellow'
                Start-Sleep -Seconds 2
                if ($i -eq $maxIntentos) {
                    Log "[ERR] No se puede limpiar $Dest. Cierre procesos Python y reintente." 'Red'
                    exit 1
                }
            }
        }
    }
    Add-Type -AssemblyName System.IO.Compression.FileSystem -ErrorAction SilentlyContinue
    [System.IO.Compression.ZipFile]::ExtractToDirectory($zipFile, $Dest)
} catch {
    Log "[ERR] Fallo extrayendo ZIP: $($_.Exception.Message)" 'Red'
    exit 1
}

# Verificar que aparezca python.exe
if (-not (Test-Path -LiteralPath $pyExe)) {
    Log "[ERR] Tras extraer no existe $pyExe" 'Red'
    exit 1
}

# Verificar que ahora _socket si carga
if (-not (Test-PythonSano -Exe $pyExe)) {
    Log "[ERR] El Python recien extraido NO carga _socket. ZIP corrupto?" 'Red'
    exit 1
}
Log "      OK: python.exe operativo, _socket carga." 'Green'

# ---------- 4) Habilitar 'import site' en _pth ----------
# El embeddable trae pythonNNN._pth con 'import site' COMENTADO. Sin eso pip
# no puede inicializarse porque no busca Lib\site-packages.
$pthFiles = Get-ChildItem -LiteralPath $Dest -Filter 'python*._pth' -ErrorAction SilentlyContinue
foreach ($pth in $pthFiles) {
    try {
        $content = Get-Content -LiteralPath $pth.FullName -Raw -Encoding UTF8
        $orig = $content
        # Descomentar 'import site'
        $content = $content -replace '(?m)^\s*#\s*import\s+site\s*$', 'import site'
        if ($content -notmatch '(?m)^\s*import\s+site\s*$') {
            $content = $content.TrimEnd("`r","`n") + "`r`nimport site`r`n"
        }
        # Asegurar que Lib y Lib\site-packages esten en el path (algunos
        # embeddables no los traen).
        if ($content -notmatch '(?m)^Lib\s*$')                { $content = "Lib`r`n" + $content }
        if ($content -notmatch '(?m)^Lib\\site-packages\s*$') { $content = "Lib\site-packages`r`n" + $content }
        if ($content -ne $orig) {
            Set-Content -LiteralPath $pth.FullName -Value $content -Encoding UTF8
            Log "      Editado: $($pth.Name) (import site habilitado)" 'DarkGray'
        }
    } catch {
        Log "[WARN] No se pudo editar $($pth.FullName): $($_.Exception.Message)" 'Yellow'
    }
}

# Crear carpetas Lib\site-packages y Scripts si no existen
foreach ($sub in @('Lib','Lib\site-packages','Scripts','DLLs')) {
    $p = Join-Path $Dest $sub
    if (-not (Test-Path -LiteralPath $p)) {
        try { New-Item -ItemType Directory -Path $p -Force | Out-Null } catch {}
    }
}

# ---------- 5) Instalar pip via get-pip.py ----------
Log "[3/4] Descargando get-pip.py ..." 'Cyan'
$getPip = Join-Path $env:TEMP 'get-pip.py'
try {
    if (Test-Path -LiteralPath $getPip) { Remove-Item -LiteralPath $getPip -Force -ErrorAction SilentlyContinue }
    Invoke-WebRequest -Uri 'https://bootstrap.pypa.io/get-pip.py' -OutFile $getPip -UseBasicParsing -TimeoutSec 120
} catch {
    Log "[ERR] No se pudo descargar get-pip.py: $($_.Exception.Message)" 'Red'
    exit 1
}

Log "[4/4] Ejecutando get-pip.py (instala pip + setuptools + wheel) ..." 'Cyan'
$pipLog = Join-Path $LogsDir ("bootstrap-getpip-" + (Get-Date -Format 'yyyyMMdd-HHmmss') + ".log")
try {
    $proc = Start-Process -FilePath $pyExe `
        -ArgumentList @($getPip, '--no-warn-script-location') `
        -WorkingDirectory $Dest -Wait -PassThru -NoNewWindow `
        -RedirectStandardOutput "$pipLog.out" `
        -RedirectStandardError  "$pipLog.err"
    Get-Content "$pipLog.out" -ErrorAction SilentlyContinue | Add-Content -LiteralPath $pipLog -Encoding UTF8
    Get-Content "$pipLog.err" -ErrorAction SilentlyContinue | Add-Content -LiteralPath $pipLog -Encoding UTF8
    Remove-Item "$pipLog.out","$pipLog.err" -ErrorAction SilentlyContinue
    if ($proc.ExitCode -ne 0) {
        Log "[ERR] get-pip.py salio con exit $($proc.ExitCode). Log: $pipLog" 'Red'
        if (Test-Path -LiteralPath $pipLog) {
            Log '----- Ultimas lineas de get-pip.py -----' 'DarkYellow'
            Get-Content -LiteralPath $pipLog -Tail 40 | ForEach-Object { Log "      $_" 'DarkGray' }
        }
        exit 1
    }
} catch {
    Log "[ERR] Fallo ejecutando get-pip.py: $($_.Exception.Message)" 'Red'
    exit 1
}

# ---------- 6) Verificacion final ----------
if (-not (Test-PipSano -Exe $pyExe)) {
    Log "[ERR] pip no responde tras instalarlo. Vea $pipLog" 'Red'
    exit 1
}
$pipv = (& $pyExe -m pip --version 2>$null)
$pyv  = (& $pyExe -c "import platform;print(platform.python_version())" 2>$null)

Log ''
Log ('=' * 70) 'Green'
Log "  BOOTSTRAP COMPLETO" 'Green'
Log ('=' * 70) 'Green'
Log "  python.exe : $pyExe" 'Green'
Log "  Python     : $pyv" 'Green'
Log "  pip        : $pipv" 'Green'
Log ''
Log "Siguiente paso: ejecute  launcher\instalar-agente.bat /VENV"
Log "para instalar fastapi, torch, opencv, etc."
exit 0
