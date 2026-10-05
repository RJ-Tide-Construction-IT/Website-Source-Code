# Nothing set up and the local test sign-in off (like the live site before
# setup): the sign-in page says so, and every testing-only page is closed.
# Runs with tests/fixtures/no-sign-in-settings.php.

$visitor = New-Jar 'visitor'
$page = (Req $visitor GET '/dashboard/login.php').body
Check 'sign-in page says sign-in isn''t available yet' ($page -match 'sign-in isn&rsquo;t available yet')
Check 'no Microsoft or Google buttons' ($page -notmatch 'Sign in with Microsoft' -and $page -notmatch 'Sign in with Google')
Check 'no link to the local test sign-in' ($page -notmatch 'dev-login')
Check 'local test sign-in page is closed (404)' ((Req $visitor GET '/dashboard/dev-login.php').code -eq 404)
Check 'pretend sign-in page is closed (404)' ((Req $visitor GET '/dashboard/pretend-sign-in.php?service=microsoft').code -eq 404)
Check 'asking to sign in with Microsoft does nothing' ((Req $visitor GET '/dashboard/login.php?with=microsoft').code -eq 200)
