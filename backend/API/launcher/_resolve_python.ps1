# Dot-source desde doctor-api.ps1 / iniciar-agente-oculto.ps1 / etc.
# Resuelve Python SIN depender del PATH del servidor si hay copia portable en API.

function Get-SpartaPortablePythonExe {
    param([string]$ApiDir)
    if (-not $ApiDir) { return $null }
    $txt = Join-Path $ApiDir 'launcher\PYTHON_EXE.txt'
    if (Test-Path -LiteralPath $txt) {
        foreach ($raw in Get-Content -LiteralPath $txt -Encoding UTF8 -ErrorAction SilentlyContinue) {
            $line = if ($null -eq $raw) { '' } else { "$raw".Trim() }
            if (-not $line -or $line.StartsWith('#')) { continue }
            if (Test-Path -LiteralPath $line) { return $line }
        }
    }
    foreach ($rel in @(
            'tools\PythonPortable\python.exe',
            'tools\python312\python.exe',
            'tools\Python312\python.exe'
        )) {
        $p = Join-Path $ApiDir $rel
        if (Test-Path -LiteralPath $p) { return $p }
    }
    return $null
}

function Test-SpartaPythonViable {
    param(
        [string]$PythonExe,
        [string]$ApiDir
    )
    if (-not $PythonExe -or -not (Test-Path -LiteralPath $PythonExe)) {
        return $false
    }
    $chk = Join-Path $ApiDir 'launcher\_check_standard_python.py'
    if (-not (Test-Path -LiteralPath $chk)) {
        return $true
    }
    $p = Start-Process -FilePath $PythonExe -ArgumentList @($chk) `
        -WorkingDirectory $ApiDir -Wait -PassThru -WindowStyle Hidden
    # exit 2 = free-threading / incompatible
    return ($null -eq $p.ExitCode -or $p.ExitCode -ne 2)
}

function Resolve-SpartaApiPython {
    param([string]$ApiDir)

    $venvPy = Join-Path $ApiDir 'venv\Scripts\python.exe'
    if ((Test-Path -LiteralPath $venvPy) -and (Test-SpartaPythonViable -PythonExe $venvPy -ApiDir $ApiDir)) {
        return [PSCustomObject]@{ Exe = $venvPy; Args = @(); Source = 'venv' }
    }

    $port = Get-SpartaPortablePythonExe -ApiDir $ApiDir
    if ($port) {
        return [PSCustomObject]@{
            Exe    = $port
            Args   = @()
            Source = 'portable (sin PATH: PYTHON_EXE.txt o tools\...)'
        }
    }

    & py -3 -c "import sys" *> $null
    if ($LASTEXITCODE -eq 0) {
        return [PSCustomObject]@{ Exe = 'py'; Args = @('-3'); Source = 'py -3 (PATH)' }
    }
    & python -c "import sys" *> $null
    if ($LASTEXITCODE -eq 0) {
        return [PSCustomObject]@{ Exe = 'python'; Args = @(); Source = 'python (PATH)' }
    }

    return $null
}
