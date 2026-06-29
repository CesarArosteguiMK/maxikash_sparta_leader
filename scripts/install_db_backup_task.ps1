param(
    [string]$TaskName = "Sparta MySQL Backup Diario",
    [string]$ScriptPath = "C:\xampp\htdocs\sparta___SPARTA_SECRET_REDACTED__\scripts\db_backup_daily.bat",
    [string]$At = "02:00"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path -LiteralPath $ScriptPath)) {
    throw "No existe el script de respaldo: $ScriptPath"
}

$startTime = [DateTime]::ParseExact($At, "HH:mm", $null)
$action = New-ScheduledTaskAction -Execute $ScriptPath
$trigger = New-ScheduledTaskTrigger -Daily -At $startTime
$settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -MultipleInstances IgnoreNew `
    -ExecutionTimeLimit (New-TimeSpan -Hours 3)

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Description "Respaldo diario de __SPARTA_SECRET_REDACTED__ y __SPARTA_SECRET_REDACTED__." `
    -Force | Out-Null

Write-Host "Tarea programada creada/actualizada: $TaskName a las $At"
