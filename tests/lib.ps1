# Shared helpers for the test suites in tests/suites/. Loaded by run-tests.ps1.
# Each suite talks to a local copy of the site (php -S) the way a browser would,
# keeping each pretend person's sign-in in its own cookie file ("jar").

# Records one check's result for the summary.
function Check([string]$Name, [bool]$Ok, [string]$Detail = '') {
    $script:Results.Add([pscustomobject]@{ Suite = $script:CurrentSuite; Name = $Name; Ok = $Ok; Detail = $Detail })
}

# A fresh, empty cookie file for one pretend person.
function New-Jar([string]$Name) {
    $path = Join-Path $script:Tmp "$Name.jar"
    Set-Content -Path $path -Value '' -Encoding ascii
    $path
}

# One page request. Returns @{ code; location; body }. Redirects aren't
# followed, so tests can check where a page sends people.
function Req([string]$Jar, [string]$Method, [string]$Path, [string]$Data = $null) {
    $bodyFile = Join-Path $script:Tmp 'response.html'
    $curlArgs = @('-s', '-o', $bodyFile, '-w', '%{http_code}|%{redirect_url}', '-b', $Jar, '-c', $Jar)
    if ($Method -eq 'POST') { $curlArgs += @('--data', $Data) }
    $meta = (& curl.exe @curlArgs "$($script:Base)$Path").Split('|')
    @{ code = [int]$meta[0]; location = $meta[1]; body = (Get-Content $bodyFile -Raw -ErrorAction SilentlyContinue) }
}

# The form token from a page, needed to submit any dashboard form.
function Csrf([string]$Html) {
    ([regex]'name="csrf" value="([0-9a-f]{64})"').Match([string]$Html).Groups[1].Value
}

function Enc([string]$Text) { [uri]::EscapeDataString($Text) }

# Signs a pretend person in with the local test sign-in. -As google acts like a
# Google account (needs approval); anything else acts like a company account.
function Invoke-TestSignIn([string]$Jar, [string]$Name, [string]$Email, [string]$As = 'company') {
    Set-Content -Path $Jar -Value '' -Encoding ascii
    $t = Csrf (Req $Jar GET '/dashboard/dev-login.php').body
    Req $Jar POST '/dashboard/dev-login.php' "csrf=$t&name=$(Enc $Name)&email=$(Enc $Email)&as=$As"
}

# The user_id hidden in a Users page row, found by the person's name.
function Get-UserId([string]$Html, [string]$Name) {
    [regex]::Match($Html, "(?s)$([regex]::Escape($Name)).*?name=`"user_id`" value=`"(\d+)`"").Groups[1].Value
}
