#Requires -RunAsAdministrator
$taskName = "SpartaLedger_PrimerosPagos_Auto_CDMX"
schtasks /Delete /TN $taskName /F
if ($LASTEXITCODE -eq 0) {
    Write-Host "Tarea eliminada: $taskName"
} else {
    Write-Host "No se pudo eliminar (puede que no existiera): codigo $LASTEXITCODE"
}
