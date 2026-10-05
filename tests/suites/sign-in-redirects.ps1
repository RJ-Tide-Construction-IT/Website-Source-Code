# With (made-up) Microsoft and Google settings: each button sends people to the
# right place with the right settings, and tampered or replayed returns are refused.
# Runs with tests/fixtures/fake-sign-in-settings.php.

Add-Type -AssemblyName System.Web

$page = (Req (New-Jar 'visitor') GET '/dashboard/login.php').body
Check 'sign-in page shows both buttons' ($page -match 'Sign in with Microsoft' -and $page -match 'Sign in with Google')

$expected = @{
    microsoft = @{ host = 'login.microsoftonline.com'; path = '/11111111-2222-3333-4444-555555555555/oauth2/v2.0/authorize'; client = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee' }
    google    = @{ host = 'accounts.google.com';       path = '/o/oauth2/v2/auth';                                      client = '123456789012-abcdefghij.apps.googleusercontent.com' }
}
foreach ($method in 'microsoft', 'google') {
    $jar = New-Jar "start-$method"
    $r = Req $jar GET "/dashboard/login.php?with=$method&next=%2Fdashboard%2Fjobs.php"
    $u = [uri]$r.location
    $q = [System.Web.HttpUtility]::ParseQueryString($u.Query)
    $e = $expected[$method]
    Check "${method}: goes to the right sign-in page" ($u.Host -eq $e.host -and $u.AbsolutePath -eq $e.path) "$($u.Host)$($u.AbsolutePath)"
    Check "${method}: sends the right client ID and return address" ($q['client_id'] -eq $e.client -and $q['redirect_uri'] -eq "$($script:Base)/dashboard/auth-callback.php")
    Check "${method}: asks only for name and email, with PKCE" ($q['scope'] -eq 'openid profile email' -and $q['code_challenge_method'] -eq 'S256' -and $q['code_challenge'].Length -eq 43)
    Check "${method}: one-time state and nonce included" ($q['state'].Length -ge 40 -and $q['nonce'].Length -ge 40)

    $r = Req $jar GET '/dashboard/auth-callback.php?code=abc&state=tampered'
    Check "${method}: tampered return is refused" ($r.location -like '*login.php')
    Check "${method}: visitor sees a friendly error" ((Req $jar GET '/dashboard/login.php').body -match 'Sign-in didn&#039;t work')
    $r = Req $jar GET "/dashboard/auth-callback.php?code=abc&state=$($q['state'])"
    Check "${method}: replaying a used sign-in is refused" ($r.location -like '*login.php')
}

Check 'unknown sign-in method is ignored' ((Req (New-Jar 'odd') GET '/dashboard/login.php?with=facebook').code -eq 200)
