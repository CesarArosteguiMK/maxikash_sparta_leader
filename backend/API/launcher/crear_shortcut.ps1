param(
    [string]$BatPath,
    [string]$ApiDir
)
$desktop = [Environment]::GetFolderPath('Desktop')
$lnkPath = Join-Path $desktop 'Iniciar API Verificacion Documentos (1 Click).lnk'
try {
    $ws = New-Object -ComObject WScript.Shell
    $s = $ws.CreateShortcut($lnkPath)
    $s.TargetPath = $BatPath
    $s.WorkingDirectory = $ApiDir
    $s.Description = 'Inicia y auto-repara la API de verificacion (1 click)'
    $s.Save()
    exit 0
} catch {
    Write-Host "Error: $_"
    exit 1
}
