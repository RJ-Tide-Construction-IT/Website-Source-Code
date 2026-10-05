# Google sign-ins wait for an Admin's approval; Google accounts never get
# automatic Admin, even with an always-Admin email address.

$admin = New-Jar 'admin'
$crew  = New-Jar 'crew'
$fake  = New-Jar 'fake'

Invoke-TestSignIn $admin 'Mitchel Cross' 'mcross@rjtide.com' | Out-Null
Check 'company Admin signs in normally' ((Req $admin GET '/dashboard/').code -eq 200)

# A field employee signs in with Google: held for approval
$r = Invoke-TestSignIn $crew 'Joe Crew' 'joe.crew@gmail.com' 'google'
Check 'Google sign-in goes back to the sign-in page' ($r.location -like '*login.php*') $r.location
Check 'Google sign-in sees the waiting-for-approval message' ((Req $crew GET '/dashboard/login.php').body -match 'waiting for an Admin to approve')
Check 'unapproved Google account can''t open the dashboard' ((Req $crew GET '/dashboard/').code -eq 302)

# Someone makes a Google account with the Admin's work email
Invoke-TestSignIn $fake 'Fake Mitchel' 'mcross@rjtide.com' 'google' | Out-Null
Check 'Google account using an always-Admin email is still held' ((Req $fake GET '/dashboard/').code -eq 302)

Check 'Admin home shows 2 people waiting' ((Req $admin GET '/dashboard/').body -match '2 people are waiting for approval')
$users = (Req $admin GET '/dashboard/users.php').body
Check 'Users page lists both under Awaiting approval' ($users -match 'Awaiting approval' -and $users -match 'Joe Crew' -and $users -match 'Fake Mitchel')
Check 'sign-in method shown as Google' ($users -match 'joe.crew@gmail.com &middot; Google')

# Approve Joe as Office, decline the fake
$t = Csrf $users
$joeId = Get-UserId $users 'Joe Crew'
Req $admin POST '/dashboard/users.php' "csrf=$t&user_id=$joeId&role=office&action=approve" | Out-Null
Req $admin POST '/dashboard/users.php' "csrf=$t&user_id=$(Get-UserId $users 'Fake Mitchel')&action=decline" | Out-Null
$r = Invoke-TestSignIn $crew 'Joe Crew' 'joe.crew@gmail.com' 'google'
Check 'approved Google account can sign in' ($r.location -like '*/dashboard/' -and (Req $crew GET '/dashboard/').code -eq 200) $r.location
Check 'approved as Office: can open Job Openings' ((Req $crew GET '/dashboard/jobs.php').code -eq 200)
Invoke-TestSignIn $fake 'Fake Mitchel' 'mcross@rjtide.com' 'google' | Out-Null
Check 'declined account is told its access is off' ((Req $fake GET '/dashboard/login.php').body -match 'access is turned off')
Check 'declined account can''t open the dashboard' ((Req $fake GET '/dashboard/').code -eq 302)
Check 'no one is left waiting' ((Req $admin GET '/dashboard/').body -notmatch 'waiting for approval')

# Turning an approved Google account off later
$users = (Req $admin GET '/dashboard/users.php').body
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=$joeId&role=office" | Out-Null
Check 'turned-off Google account is signed out on the next page' ((Req $crew GET '/dashboard/').code -eq 302)
Check 'the real Admin is unaffected' ((Req $admin GET '/dashboard/').body -match 'Mitchel Cross &middot; Admin')

$log = (Req $admin GET '/dashboard/users.php').body
Check 'activity log records waiting, approval and decline' ($log -match 'Joe Crew \(joe.crew@gmail.com\) signed in with Google for the first time and is waiting for approval' -and $log -match 'approved Joe Crew \(Google\) as Office' -and $log -match 'declined Fake Mitchel \(Google\)')
