param(
    [int]$Port = 8001,
    [string]$Subdomain = 'gstnotif20260224',
    [switch]$KeepExisting
)

$ErrorActionPreference = 'Stop'

function Get-TunnelProcesses {
    Get-CimInstance Win32_Process -ErrorAction SilentlyContinue |
        Where-Object {
            $_.Name -eq 'node.exe' -and
            $_.CommandLine -match 'localtunnel' -and
            $_.CommandLine -match "--port\s+$Port"
        }
}

if (-not $KeepExisting) {
    Get-TunnelProcesses | ForEach-Object {
        Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
    }
}

$npx = Get-Command npx -ErrorAction SilentlyContinue
if (-not $npx) {
    throw 'npx was not found. Install Node.js first.'
}

$candidateSubdomains = @()
if (-not [string]::IsNullOrWhiteSpace($Subdomain)) {
    $candidateSubdomains += $Subdomain
}

$candidateSubdomains += "gstnotif$((Get-Date).ToString('yyMMdd'))$((Get-Random -Minimum 100 -Maximum 999))"
$candidateSubdomains += "gstnotif$((Get-Random -Minimum 100000 -Maximum 999999))"

$process = $null
$activeSubdomain = $null

foreach ($candidate in $candidateSubdomains | Select-Object -Unique) {
    if (-not $KeepExisting) {
        Get-TunnelProcesses | ForEach-Object {
            Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
        }
    }

    $command = "npx --yes localtunnel --port $Port --subdomain $candidate"
    $process = Start-Process -FilePath 'powershell.exe' -ArgumentList @(
        '-NoProfile',
        '-ExecutionPolicy', 'Bypass',
        '-Command', $command
    ) -PassThru

    Start-Sleep -Seconds 3

    $running = Get-TunnelProcesses | Where-Object { $_.CommandLine -match "--subdomain\s+$candidate" }
    if ($running) {
        $activeSubdomain = $candidate
        break
    }
}

if (-not $activeSubdomain) {
    throw 'LocalTunnel failed to start after multiple attempts.'
}

$url = "https://$activeSubdomain.loca.lt"
$password = ''
try {
    $password = (Invoke-RestMethod -Uri 'https://loca.lt/mytunnelpassword' -TimeoutSec 20 | Out-String).Trim()
} catch {
    $password = '(unavailable right now, retry: Invoke-RestMethod https://loca.lt/mytunnelpassword)'
}

Write-Output 'LocalTunnel started.'
Write-Output "URL: $url"
Write-Output "Password (if prompted): $password"
Write-Output "Process ID: $($process.Id)"
Write-Output "To stop: Get-CimInstance Win32_Process | ? { `$_.Name -eq 'node.exe' -and `$_.CommandLine -match 'localtunnel' -and `$_.CommandLine -match '--subdomain\s+$activeSubdomain' } | % { Stop-Process -Id `$_.ProcessId -Force }"
