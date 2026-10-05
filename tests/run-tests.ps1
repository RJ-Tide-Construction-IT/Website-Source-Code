# Runs every Employee Dashboard test against a local copy of the site.
#
#   powershell -ExecutionPolicy Bypass -File tests\run-tests.ps1
#
# Nothing to set up first. For each suite it starts PHP's preview server on a
# fresh, empty dashboard database, runs the checks, and fails the suite if PHP
# logged any warnings. Your own local dashboard data (uploads/dashboard/) is set
# aside first and put back at the end, even if a test fails. Tests never send
# email and never touch the live site. The setup-check suite needs internet.
param([int]$Port = 8765)

$ErrorActionPreference = 'Stop'
$Root = Split-Path $PSScriptRoot -Parent
Set-Location $Root
. (Join-Path $PSScriptRoot 'lib.ps1')

$script:Base    = "http://localhost:$Port"
$script:Tmp     = Join-Path $env:TEMP 'rjtide-dashboard-tests'
$script:Results = New-Object System.Collections.Generic.List[object]
$DataDir        = Join-Path $Root 'uploads\dashboard'
$Backup         = Join-Path $script:Tmp 'your-dashboard-data'
$Fixtures       = Join-Path $PSScriptRoot 'fixtures'

if (-not (Get-Command php -ErrorAction SilentlyContinue)) { throw 'PHP is not installed or not on the PATH.' }
if (-not (Get-Command curl.exe -ErrorAction SilentlyContinue)) { throw 'curl.exe was not found (it comes with Windows 10 and later).' }
# Turn on SQLite for the test server only if php.ini doesn't already (turning
# it on twice makes PHP log a warning, which would fail every suite).
$sqliteOption = if ((php -r "echo extension_loaded('pdo_sqlite') ? 1 : 0;") -eq '1') { @() } else { @('-d', 'extension=pdo_sqlite') }
if (Test-Path $script:Tmp) { Remove-Item -Recurse -Force $script:Tmp }
New-Item -ItemType Directory -Path $script:Tmp | Out-Null

# Web-page suites: file in tests\suites, plus the settings file loaded before every page.
$suites = @(
    @{ Name = 'dashboard';         Settings = 'test-settings.php' },
    @{ Name = 'job-postings';      Settings = 'test-settings.php' },
    @{ Name = 'google-approval';   Settings = 'test-settings.php' },
    @{ Name = 'pretend-sign-in';   Settings = 'test-settings.php' },
    @{ Name = 'no-sign-in';        Settings = 'no-sign-in-settings.php' },
    @{ Name = 'sign-in-redirects'; Settings = 'fake-sign-in-settings.php' },
    @{ Name = 'setup-check';       Settings = 'fake-sign-in-settings.php' }
)

function Clear-DashboardData {
    if (Test-Path $DataDir) { Remove-Item -Recurse -Force $DataDir }
}

$hadData = Test-Path $DataDir
if ($hadData) { Move-Item $DataDir $Backup }
try {
    foreach ($suite in $suites) {
        $script:CurrentSuite = $suite.Name
        Clear-DashboardData
        $log = Join-Path $script:Tmp "php-$($suite.Name).log"
        $prepend = Join-Path $Fixtures $suite.Settings
        $server = Start-Process php -PassThru -WindowStyle Hidden -WorkingDirectory $Root -RedirectStandardError $log -ArgumentList ($sqliteOption + @(
            '-d', 'display_errors=0', '-d', "auto_prepend_file=`"$prepend`"", '-S', "localhost:$Port"))
        try {
            # Wait for the server to start answering.
            for ($i = 0; $i -lt 50 -and (curl.exe -s -o NUL -w '%{http_code}' "$($script:Base)/index.php") -ne '200'; $i++) { Start-Sleep -Milliseconds 100 }
            . (Join-Path $PSScriptRoot "suites\$($suite.Name).ps1")
        } catch {
            Check "suite stopped early: $_" $false
        } finally {
            Stop-Process -Id $server.Id -ErrorAction SilentlyContinue
            Start-Sleep -Milliseconds 300
        }
        # Any PHP warning is a failure. The one exception: your own secrets.php
        # trying to redefine a setting the test settings file already set.
        $problems = @(Get-Content $log -ErrorAction SilentlyContinue | Where-Object {
            $_ -match 'PHP (Warning|Fatal|Notice|Deprecated)|Employee Dashboard error' -and $_ -notmatch 'already defined in .*secrets\.php'
        })
        Check 'no PHP warnings or errors' ($problems.Count -eq 0) ($problems | Select-Object -First 1)
    }

    # Command-line checks: ID-token rules and live-site address handling.
    $script:CurrentSuite = 'identity-checks'
    foreach ($line in (php (Join-Path $PSScriptRoot 'php\identity-checks.php') 2>&1)) {
        $status, $name, $detail = "$line" -split '\|', 3
        if ($status -in 'PASS', 'FAIL') {
            Check $name ($status -eq 'PASS') $detail
        } elseif ("$line".Trim()) {
            Check "script output: $line" $false   # e.g. a PHP error that stopped the script
        }
    }

    $script:CurrentSuite = 'live-address'
    $addressScript = Join-Path $PSScriptRoot 'php\live-address.php'
    $uri = php $addressScript redirect-uri
    Check 'sign-in return address is always https://rjtide.com/...' ($uri -eq 'https://rjtide.com/dashboard/auth-callback.php') $uri
    foreach ($case in @(@('rjtide.com', 'continues'), @('RJTIDE.COM', 'continues'), @('www.rjtide.com', 'redirected'), @('evil.example', 'redirected'))) {
        $got = php $addressScript $case[0]
        Check "visiting $($case[0]): $($case[1])" ($got -eq $case[1]) $got
    }
} finally {
    Clear-DashboardData
    if ($hadData) { Move-Item $Backup $DataDir }
}

# Summary
$script:Results | Group-Object Suite | ForEach-Object {
    $failed = @($_.Group | Where-Object { -not $_.Ok })
    '{0,-18} {1,3} passed{2}' -f $_.Name, ($_.Count - $failed.Count), $(if ($failed) { ", $($failed.Count) FAILED" } else { '' })
    foreach ($f in $failed) { "    FAIL  $($f.Name)$(if ($f.Detail) { "  [$($f.Detail)]" })" }
}
$failedTotal = @($script:Results | Where-Object { -not $_.Ok }).Count
"`n{0} checks, {1} failed. Your local dashboard data was {2}." -f $script:Results.Count, $failedTotal, $(if ($hadData) { 'put back' } else { 'not touched (there wasn''t any)' })
if ($failedTotal) { exit 1 }
