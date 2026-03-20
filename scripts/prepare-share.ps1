param(
    [string]$ProjectRoot = (Resolve-Path "$PSScriptRoot\..").Path,
    [string]$OutputDir = (Join-Path (Resolve-Path "$PSScriptRoot\..").Path "dist\share")
)

$ErrorActionPreference = "Stop"

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$shareFolderName = "PFE-share-$timestamp"
$shareRoot = Join-Path $OutputDir $shareFolderName
$zipPath = Join-Path $OutputDir "$shareFolderName.zip"

New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
New-Item -ItemType Directory -Path $shareRoot -Force | Out-Null

$excludeDirs = @(
    ".git",
    "node_modules",
    "vendor",
    ".vscode",
    "dist",
    "storage\debugbar",
    "storage\logs",
    "storage\framework\cache",
    "storage\framework\sessions",
    "storage\framework\views",
    "bootstrap\cache"
) | ForEach-Object { Join-Path $ProjectRoot $_ }

$excludeFiles = @(
    ".env",
    ".env.local",
    ".phpunit.result.cache",
    "*.log"
)

$roboArgs = @(
    $ProjectRoot,
    $shareRoot,
    "/E",
    "/R:1",
    "/W:1",
    "/NFL",
    "/NDL",
    "/NJH",
    "/NJS",
    "/NP",
    "/XD"
) + $excludeDirs + @(
    "/XF"
) + $excludeFiles

robocopy @roboArgs | Out-Null

if ($LASTEXITCODE -ge 8) {
    throw "Share copy failed (robocopy exit code: $LASTEXITCODE)."
}

if (-not (Test-Path (Join-Path $shareRoot ".env.example"))) {
    throw "Generated share folder is invalid: .env.example missing."
}

if (Test-Path $zipPath) {
    Remove-Item -Path $zipPath -Force
}

Compress-Archive -Path (Join-Path $shareRoot "*") -DestinationPath $zipPath -Force

Write-Host "Share package ready: $zipPath"
Write-Host "Sanitized project folder: $shareRoot"