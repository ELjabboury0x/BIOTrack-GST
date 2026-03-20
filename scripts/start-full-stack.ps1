$ErrorActionPreference = 'Stop'

$workspace = Split-Path -Parent $PSScriptRoot
$backend = Join-Path $workspace 'backend'
$publicDir = Join-Path $workspace 'public'
$php = "C:\Users\Dell\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
$extDir = "C:\Users\Dell\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\ext"
$caBundleDir = Join-Path $backend 'storage\certs'
$caBundleFile = Join-Path $caBundleDir 'cacert.pem'
$laravelHost = '0.0.0.0'
$laravelPort = 8001
$realtimePort = 6001

if (-not (Test-Path $backend)) {
    throw "Backend directory not found at: $backend"
}

if (-not (Test-Path $publicDir)) {
    throw "Public directory not found at: $publicDir"
}

if (-not (Test-Path $php)) {
    throw "PHP executable not found at: $php"
}

if (-not (Test-Path $extDir)) {
    throw "PHP extension directory not found at: $extDir"
}

if (-not (Test-Path $caBundleDir)) {
    New-Item -ItemType Directory -Path $caBundleDir -Force | Out-Null
}

if (-not (Test-Path $caBundleFile)) {
    try {
        Invoke-WebRequest -Uri 'https://curl.se/ca/cacert.pem' -OutFile $caBundleFile -UseBasicParsing
    }
    catch {
        Write-Warning "Unable to download CA bundle. Web push SSL validation may fail: $($_.Exception.Message)"
    }
}

$phpArgs = @(
    '-d', "extension_dir=$extDir",
    '-d', 'extension=openssl',
    '-d', 'extension=curl',
    '-d', 'extension=gd',
    '-d', 'extension=mbstring',
    '-d', 'extension=fileinfo',
    '-d', 'extension=pdo_mysql',
    '-d', 'extension=zip',
    '-d', "curl.cainfo=$caBundleFile",
    '-d', "openssl.cafile=$caBundleFile",
    '-d', 'opcache.enable=1',
    '-d', 'opcache.enable_cli=1',
    '-d', 'opcache.validate_timestamps=1',
    '-d', 'opcache.revalidate_freq=2',
    '-d', 'realpath_cache_size=4096K',
    '-d', 'realpath_cache_ttl=600',
    '-S', "$laravelHost`:$laravelPort",
    '-t', $publicDir
)

$ports = @($laravelPort, 8001, 8443, $realtimePort)
foreach ($p in $ports) {
    Get-NetTCPConnection -LocalPort $p -State Listen -ErrorAction SilentlyContinue |
        Select-Object -ExpandProperty OwningProcess -Unique |
        ForEach-Object {
            Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue
        }
}

Push-Location $backend
& $php '-d' "extension_dir=$extDir" '-d' 'extension=openssl' '-d' 'extension=curl' '-d' 'extension=gd' '-d' 'extension=mbstring' '-d' 'extension=fileinfo' '-d' 'extension=pdo_mysql' '-d' "curl.cainfo=$caBundleFile" '-d' "openssl.cafile=$caBundleFile" artisan optimize | Out-Null
Pop-Location

$env:REALTIME_HOST = '0.0.0.0'
$env:REALTIME_PORT = "$realtimePort"

# --- Ensure Redis is running ---
$redisSvc = Get-Service -Name "Redis" -ErrorAction SilentlyContinue
if ($redisSvc -and $redisSvc.Status -ne 'Running') {
    Start-Service -Name "Redis" -ErrorAction SilentlyContinue
    Write-Output "Redis service started."
} elseif ($redisSvc) {
    Write-Output "Redis already running."
} else {
    Write-Warning "Redis service not found. Cache/session/queue will fall back to file driver."
}

# --- Ensure MySQL is running ---
$mysqlProc = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
if (-not $mysqlProc) {
    if (Test-Path "C:\xampp\mysql_start.bat") {
        Start-Process -FilePath "C:\xampp\mysql_start.bat" -WindowStyle Minimized
        Write-Output "MySQL starting..."

        $mysqlReady = $false
        for ($i = 0; $i -lt 20; $i++) {
            Start-Sleep -Seconds 1
            $listener = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue
            if ($listener) {
                $mysqlReady = $true
                break
            }
        }

        if ($mysqlReady) {
            Write-Output "MySQL is listening on port 3306."
        } else {
            Write-Warning "MySQL did not become ready within 20 seconds. Laravel may return DB connection errors until MySQL is up."
        }
    } else {
        Write-Warning "MySQL not running and start script not found."
    }
} else {
    Write-Output "MySQL already running."
}

Start-Process -FilePath $php -ArgumentList $phpArgs -WorkingDirectory $backend
Start-Process -FilePath 'npm.cmd' -ArgumentList @('--prefix', $backend, 'run', 'realtime') -WorkingDirectory $workspace

# --- Start Nginx ---
$nginxExe = "C:\Users\Dell\AppData\Local\Microsoft\WinGet\Packages\nginxinc.nginx_Microsoft.Winget.Source_8wekyb3d8bbwe\nginx-1.29.5\nginx.exe"
$nginxDir = Split-Path $nginxExe
$nginxRunning = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue
if (-not $nginxRunning -and (Test-Path $nginxExe)) {
    Start-Process -FilePath $nginxExe -WorkingDirectory $nginxDir
    Write-Output "Nginx started on port 80."
} elseif ($nginxRunning) {
    Write-Output "Nginx already running on port 80."
}

$lanIp = ([System.Net.Dns]::GetHostAddresses([System.Net.Dns]::GetHostName()) | Where-Object { $_.AddressFamily -eq 'InterNetwork' -and $_.IPAddressToString -notlike '169.254*' } | Select-Object -First 1).IPAddressToString

Write-Output ""
Write-Output "=== All services started ==="
Write-Output "Nginx (front):    http://127.0.0.1:80"
Write-Output "Laravel (direct): http://127.0.0.1:$laravelPort"
Write-Output "LAN URL:          http://${lanIp}:80"
