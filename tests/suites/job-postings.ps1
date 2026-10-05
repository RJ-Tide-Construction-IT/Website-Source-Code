# Adding, previewing, renaming, unlisting and deleting job postings, and who can do it.

$anon   = New-Jar 'visitor'
$admin  = New-Jar 'admin'
$office = New-Jar 'office'
Invoke-TestSignIn $admin 'Mitchel Cross' 'mcross@rjtide.com' | Out-Null

$body = "# Summary / Objective`nWeld and fabricate parts in the shop.`nSecond line of the same paragraph.`n`n# Essential Functions`n- MIG and TIG welding`n- Read blueprints`n`nTrap: <script>alert(1)</script> & more"

Check 'admin sees the Job Postings tile' ((Req $admin GET '/dashboard/').body -match 'href="/dashboard/postings.php"')
$r = Req $admin GET '/dashboard/posting-edit.php'
Check 'a new posting starts with the example text' ($r.body -match '# Essential Functions')
$t = Csrf $r.body
$form = "csrf=$t&title=$(Enc 'Shop Welder')&salary=$(Enc 'Starting at $24.00')&classification=Non-Exempt&reports_to=$(Enc 'Shop Foreman')&body=$(Enc $body)&standard_sections=1&open=1"

# Preview doesn't save
$r = Req $admin POST '/dashboard/posting-edit.php' "$form&action=preview"
Check 'preview shows the formatted posting' ($r.code -eq 200 -and $r.body -match 'Preview \(not saved yet\)' -and $r.body -match '<h3>Essential Functions</h3>' -and $r.body -match '<li>Read blueprints</li>')
Check 'preview shows typed HTML as plain text' ($r.body -match '&lt;script&gt;alert\(1\)&lt;/script&gt; &amp; more' -and $r.body -notmatch '<script>alert')
Check 'preview doesn''t save anything' (-not (Test-Path 'uploads/dashboard/job-postings.json'))

# Titles must be unique
$r = Req $admin POST '/dashboard/posting-edit.php' "csrf=$t&title=$(Enc 'craftsman 1')&body=x&action=save"
Check 'a built-in title (any capitalization) is refused' ($r.code -eq 200 -and $r.body -match 'already a posting called')
Check 'the reserved title "Other" is refused' ((Req $admin POST '/dashboard/posting-edit.php' "csrf=$t&title=Other&body=x&action=save").body -match 'already a posting called')

# Save
$r = Req $admin POST '/dashboard/posting-edit.php' "$form&action=save"
Check 'saving goes back to the postings list' ($r.code -eq 302 -and $r.location -like '*/dashboard/postings.php') $r.location
$saved = Get-Content 'uploads/dashboard/job-postings.json' -Raw | ConvertFrom-Json
Check 'posting is stored under a web-friendly id' ($null -ne $saved.postings.'shop-welder')
$list = (Req $admin GET '/dashboard/postings.php').body
Check 'postings list shows it as Listed' ($list -match 'Shop Welder' -and $list -match 'badge--open">Listed')

# Public side
$page = Req $anon GET '/careers/posting.php?id=shop-welder'
Check 'public posting page loads' ($page.code -eq 200 -and $page.body -match '<h1>Shop Welder</h1>')
Check 'public page shows salary, classification, reports-to' ($page.body -match 'Starting at \$24.00' -and $page.body -match 'Non-Exempt' -and $page.body -match 'Shop Foreman')
Check 'public page joins paragraph lines and makes bullets' ($page.body -match '<p>Weld and fabricate parts in the shop. Second line of the same paragraph.</p>' -and $page.body -match '<li>MIG and TIG welding</li>')
Check 'public page includes the standard sections' ($page.body -match 'AAP / EEO Statement')
Check 'public page shows typed HTML as plain text' ($page.body -notmatch '<script>alert')
Check 'Careers page lists the new posting' ((Req $anon GET '/careers.php').body -match 'href="/careers/posting.php\?id=shop-welder"')
Check 'application dropdown includes it' ((Req $anon GET '/employment.php').body -match '<option value="Shop Welder">')
Check 'Job Openings lists it, checked' ((Req $admin GET '/dashboard/jobs.php').body -match 'value="Shop Welder" checked')
$missing = Req $anon GET '/careers/posting.php?id=does-not-exist'
Check 'an unknown posting shows a friendly 404' ($missing.code -eq 404 -and $missing.body -match 'isn&rsquo;t available anymore')

# Rename and unlist, then relist without the standard sections
$t = Csrf (Req $admin GET '/dashboard/posting-edit.php?id=shop-welder').body
Req $admin POST '/dashboard/posting-edit.php?id=shop-welder' "csrf=$t&title=$(Enc 'Shop Welder II')&body=$(Enc $body)&standard_sections=1&action=save" | Out-Null
Check 'renamed and unchecked: off the Careers page' ((Req $anon GET '/careers.php').body -notmatch 'Shop Welder')
$page = (Req $anon GET '/careers/posting.php?id=shop-welder').body
Check 'same web address still works after a rename' ($page -match '<h1>Shop Welder II</h1>')
Check 'standard sections still on after the rename' ($page -match 'AAP / EEO Statement')
$t = Csrf (Req $admin GET '/dashboard/posting-edit.php?id=shop-welder').body
Req $admin POST '/dashboard/posting-edit.php?id=shop-welder' "csrf=$t&title=$(Enc 'Shop Welder II')&body=$(Enc $body)&open=1&action=save" | Out-Null
Check 'relisted under the new title' ((Req $anon GET '/careers.php').body -match 'Shop Welder II')
Check 'standard sections can be turned off' ((Req $anon GET '/careers/posting.php?id=shop-welder').body -notmatch 'AAP / EEO Statement')
$openings = Get-Content 'uploads/dashboard/job-openings.json' -Raw | ConvertFrom-Json
Check 'old title is gone from the saved openings' (($openings.open -notcontains 'Shop Welder') -and ($openings.open -contains 'Shop Welder II'))

# Office: openings yes, postings no
Invoke-TestSignIn $office 'Office Person' 'office.person@rjtide.com' | Out-Null
$users = (Req $admin GET '/dashboard/users.php').body
Req $admin POST '/dashboard/users.php' "csrf=$(Csrf $users)&user_id=$(Get-UserId $users 'Office Person')&role=office&active=1" | Out-Null
Check 'Office can open Job Openings' ((Req $office GET '/dashboard/jobs.php').code -eq 200)
Check 'Office can''t open Job Postings (403)' ((Req $office GET '/dashboard/postings.php').code -eq 403)
Check 'Office can''t open the posting editor (403)' ((Req $office GET '/dashboard/posting-edit.php').code -eq 403)
Check 'Office doesn''t see the add-posting link' ((Req $office GET '/dashboard/jobs.php').body -notmatch 'posting-edit.php')

# Delete
$t = Csrf (Req $admin GET '/dashboard/posting-edit.php?id=shop-welder').body
Check 'delete goes back to the list' ((Req $admin POST '/dashboard/posting-edit.php?id=shop-welder' "csrf=$t&action=delete").code -eq 302)
Check 'deleted posting''s page is a 404' ((Req $anon GET '/careers/posting.php?id=shop-welder').code -eq 404)
Check 'deleted posting is gone from the Careers page' ((Req $anon GET '/careers.php').body -notmatch 'Shop Welder')
$log = (Req $admin GET '/dashboard/users.php').body
Check 'activity log has the add, edit and delete' ($log -match 'added the job posting Shop Welder' -and $log -match 'renamed from Shop Welder' -and $log -match 'deleted the job posting Shop Welder II')

$nav = [regex]::Match((Req $admin GET '/dashboard/posting-edit.php').body, '<nav class="dash-tabs".*?</nav>', 'Singleline').Value
Check 'Job Postings tab is highlighted on the editor' ($nav -match 'class="is-active"[^>]*>Job Postings<')
