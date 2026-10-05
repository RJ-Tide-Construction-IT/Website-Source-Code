# The whole sign-in flow through the pretend Microsoft and Google pages, the
# way a browser would: button -> pretend page -> return page -> dashboard.
# Runs with tests/fixtures/test-settings.php (real sign-in settings blank).

# Every hidden field on a page, as a form body (for re-submitting the pretend page).
function Get-HiddenFields([string]$Html, [hashtable]$Override = @{}) {
    $fields = [ordered]@{}
    foreach ($m in ([regex]'<input type="hidden" name="([a-z_]+)" value="([^"]*)"').Matches($Html)) {
        $fields[$m.Groups[1].Value] = [System.Net.WebUtility]::HtmlDecode($m.Groups[2].Value)
    }
    foreach ($k in $Override.Keys) { $fields[$k] = $Override[$k] }
    ($fields.Keys | ForEach-Object { "$_=$(Enc $fields[$_])" }) -join '&'
}

# Clicks a sign-in button and fills in the pretend page. Returns the response
# from the return page (auth-callback.php), plus where the pretend page sent us.
function Invoke-PretendSignIn([string]$Jar, [string]$Method, [string]$Name, [string]$Email, [string]$Extra = '', [hashtable]$Override = @{}) {
    $start = Req $Jar GET "/dashboard/login.php?with=$Method&next=%2Fdashboard%2Fjobs.php"
    $pretendPath = $start.location.Replace($script:Base, '')
    $page = (Req $Jar GET $pretendPath).body
    $sent = Req $Jar POST '/dashboard/pretend-sign-in.php' ((Get-HiddenFields $page $Override) + "&name=$(Enc $Name)&email=$(Enc $Email)$Extra")
    $back = if ($sent.location) { Req $Jar GET $sent.location.Replace($script:Base, '') } else { $sent }
    @{ start = $start; page = $page; sent = $sent; back = $back }
}

$visitor = New-Jar 'visitor'
$login = (Req $visitor GET '/dashboard/login.php').body
Check 'sign-in page shows both buttons locally' ($login -match 'Sign in with Microsoft' -and $login -match 'Sign in with Google')
Check 'each button is labeled as going to a pretend page' (([regex]::Matches($login, 'Local testing: goes to a pretend sign-in page')).Count -eq 2)

# Microsoft: the always-Admin email signs in and lands where they were going
$admin = New-Jar 'admin'
$flow = Invoke-PretendSignIn $admin 'microsoft' 'Mitchel Cross' 'mcross@rjtide.com'
Check 'Microsoft button goes to the pretend Microsoft page' ($flow.start.location -like '*/dashboard/pretend-sign-in.php?service=microsoft&client_id=pretend-microsoft*') $flow.start.location
Check 'pretend page is clearly labeled local-only' ($flow.page -match 'Pretend Microsoft Sign-In' -and $flow.page -match 'Local testing only')
Check 'pretend page sends back a one-time code' ($flow.sent.location -like '*/dashboard/auth-callback.php?code=*&state=*')
Check 'return page signs in and goes to the requested page' ($flow.back.code -eq 302 -and $flow.back.location -like '*/dashboard/jobs.php') $flow.back.location
Check 'signed in as Admin via Microsoft' ((Req $admin GET '/dashboard/').body -match 'Mitchel Cross &middot; Admin')
Check 'Users page shows the sign-in method as Microsoft' ((Req $admin GET '/dashboard/users.php').body -match 'mcross@rjtide.com &middot; Microsoft')

# The same one-time code can't be used twice
$replay = Req $admin GET $flow.sent.location.Replace($script:Base, '')
Check 'replaying a used code is refused' ($replay.location -like '*login.php')

# Cancel on the pretend page
$cancelJar = New-Jar 'cancel'
$flow = Invoke-PretendSignIn $cancelJar 'microsoft' 'x' 'x@example.com' '&action=cancel'
Check 'Cancel sends back an access_denied error' ($flow.sent.location -like '*error=access_denied*')
Check 'after Cancel, the visitor sees "Sign-in didn''t work"' ((Req $cancelJar GET '/dashboard/login.php').body -match 'Sign-in didn&#039;t work')

# Two sign-ins started in one browser (double-click, two tabs): the first still works
$double = New-Jar 'double'
$first  = Req $double GET '/dashboard/login.php?with=microsoft&next=%2Fdashboard%2F'
Req $double GET '/dashboard/login.php?with=microsoft&next=%2Fdashboard%2F' | Out-Null
$page = (Req $double GET $first.location.Replace($script:Base, '')).body
$sent = Req $double POST '/dashboard/pretend-sign-in.php' ((Get-HiddenFields $page) + '&name=Double+Click&email=double%40rjtide.com')
$back = Req $double GET $sent.location.Replace($script:Base, '')
Check 'starting a second sign-in doesn''t cancel the first' ($back.location -eq "$($script:Base)/dashboard/") $back.location

# On your own computer, a failed sign-in shows the exact reason
$reason = New-Jar 'reason'
Req $reason GET '/dashboard/auth-callback.php?code=abc&state=made-up' | Out-Null
Check 'failed sign-in shows the exact reason locally' ((Req $reason GET '/dashboard/login.php').body -match 'Shown only on your own computer: No matching sign-in in progress')

# A code issued for a different PKCE challenge is refused
$pkceJar = New-Jar 'pkce'
$flow = Invoke-PretendSignIn $pkceJar 'microsoft' 'Pkce Test' 'pkce@rjtide.com' '' @{ code_challenge = 'not-the-real-challenge-xxxxxxxxxxxxxxxxxxxxx' }
Check 'mismatched PKCE challenge is refused' ($flow.back.location -like '*login.php') $flow.back.location
Check 'pretend page refuses a wrong return address' ((Req $pkceJar GET '/dashboard/pretend-sign-in.php?service=microsoft&client_id=pretend-microsoft&redirect_uri=https%3A%2F%2Fevil.example%2F&response_type=code&state=s&nonce=n&code_challenge=c&code_challenge_method=S256').code -eq 400)

# Google: held for approval, and unverified emails refused
$crew = New-Jar 'crew'
$flow = Invoke-PretendSignIn $crew 'google' 'Joe Crew' 'joe.crew@gmail.com' '&email_verified=1'
Check 'Google button goes to the pretend Google page' ($flow.start.location -like '*/dashboard/pretend-sign-in.php?service=google*')
Check 'new Google sign-in waits for approval' ($flow.back.location -like '*login.php' -and (Req $crew GET '/dashboard/login.php').body -match 'needs to approve your account')
Check 'Admin sees the Google sign-in waiting' ((Req $admin GET '/dashboard/').body -match '1 person is waiting for approval')
$unverified = New-Jar 'unverified'
$flow = Invoke-PretendSignIn $unverified 'google' 'No Verify' 'noverify@gmail.com'
Check 'unverified Google email is refused' ((Req $unverified GET '/dashboard/login.php').body -match 'Sign-in didn&#039;t work')

# Approve the Google sign-in, then it can sign in through the pretend page
$users = (Req $admin GET '/dashboard/users.php').body
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=$(Get-UserId $users 'Joe Crew')&role=employee&action=approve" | Out-Null
$flow = Invoke-PretendSignIn $crew 'google' 'Joe Crew' 'joe.crew@gmail.com' '&email_verified=1'
Check 'approved Google account signs in through the pretend page' ($flow.back.location -like '*/dashboard/jobs.php' -and (Req $crew GET '/dashboard/').code -eq 200)
