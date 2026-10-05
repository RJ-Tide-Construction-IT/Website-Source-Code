# Sign-in redirects, roles, job openings, form protection, user management, sign-out.

$anon  = New-Jar 'visitor'
$admin = New-Jar 'admin'
$emp   = New-Jar 'employee'

# Not signed in: sent to the sign-in page, with a way back
$r = Req $anon GET '/dashboard/jobs.php'
Check 'signed-out visitor is sent to sign-in, with a way back' ($r.code -eq 302 -and $r.location -like '*login.php?next=%2Fdashboard%2Fjobs.php') $r.location
$r = Req $anon GET '/dashboard/login.php'
Check 'sign-in page is hidden from search engines' ($r.body -match 'noindex')
Check 'sign-in page loads no analytics' ($r.body -notmatch 'clarity\.ms|googletagmanager')

# Admin signs in; an outside "next" address must be ignored
$t = Csrf (Req $admin GET '/dashboard/dev-login.php?next=https%3A%2F%2Fevil.example').body
$r = Req $admin POST '/dashboard/dev-login.php' "csrf=$t&next=$(Enc 'https://evil.example')&name=Mitchel+Cross&email=mcross%40rjtide.com&as=company"
Check 'admin signs in, outside next= address is ignored' ($r.code -eq 302 -and $r.location -eq "$($script:Base)/dashboard/") $r.location
$r = Req $admin GET '/dashboard/'
Check 'admin home shows Job Openings and Users tiles' ($r.body -match 'href="/dashboard/jobs.php"' -and $r.body -match 'href="/dashboard/users.php"')
Check 'admin is labeled Admin' ($r.body -match 'Mitchel Cross &middot; Admin')
$headers = (curl.exe -s -D - -o NUL -b $admin "$($script:Base)/dashboard/") -join "`n"
Check 'private pages are sent with no-store caching' ($headers -match 'Cache-Control: no-store')

# Job openings, with a made-up title mixed in
$t = Csrf (Req $admin GET '/dashboard/jobs.php').body
$r = Req $admin POST '/dashboard/jobs.php' "csrf=$t&open[]=$(Enc 'Craftsman 1')&open[]=$(Enc 'Project Engineer')&open[]=$(Enc 'Free money')"
Check 'saving job openings redirects back' ($r.code -eq 302)
$json = Get-Content 'uploads/dashboard/job-openings.json' -Raw | ConvertFrom-Json
Check 'only real job titles are saved' (($json.open -join ',') -eq 'Craftsman 1,Project Engineer') ($json.open -join ',')
$careers = (Req $anon GET '/careers.php').body
Check 'Careers page lists newly opened Project Engineer' ($careers -match 'Project Engineer')
Check 'Careers page no longer lists closed Craftsman 2' ($careers -notmatch 'Craftsman 2')
Check 'public Careers page still loads analytics' ($careers -match 'clarity\.ms')
$apply = (Req $anon GET '/employment.php').body
Check 'application dropdown matches the openings' ($apply -match '<option value="Project Engineer">' -and $apply -notmatch '<option value="Craftsman 2">')

# Forms without a valid token are rejected
$r = Req $admin POST '/dashboard/jobs.php' "open[]=$(Enc 'Craftsman 2')"
Check 'form submitted without a token is rejected (400)' ($r.code -eq 400) "$($r.code)"

# Regular employee
Invoke-TestSignIn $emp 'Test Worker' 'test.worker@rjtide.com' | Out-Null
$r = Req $emp GET '/dashboard/'
Check 'employee home hides Admin tiles' ($r.body -match 'Welcome, Test' -and $r.body -notmatch 'href="/dashboard/jobs.php"' -and $r.body -notmatch 'href="/dashboard/users.php"')
Check 'employee is blocked from Job Openings (403)' ((Req $emp GET '/dashboard/jobs.php').code -eq 403)
Check 'employee is blocked from Users (403)' ((Req $emp GET '/dashboard/users.php').code -eq 403)

# Admin promotes the employee to Office: Job Openings yes, Users no
$users = (Req $admin GET '/dashboard/users.php').body
$empId = Get-UserId $users 'Test Worker'
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=$empId&role=office&active=1" | Out-Null
Check 'promoted to Office: can open Job Openings' ((Req $emp GET '/dashboard/jobs.php').code -eq 200)
Check 'Office still cannot open Users (403)' ((Req $emp GET '/dashboard/users.php').code -eq 403)

# Lockout protections
$users = (Req $admin GET '/dashboard/users.php').body
Check 'admin''s own row has no edit form' ($users -match '>You<')
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=1&role=employee&active=0" | Out-Null
Check 'admin can''t demote themselves' ((Req $admin GET '/dashboard/users.php').body -match 'can&#039;t change your own role')
Check 'admin is still Admin afterwards' ((Req $admin GET '/dashboard/').body -match 'Mitchel Cross &middot; Admin')

# Turning access off signs them out right away and blocks signing back in
$users = (Req $admin GET '/dashboard/users.php').body
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=$empId&role=office" | Out-Null
$r = Req $emp GET '/dashboard/'
Check 'turned-off employee is signed out on the next page' ($r.code -eq 302 -and $r.location -like '*login.php*') $r.location
$r = Invoke-TestSignIn $emp 'Test Worker' 'test.worker@rjtide.com'
Check 'turned-off employee can''t sign back in' ($r.location -like '*login.php*') $r.location

# Activity log
$log = (Req $admin GET '/dashboard/users.php').body
Check 'activity log shows job and role changes' ($log -match 'opened Project Engineer' -and $log -match 'role Employee to Office' -and $log -match 'turned access off')

# Sign-out: a plain link does nothing, the form button signs out
Req $admin GET '/dashboard/logout.php' | Out-Null
Check 'sign-out link (GET) doesn''t sign out' ((Req $admin GET '/dashboard/').code -eq 200)
$r = Req $admin POST '/dashboard/logout.php' "csrf=$(Csrf (Req $admin GET '/dashboard/').body)"
Check 'sign-out button works' ($r.location -like '*signed_out=1*' -and (Req $admin GET '/dashboard/').code -eq 302)
