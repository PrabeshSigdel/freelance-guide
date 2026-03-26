$ErrorActionPreference = 'Stop'

$phpExe = $null
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($phpCommand) {
    $phpExe = $phpCommand.Source
} elseif (Test-Path 'C:\xampp\php\php.exe') {
    $phpExe = 'C:\xampp\php\php.exe'
}

if (-not $phpExe) {
    Write-Error 'PHP executable not found. Install PHP in PATH or use C:\xampp\php\php.exe.'
    exit 1
}

$wpContentPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..\..')).Path
$pluginPath = Join-Path $wpContentPath 'plugins\aa-trek-framework'
$themePath = Join-Path $wpContentPath 'themes\aatf-expedition-base'

$paths = @($pluginPath, $themePath)
$phpFiles = Get-ChildItem -Path $paths -Recurse -Filter '*.php' | Sort-Object FullName

if (-not $phpFiles -or $phpFiles.Count -eq 0) {
    Write-Error 'No PHP files found in custom plugin/theme paths.'
    exit 1
}

$failed = @()

foreach ($file in $phpFiles) {
    $output = & $phpExe -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        $failed += $file.FullName
        Write-Host $output -ForegroundColor Red
    } else {
        Write-Host $output
    }
}

if ($failed.Count -gt 0) {
    Write-Host ''
    Write-Host 'Lint failed in:' -ForegroundColor Red
    $failed | ForEach-Object { Write-Host $_ -ForegroundColor Red }
    exit 1
}

Write-Host ''
Write-Host "Lint passed for $($phpFiles.Count) files." -ForegroundColor Green
exit 0
