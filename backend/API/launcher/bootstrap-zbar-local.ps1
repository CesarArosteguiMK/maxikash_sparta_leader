param(
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$apiDir = Split-Path -Parent $here
$binDir = Join-Path $apiDir 'tools\zbar\bin'

New-Item -ItemType Directory -Force -Path $binDir | Out-Null

function Test-NeedDownload {
    foreach ($n in @('libzbar-64.dll', 'libiconv.dll')) {
        $p = Join-Path $binDir $n
        if ($Force -or -not (Test-Path -LiteralPath $p)) {
            return $true
        }
    }
    return $false
}

# Paquetes MSYS2 (mingw64) cuyo bin/ se mezcla: runtime + dependencias de libzbar-0.
$pkgUrls = @(
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-gcc-libs-14.2.0-2-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-zlib-1.3.1-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-libpng-1.6.44-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-libjpeg-turbo-3.1.0-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-libiconv-1.18-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-gettext-runtime-0.23.1-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-libwinpthread-14.0.0.r14.g4761eabdd-1-any.pkg.tar.zst',
    'https://repo.msys2.org/mingw/mingw64/mingw-w64-x86_64-zbar-0.23.93-2-any.pkg.tar.zst'
)

if (Test-NeedDownload) {
    Write-Host "[zbar] Descargando DLLs (MSYS2 mingw64) solo dentro de la API..."
    $tmp = Join-Path $env:TEMP ('sparta-zbar-' + [guid]::NewGuid().ToString())
    $merge = Join-Path $tmp 'merge-bin'
    New-Item -ItemType Directory -Force -Path $tmp | Out-Null
    New-Item -ItemType Directory -Force -Path $merge | Out-Null
    try {
        $i = 0
        foreach ($url in $pkgUrls) {
            $i++
            $pkg = Join-Path $tmp ("p{0}.pkg.tar.zst" -f $i)
            Invoke-WebRequest -Uri $url -OutFile $pkg -UseBasicParsing
            $ex = Join-Path $tmp ('extract{0}' -f $i)
            New-Item -ItemType Directory -Force -Path $ex | Out-Null
            Push-Location $ex
            try {
                tar -xf $pkg
            } finally {
                Pop-Location
            }
            $mb = Join-Path $ex 'mingw64\bin'
            if (Test-Path -LiteralPath $mb) {
                Copy-Item -Path (Join-Path $mb '*.dll') -Destination $merge -Force -ErrorAction SilentlyContinue
            }
        }

        $zb = Join-Path $merge 'libzbar-0.dll'
        $ic = Join-Path $merge 'libiconv-2.dll'
        if (-not (Test-Path -LiteralPath $zb)) { throw "Falta libzbar-0.dll tras fusionar paquetes MSYS2." }
        if (-not (Test-Path -LiteralPath $ic)) { throw "Falta libiconv-2.dll tras fusionar paquetes MSYS2." }

        Copy-Item -LiteralPath $zb -Destination (Join-Path $merge 'libzbar-64.dll') -Force
        Copy-Item -LiteralPath $ic -Destination (Join-Path $merge 'libiconv.dll') -Force

        Copy-Item -Path (Join-Path $merge '*.dll') -Destination $binDir -Force

        $nDll = (Get-ChildItem -LiteralPath $binDir -Filter '*.dll').Count
        Write-Host "[zbar] DLLs en: $binDir ($nDll archivos .dll)"
    }
    finally {
        Remove-Item -LiteralPath $tmp -Recurse -Force -ErrorAction SilentlyContinue
    }
}
else {
    Write-Host "[zbar] DLLs locales ya presentes en $binDir"
}

if (-not (Test-NeedDownload)) {
    Write-Host "[zbar] OK: DLLs locales presentes dentro de API. El doctor validara pyzbar."
    exit 0
}

Write-Host "[zbar] ERROR: faltan DLLs locales en $binDir." -ForegroundColor Yellow
exit 1
