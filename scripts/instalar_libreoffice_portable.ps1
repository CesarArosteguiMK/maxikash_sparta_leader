[CmdletBinding()]
param(
    [string]$Version = '26.2.4',
    [string]$ExpectedSha256 = '4bde93374aef4409243505b20d16561a4628ac7591457dd01fc6e1ccf571ba65'
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$toolsRoot = Join-Path $projectRoot 'backend\tools'
$portableRoot = Join-Path $toolsRoot 'LibreOfficePortable'
$soffice = Join-Path $portableRoot 'App\libreoffice\program\soffice.exe'

if (Test-Path -LiteralPath $soffice -PathType Leaf) {
    Write-Host "LibreOffice Portable ya está disponible en: $soffice" -ForegroundColor Green
    & $soffice --headless --version
    exit $LASTEXITCODE
}

$fileName = "LibreOfficePortable_${Version}_MultilingualStandard.paf.exe"
$downloadUrl = "https://download.documentfoundation.org/libreoffice/portable/$Version/$fileName"
$downloadRoot = Join-Path ([System.IO.Path]::GetTempPath()) ("sparta_libreoffice_" + [guid]::NewGuid().ToString('N'))
$installer = Join-Path $downloadRoot $fileName

New-Item -ItemType Directory -Path $toolsRoot -Force | Out-Null
New-Item -ItemType Directory -Path $downloadRoot -Force | Out-Null

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Write-Host "Descargando LibreOffice Portable $Version..." -ForegroundColor Cyan
    Invoke-WebRequest -Uri $downloadUrl -OutFile $installer -UseBasicParsing

    $actualSha256 = (Get-FileHash -LiteralPath $installer -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($actualSha256 -ne $ExpectedSha256.ToLowerInvariant()) {
        throw "La descarga no pasó la validación SHA-256. Esperado: $ExpectedSha256; recibido: $actualSha256"
    }

    Write-Host 'Descarga validada. Extrayendo dentro del proyecto...' -ForegroundColor Cyan
    $destination = $toolsRoot.TrimEnd('\\') + '\\'
    $arguments = @(
        ('/DESTINATION="{0}"' -f $destination),
        '/SILENT=true',
        '/HIDEINSTALLER=true',
        '/AUTOCLOSE=true'
    )
    $process = Start-Process -FilePath $installer -ArgumentList $arguments -Wait -PassThru
    if ($process.ExitCode -ne 0) {
        throw "El instalador de LibreOffice Portable terminó con código $($process.ExitCode)."
    }
    if (-not (Test-Path -LiteralPath $soffice -PathType Leaf)) {
        throw "La instalación terminó, pero no se encontró el ejecutable esperado: $soffice"
    }

    $versionOutput = (& $soffice --headless --version 2>&1 | Out-String).Trim()
    if ($LASTEXITCODE -ne 0) {
        throw 'LibreOffice fue extraído, pero la prueba en modo headless falló.'
    }
    Write-Host "LibreOffice Portable instalado correctamente: $versionOutput" -ForegroundColor Green
    Write-Host "Sparta lo detectará automáticamente en: $soffice" -ForegroundColor Green
} finally {
    if (Test-Path -LiteralPath $downloadRoot) {
        Remove-Item -LiteralPath $downloadRoot -Recurse -Force -ErrorAction SilentlyContinue
    }
}
