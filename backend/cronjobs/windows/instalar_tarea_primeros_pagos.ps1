#Requires -RunAsAdministrator
<#
  Instala una tarea programada en Windows que ejecuta cada 5 minutos el envio automatico.
  Usa ejecutar_primeros_pagos.bat si existe (mismo criterio que el Programador de tareas manual);
  si no, invoca php.exe + enviar_primeros_pagos_lunes.php.

  El interruptor en la aplicacion solo controla si el script envia o no;
  esta tarea debe existir para que haya ejecuciones periodicas.

  Uso (PowerShell como administrador), desde esta carpeta:
    .\instalar_tarea_primeros_pagos.ps1

  Parametros opcionales:
    .\instalar_tarea_primeros_pagos.ps1 -PhpExe "D:\php\php.exe"
#>
param(
    [string]$PhpExe = ""
)

$ErrorActionPreference = "Stop"

$taskName = "SpartaLedger_PrimerosPagos_Auto_CDMX"

# Ruta del script PHP (hermano de la carpeta windows)
$cronPhp = Join-Path (Split-Path -Parent $PSScriptRoot) "enviar_primeros_pagos_lunes.php"

if (-not (Test-Path -LiteralPath $cronPhp)) {
    Write-Error "No se encontro el cron: $cronPhp"
}

if ([string]::IsNullOrWhiteSpace($PhpExe)) {
    $candidates = @(
        (Join-Path ${env:ProgramFiles} "xampp\php\php.exe"),
        (Join-Path ${env:ProgramFiles(x86)} "xampp\php\php.exe"),
        "C:\xampp\php\php.exe"
    )
    foreach ($c in $candidates) {
        if (Test-Path -LiteralPath $c) {
            $PhpExe = $c
            break
        }
    }
}

if ([string]::IsNullOrWhiteSpace($PhpExe) -or -not (Test-Path -LiteralPath $PhpExe)) {
    Write-Error "No se encontro php.exe. Pase -PhpExe 'C:\ruta\a\php.exe'"
}

$cronPhpFull = (Resolve-Path -LiteralPath $cronPhp).Path
$launcherBat = Join-Path (Split-Path -Parent $PSScriptRoot) "ejecutar_primeros_pagos.bat"
if (Test-Path -LiteralPath $launcherBat) {
    $tr = "`"$((Resolve-Path -LiteralPath $launcherBat).Path)`""
    Write-Host "Lanzador: $tr (ejecutar_primeros_pagos.bat)"
} else {
    $tr = "`"$PhpExe`" `"$cronPhpFull`""
    Write-Host "PHP:     $PhpExe"
    Write-Host "Script:  $cronPhpFull"
}
Write-Host "Tarea:   $taskName"
Write-Host ""

schtasks /Delete /TN $taskName /F 2>$null | Out-Null

# Cada 5 minutos, indefinidamente (desde ahora)
$create = schtasks /Create /F /TN $taskName /TR $tr /SC MINUTE /MO 5 /RL LIMITED
if ($LASTEXITCODE -ne 0) {
    Write-Error "schtasks /Create fallo: $create"
}

Write-Host "OK: tarea creada. El interruptor en el menu sigue controlando si el script envia correo o no."
Write-Host "Para eliminar: .\desinstalar_tarea_primeros_pagos.ps1"
