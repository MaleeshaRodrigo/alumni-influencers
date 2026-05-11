param(
    [string]$TaskName = "AlumniInfluencers-DailyWinner",
    [string]$ProjectRoot = "",
    [string]$PhpExecutable = "",
    [string]$StartTime = "00:00",
    [switch]$RunAsSystem
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

if ([string]::IsNullOrWhiteSpace($ProjectRoot)) {
    $ProjectRoot = Split-Path -Parent $PSScriptRoot
}

if (-not (Test-Path -Path $ProjectRoot)) {
    throw "Project root not found: $ProjectRoot"
}

$runnerScript = Join-Path $ProjectRoot "scripts\run_daily_winner.ps1"
if (-not (Test-Path -Path $runnerScript)) {
    throw "Runner script not found: $runnerScript"
}

$taskRunner = Join-Path $ProjectRoot "scripts\\run_daily_winner_task.bat"
if (-not (Test-Path -Path $taskRunner)) {
    throw "Task runner file not found: $taskRunner"
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

$escapedPhp = $PhpExecutable.Replace('"', '""')
$escapedTaskRunner = $taskRunner.Replace('"', '""')

$taskCommand = "cmd.exe /c `"`"$escapedTaskRunner`" `"$escapedPhp`"`""

Write-Output "Creating or updating scheduled task: $TaskName"
Write-Output "Command: $taskCommand"

if ($RunAsSystem) {
    schtasks /Create /TN "$TaskName" /SC DAILY /ST $StartTime /TR "$taskCommand" /RU "SYSTEM" /F | Out-Null
} else {
    schtasks /Create /TN "$TaskName" /SC DAILY /ST $StartTime /TR "$taskCommand" /F | Out-Null
}
if ($LASTEXITCODE -ne 0) {
    throw "Failed to create task '$TaskName'."
}

Write-Output "Task created. Current task details:"
schtasks /Query /TN "$TaskName" /V /FO LIST
if ($LASTEXITCODE -ne 0) {
    throw "Task '$TaskName' was created but query failed."
}
