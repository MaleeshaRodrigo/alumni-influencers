param(
    [string]$ProjectRoot = "",
    [string]$PhpExecutable = "",
    [string]$OutputLog = ""
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $PSScriptRoot
}

if (-not (Test-Path -Path $ProjectRoot)) {
    throw "Project root not found: $ProjectRoot"
}

if ([string]::IsNullOrWhiteSpace($PhpExecutable)) {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($null -ne $phpCommand) {
        $PhpExecutable = $phpCommand.Source
    }
}

if ([string]::IsNullOrWhiteSpace($PhpExecutable)) {
    throw "PHP executable not found. Install PHP or pass -PhpExecutable."
}

if ([string]::IsNullOrWhiteSpace($OutputLog)) {
    $logsDir = Join-Path $ProjectRoot "application\logs"
    if (-not (Test-Path -Path $logsDir)) {
        New-Item -Path $logsDir -ItemType Directory -Force | Out-Null
    }

    $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $OutputLog = Join-Path $logsDir ("winner-task-{0}.log" -f $stamp)
}

Push-Location $ProjectRoot
try {
    $args = @("index.php", "bids", "run_daily_winner")
    & $PhpExecutable @args *>> $OutputLog

    if ($LASTEXITCODE -ne 0) {
        throw "Winner command failed with exit code $LASTEXITCODE. See log: $OutputLog"
    }

    Write-Output "Winner command completed successfully."
    Write-Output "Log: $OutputLog"
}
finally {
    Pop-Location
}
