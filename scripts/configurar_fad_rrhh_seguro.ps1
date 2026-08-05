param(
    [string]$EnvFile = 'C:\xampp\secure\sparta_ledger.env'
)

$ErrorActionPreference = 'Stop'

function Convert-SecureValueToPlainText([Security.SecureString]$Value) {
    $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Value)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
    }
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

function Get-EnvValue([System.Collections.Generic.List[string]]$Lines, [string]$Name) {
    $prefix = "$Name="
    foreach ($line in $Lines) {
        if ($line.StartsWith($prefix, [StringComparison]::Ordinal)) {
            return $line.Substring($prefix.Length)
        }
    }
    return ''
}

$directory = Split-Path -Parent $EnvFile
if (-not (Test-Path -LiteralPath $directory)) {
    New-Item -ItemType Directory -Path $directory -Force | Out-Null
}

$lines = [System.Collections.Generic.List[string]]::new()
if (Test-Path -LiteralPath $EnvFile) {
    [IO.File]::ReadAllLines($EnvFile) | ForEach-Object { $lines.Add($_) }
}

$username = Read-Host 'Usuario del portal FAD Capital Humano (Enter conserva el actual)'
$securePassword = Read-Host 'Contrasena del portal FAD Capital Humano' -AsSecureString
$password = Convert-SecureValueToPlainText $securePassword
Write-Host 'Los siguientes cinco valores son opcionales; presiona Enter si todavia no los conoces.' -ForegroundColor Yellow
$countryId = Read-Host 'FAD countryId para Mexico (Enter conserva; recomendado 1)'
$requisitionTypeId = Read-Host 'FAD requisitionTypeId para Contrato (Enter conserva; recomendado 2)'
$signTimeId = Read-Host 'FAD signTimeId (Enter conserva; 15 equivale a 10 dias)'
$signatureBox = Read-Host 'Caja de firma JSON (opcional)'
$certificateBox = Read-Host 'Caja de certificado JSON (opcional)'

if ([string]::IsNullOrWhiteSpace($username)) { $username = Get-EnvValue $lines 'FAD_RRHH_USERNAME' }
if ([string]::IsNullOrWhiteSpace($password)) { $password = Get-EnvValue $lines 'FAD_RRHH_PASSWORD' }
if ([string]::IsNullOrWhiteSpace($countryId)) { $countryId = Get-EnvValue $lines 'FAD_RRHH_COUNTRY_ID' }
if ([string]::IsNullOrWhiteSpace($requisitionTypeId)) { $requisitionTypeId = Get-EnvValue $lines 'FAD_RRHH_REQUISITION_TYPE_ID' }
if ([string]::IsNullOrWhiteSpace($signTimeId)) { $signTimeId = Get-EnvValue $lines 'FAD_RRHH_SIGN_TIME_ID' }
if ([string]::IsNullOrWhiteSpace($signatureBox)) { $signatureBox = Get-EnvValue $lines 'FAD_RRHH_SIGNATURE_BOX' }
if ([string]::IsNullOrWhiteSpace($certificateBox)) { $certificateBox = Get-EnvValue $lines 'FAD_RRHH_CERTIFICATE_BOX' }

if ([string]::IsNullOrWhiteSpace($username) -or [string]::IsNullOrWhiteSpace($password)) {
    throw 'Usuario y contrasena son obligatorios.'
}

foreach ($catalogValue in @($countryId, $requisitionTypeId, $signTimeId)) {
    if (-not [string]::IsNullOrWhiteSpace($catalogValue) -and $catalogValue -notmatch '^[1-9]\d*$') {
        throw 'Los identificadores de catalogo deben ser numeros enteros positivos.'
    }
}

foreach ($jsonValue in @($signatureBox, $certificateBox)) {
    if (-not [string]::IsNullOrWhiteSpace($jsonValue)) {
        try {
            $parsed = $jsonValue | ConvertFrom-Json -ErrorAction Stop
            foreach ($property in @('page', 'positionX1', 'positionX2', 'positionY1', 'positionY2')) {
                if ($null -eq $parsed.$property) { throw "Falta $property" }
            }
        } catch {
            throw 'Las cajas de firma y certificado deben ser JSON valido con pagina y coordenadas.'
        }
    }
}

Set-EnvValue $lines 'FAD_RRHH_ENABLED' '1'
Set-EnvValue $lines 'FAD_RRHH_ENFORCE_SIGNED' '0'
Set-EnvValue $lines 'FAD_RRHH_API_BASE' 'https://api.firmaautografa.com'
Set-EnvValue $lines 'FAD_RRHH_PORTAL_BASE' 'https://clientes.firmaautografa.com'
Set-EnvValue $lines 'FAD_RRHH_AUTH_MODE' 'portal_bootstrap'
Set-EnvValue $lines 'FAD_RRHH_USERNAME' $username.Trim()
Set-EnvValue $lines 'FAD_RRHH_PASSWORD' $password
Set-EnvValue $lines 'FAD_RRHH_COUNTRY_ID' $countryId.Trim()
Set-EnvValue $lines 'FAD_RRHH_COUNTRY_CODE' '+52'
Set-EnvValue $lines 'FAD_RRHH_REQUISITION_TYPE_ID' $requisitionTypeId.Trim()
Set-EnvValue $lines 'FAD_RRHH_SIGN_TIME_ID' $signTimeId.Trim()
Set-EnvValue $lines 'FAD_RRHH_SIGNATURE_BOX' $signatureBox.Trim()
Set-EnvValue $lines 'FAD_RRHH_CERTIFICATE_BOX' $certificateBox.Trim()

[IO.File]::WriteAllLines($EnvFile, $lines, [Text.UTF8Encoding]::new($false))
$password = $null
Write-Host 'Configuracion FAD RRHH guardada en el archivo seguro. El modo obligatorio permanece apagado.' -ForegroundColor Green
