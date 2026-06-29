param(
    [string]$BackupRoot = "C:\sparta_backups\mysql",
    [int]$RetentionDays = 14,
    [string]$MysqlDump = "C:\xampp\mysql\bin\mysqldump.exe",
    [string[]]$Databases = @("__SPARTA_SECRET_REDACTED__", "__SPARTA_SECRET_REDACTED__")
)

$ErrorActionPreference = "Stop"

function Read-EnvOrDefault {
    param([string[]]$Names, [string]$Default = "")
    foreach ($name in $Names) {
        $value = [Environment]::GetEnvironmentVariable($name)
        if (-not [string]::IsNullOrWhiteSpace($value)) {
            return $value
        }
    }
    return $Default
}

function Write-Log {
    param([string]$Message)
    $line = "{0} {1}" -f (Get-Date -Format "yyyy-MM-dd HH:mm:ss"), $Message
    Write-Host $line
    Add-Content -LiteralPath $script:LogFile -Value $line -Encoding UTF8
}

if (-not (Test-Path -LiteralPath $MysqlDump)) {
    throw "No se encontro mysqldump en: $MysqlDump"
}

$dateStamp = Get-Date -Format "yyyyMMdd_HHmmss"
$runDir = Join-Path $BackupRoot $dateStamp
$logDir = Join-Path $BackupRoot "logs"
New-Item -ItemType Directory -Force -Path $runDir, $logDir | Out-Null

$script:LogFile = Join-Path $logDir ("backup_{0}.log" -f $dateStamp)

$hostName = Read-EnvOrDefault -Names @("DB_BACKUP_HOST", "DB_HOST", "DB_SERVIDOR") -Default "__SPARTA_HOST_REDACTED__"
$port = Read-EnvOrDefault -Names @("DB_BACKUP_PORT", "DB_PUERTO") -Default "3306"
$user = Read-EnvOrDefault -Names @("DB_BACKUP_USER", "DB_USER", "DB_USUARIO") -Default "__SPARTA_SECRET_REDACTED__"
$password = Read-EnvOrDefault -Names @("DB_BACKUP_PASSWORD", "DB_PASSWORD", "DB_PASS") -Default '__SPARTA_PASSWORD_REDACTED__'
$databases = $Databases | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }

$defaultsFile = Join-Path $env:TEMP ("sparta_mysqldump_{0}.cnf" -f ([Guid]::NewGuid().ToString("N")))

try {
    @"
[client]
host=$hostName
port=$port
user=$user
password=$password
default-character-set=utf8mb4
"@ | Set-Content -LiteralPath $defaultsFile -Encoding ASCII

    Write-Log "Iniciando respaldo MySQL. Host=$hostName Puerto=$port Destino=$runDir"

    foreach ($database in $databases) {
        $outFile = Join-Path $runDir ("{0}_{1}.sql" -f $database, $dateStamp)
        $errFile = Join-Path $runDir ("{0}_{1}.err.log" -f $database, $dateStamp)
        Write-Log "Respaldando base: $database"

        & $MysqlDump `
            "--defaults-extra-file=$defaultsFile" `
            "--single-transaction" `
            "--quick" `
            "--force" `
            "--max-allowed-packet=1G" `
            "--routines" `
            "--triggers" `
            "--events" `
            "--hex-blob" `
            $database `
            "--result-file=$outFile" 2> $errFile

        if ($LASTEXITCODE -ne 0) {
            $detail = ""
            if (Test-Path -LiteralPath $errFile) {
                $detail = (Get-Content -LiteralPath $errFile -Tail 20 -ErrorAction SilentlyContinue) -join " | "
            }
            throw "mysqldump fallo para $database con codigo $LASTEXITCODE. $detail"
        }

        if ((Test-Path -LiteralPath $errFile) -and ((Get-Item -LiteralPath $errFile).Length -gt 0)) {
            $warnings = (Get-Content -LiteralPath $errFile -Tail 20 -ErrorAction SilentlyContinue) -join " | "
            Write-Log "AVISO ${database}: mysqldump continuo con advertencias. $warnings"
        }

        $sizeMb = [Math]::Round((Get-Item -LiteralPath $outFile).Length / 1MB, 2)
        Write-Log "OK $database ($sizeMb MB)"
    }

    $manifest = [ordered]@{
        created_at = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
        timezone = "America/Mexico_City"
        host = $hostName
        port = $port
        databases = $databases
        retention_days = $RetentionDays
    }
    $manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $runDir "manifest.json") -Encoding UTF8

    $zipFile = Join-Path $BackupRoot ("sparta_mysql_{0}.zip" -f $dateStamp)
    Compress-Archive -Path (Join-Path $runDir "*") -DestinationPath $zipFile -Force
    Remove-Item -LiteralPath $runDir -Recurse -Force

    $zipSizeMb = [Math]::Round((Get-Item -LiteralPath $zipFile).Length / 1MB, 2)
    Write-Log "Respaldo comprimido: $zipFile ($zipSizeMb MB)"

    $limitDate = (Get-Date).AddDays(-1 * $RetentionDays)
    Get-ChildItem -LiteralPath $BackupRoot -Filter "sparta_mysql_*.zip" -File |
        Where-Object { $_.LastWriteTime -lt $limitDate } |
        ForEach-Object {
            Write-Log "Eliminando respaldo viejo: $($_.FullName)"
            Remove-Item -LiteralPath $_.FullName -Force
        }

    Get-ChildItem -LiteralPath $logDir -Filter "backup_*.log" -File |
        Where-Object { $_.LastWriteTime -lt $limitDate } |
        Remove-Item -Force

    Write-Log "Respaldo finalizado correctamente."
    exit 0
} catch {
    Write-Log ("ERROR: " + $_.Exception.Message)
    exit 1
} finally {
    if (Test-Path -LiteralPath $defaultsFile) {
        Remove-Item -LiteralPath $defaultsFile -Force
    }
}
