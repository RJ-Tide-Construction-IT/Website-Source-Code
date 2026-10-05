# The setup check page reports correctly with (made-up) Microsoft and Google
# settings. Needs internet: it really contacts Microsoft and Google.
# Runs with tests/fixtures/fake-sign-in-settings.php.

$html = (Req (New-Jar 'visitor') GET '/dashboard/setup-check.php').body
$rows = @{}
([regex]'(?s)<li class="is-(ok|bad)">.*?<strong>([^<]+)</strong>').Matches([string]$html) | ForEach-Object {
    $rows[[System.Net.WebUtility]::HtmlDecode($_.Groups[2].Value)] = $_.Groups[1].Value
}
function Expect([string]$Label, [string]$State) {
    Check "setup check: $Label = $State" ($rows[$Label] -eq $State) "got: $($rows[$Label])"
}

Expect 'PHP 8.1 or newer' 'ok'
Expect 'The site can save files in uploads/dashboard/' 'ok'
Expect 'SQLite support (pdo_sqlite), for the built-in database' 'ok'
Expect 'At least one sign-in method (Microsoft or Google) is set up' 'ok'
Expect 'Microsoft: Tenant ID and Client ID look right' 'ok'
Expect 'Microsoft: the server can reach Microsoft sign-in' 'ok'
Expect 'Microsoft: recognizes the Tenant ID' 'bad'   # the made-up tenant
Expect 'Google: Client ID looks right' 'ok'
Expect 'Google: the server can reach Google sign-in' 'ok'
Expect 'Google: recognizes the Client ID' 'bad'       # the made-up client ID
Expect 'Local test sign-in is on (fine on your own computer)' 'ok'   # the tests turn it on; only flagged on the live server
