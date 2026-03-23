param(
    [string]$BatPath,
    [string]$ApiDir
)
$desktop = [Environment]::GetFolderPath('Desktop')
$lnkPath = Join-Path $desktop 'Iniciar API Verificacion Documentos.lnk'
try {
    $ws = New-Object -ComObject WScript.Shell
    $s = $ws.CreateShortcut($lnkPath)
    $s.TargetPath = $BatPath
    $s.WorkingDirectory = $ApiDir
    $s.Description = 'Inicia la API de verificacion de documentos (FAD/Ingresos)'
    $s.Save()
    exit 0
} catch {
    Write-Host "Error: $_"
    exit 1
}
