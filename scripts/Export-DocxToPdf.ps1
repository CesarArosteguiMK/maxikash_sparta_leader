param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath,
    [Parameter(Mandatory = $true)]
    [string]$OutputPath
)

$ErrorActionPreference = 'Stop'
$inputFile = Get-Item -LiteralPath $InputPath
if ($inputFile.Extension -ne '.docx') {
    throw 'El archivo de entrada debe ser DOCX.'
}
$outputFullPath = [IO.Path]::GetFullPath($OutputPath)
if ([IO.Path]::GetExtension($outputFullPath) -ne '.pdf') {
    throw 'El archivo de salida debe ser PDF.'
}
$outputDirectory = [IO.Path]::GetDirectoryName($outputFullPath)
if (-not (Test-Path -LiteralPath $outputDirectory)) {
    New-Item -ItemType Directory -Path $outputDirectory -Force | Out-Null
}

$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($inputFile.FullName, $false, $true)
    $document.ExportAsFixedFormat($outputFullPath, 17)
} finally {
    if ($null -ne $document) {
        $document.Close($false)
        [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($document)
    }
    if ($null -ne $word) {
        $word.Quit()
        [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($word)
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}

Write-Output $outputFullPath
