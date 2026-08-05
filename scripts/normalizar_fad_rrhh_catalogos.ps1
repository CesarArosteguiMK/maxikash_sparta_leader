param(
    [string]$EnvFile = 'C:\xampp\secure\sparta_ledger.env'
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $EnvFile)) {
    throw 'No existe el archivo seguro de configuracion FAD RRHH.'
}

function Set-EnvValue([System.Collections.Generic.List[string]]$Lines, [string]$Name, [string]$Value) {
    $prefix = "$Name="
    for ($i = 0; $i -lt $Lines.Count; $i++) {
        if ($Lines[$i].StartsWith($prefix, [StringComparison]::Ordinal)) {
            $Lines[$i] = $prefix + $Value
            return
        }
    }
    $Lines.Add($prefix + $Value)
}

function Test-FadBox([string]$Value) {
    if ([string]::IsNullOrWhiteSpace($Value)) { return $false }
    try {
        $parsed = $Value | ConvertFrom-Json -ErrorAction Stop
        foreach ($property in @('page', 'positionX1', 'positionX2', 'positionY1', 'positionY2')) {
            if ($null -eq $parsed.$property) { return $false }
        }
        return $true
    } catch {
        return $false
    }
}

$lines = [System.Collections.Generic.List[string]]::new()
[IO.File]::ReadAllLines($EnvFile) | ForEach-Object { $lines.Add($_) }

Set-EnvValue $lines 'FAD_RRHH_COUNTRY_ID' '1'
Set-EnvValue $lines 'FAD_RRHH_COUNTRY_CODE' '+52'
Set-EnvValue $lines 'FAD_RRHH_REQUISITION_TYPE_ID' '2'
Set-EnvValue $lines 'FAD_RRHH_SIGN_TIME_ID' '15'
Set-EnvValue $lines 'FAD_RRHH_ENFORCE_SIGNED' '0'

foreach ($name in @('FAD_RRHH_SIGNATURE_BOX', 'FAD_RRHH_CERTIFICATE_BOX')) {
    $prefix = "$name="
    $current = ''
    foreach ($line in $lines) {
        if ($line.StartsWith($prefix, [StringComparison]::Ordinal)) {
            $current = $line.Substring($prefix.Length)
            break
        }
    }
    if (-not (Test-FadBox $current)) {
        Set-EnvValue $lines $name ''
    }
}

[IO.File]::WriteAllLines($EnvFile, $lines, [Text.UTF8Encoding]::new($false))
Write-Host 'Catalogos FAD RRHH normalizados; las cajas invalidas quedaron vacias.' -ForegroundColor Green
