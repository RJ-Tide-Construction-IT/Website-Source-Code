# RJ Tide Construction, website source

This is the code behind the RJ Tide Construction website. It's written in **PHP**,
a language that mixes regular HTML with small chunks of code (anything between
`<?php ... ?>`) to fill in things like the phone number or a list of projects
automatically instead of typing them out on every page.

You don't need to know PHP to make most changes, the sections below tell you
exactly which file to open for common edits.

## "I want to change...", quick lookup

| I want to... | Open this file |
|---|---|
| Change the phone number, email, or address shown site-wide | [includes/config.php](includes/config.php) |
| Change the text/wording on the homepage | [index.php](index.php) |
| Change the About page text | [about.php](about.php) |
| Change a service page (Concrete, Agricultural, Industrial Maintenance) | the matching file in [services/](services/) |
| Add a new job posting | Employee Dashboard > **Job Postings** > **Add a job posting** (Admins), no code changes needed |
| Edit one of the 11 original job postings | the matching file in [careers/](careers/) (these are built into the site; the list of them is `$GLOBALS['JOBS']` in [includes/config.php](includes/config.php)) |
| Stop listing a position you're not hiring for right now | Sign in to the Employee Dashboard (`/dashboard/`) and use **Job Openings**, no code changes needed |
| Give an employee more dashboard access, or turn off someone's access | Employee Dashboard > **Users** (Admins only) |
| Change what each dashboard role can do, or who is always an Admin | `DASHBOARD_ROLES` / `DASHBOARD_ADMIN_EMAILS` in [includes/config.php](includes/config.php) |
| Change the EEO / Work Authorization / Other Duties wording on job postings | [includes/job-posting-standard-sections.php](includes/job-posting-standard-sections.php) (shared by every posting that shows them) |
| Add/remove a checkbox on the job application (licenses, physical requirements, experience) | the `APPLICATION_...` lists in [includes/config.php](includes/config.php) |
| Change the layout of the printable PDF attached to each job application email | [includes/application-pdf.php](includes/application-pdf.php), see "Printable application PDF" below |
| Add, remove, or edit a photo on the Projects page | the `$sections` array at the top of the matching file in [projects/](projects/), see below |
| Change colors, fonts, or overall look | [assets/css/style.css](assets/css/style.css) |
| Change the mobile menu, slideshow, or image lightbox behavior | [assets/js/main.js](assets/js/main.js) |
| Change what's in the top menu bar on every page | `$GLOBALS['MAIN_NAV']` inside [includes/config.php](includes/config.php) |
| Change the very top or bottom of every page (logo bar, menu, footer links) | [includes/header.php](includes/header.php) / [includes/footer.php](includes/footer.php) |
| Swap out a photo or logo | replace the file in [assets/img/](assets/img/) (keep the same filename so nothing breaks) |

For any page's body text, just open the file and edit the words between the
HTML tags (the bits like `<p>...</p>`), you don't need to touch anything
that starts with `<?php`.

## Requirements

- PHP 8.1 or newer (the Employee Dashboard and the application PDF need 8.1),
  with the `fileinfo` and `curl` extensions (on by default in most installs), plus
  `pdo_sqlite` for the dashboard's built-in database. This machine has PHP 8.4
  installed via `winget install PHP.PHP.8.4`.
- Any standard web host that runs PHP works for deployment. The live site runs on
  IIS (Windows) hosting, which is why there's a `web.config` alongside `.htaccess`.

## Previewing your changes before they go live

You can run a copy of the site on your own computer to check that an edit
looks right before it's published.

1. Open a terminal in the project's main folder (the one this README is in).
2. Run:

   ```
   php -S localhost:8000
   ```

3. Open http://localhost:8000/index.php in a web browser.
4. Leave that terminal window open while you look around. Press `Ctrl+C` in
   the terminal to stop the preview when you're done.

Refresh the browser after saving a file to see your change.

## How the pages fit together

Every page (like `index.php` or `about.php`) is a full HTML page, but three
pieces are shared and pulled in automatically so they only need to be edited
once:

- **[includes/header.php](includes/header.php)**, the top of every page: logo, menu bar.
- **[includes/footer.php](includes/footer.php)**, the bottom of every page: contact info, page links, the cookie notice.
- **[includes/config.php](includes/config.php)**, site-wide facts (phone number, email, address, the menu links) plus a couple of small logo lists. Change a value here and it updates everywhere it's used.

## Full folder guide

```
index.php, about.php, contact.php, projects.php, careers.php, employment.php
                       the main pages of the site

includes/              shared pieces every page uses
  header.php             top of the page (logo, menu)
  footer.php              bottom of the page (contact info, page links)
  config.php              phone/email/address + menu links, edited in one place
  mailer.php               sends emails for the contact/application forms
  secrets.php              API key for the email service (not in this repo,
                            see "Email setup" below)
  photo-sections.php       shared renderer for the photo galleries on the
                            Projects sub-pages, see "Editing the project
                            galleries" below
  job-posting-header.php    shared hero + back-link/Apply-Now/job-meta markup
  job-posting-footer.php    for every page in careers/, see below
  build-right-way.php      "We Build the Right Way" section, shared by the
                            homepage and About page
  associations.php         "Associations" logo strip, shared by the same two
  job-posting-standard-sections.php
                            the Work Authorization / EEO / Other Duties text
                            shared by the full-format postings
  application-pdf.php      builds the printable PDF of each job application
  pdf-logo.jpg              the logo used on that PDF
  vendor/, composer.json    the dompdf library that makes the PDF, see
                            "Printable application PDF" below, don't edit
                            these by hand

services/              one file per service page (Concrete, Agricultural,
                        Ag/Industrial Maintenance)

careers/                one file per job posting. To stop listing one without
                        deleting it, use the 'open' flag in the
                        $GLOBALS['JOBS'] list in includes/config.php (see
                        the quick-lookup table above)

projects/               supporting pages for the Projects section
                        (agricultural.php, concrete.php, special-projects.php)

assets/css/style.css   all colors and fonts, the color/font values are near
                        the top of the file if you want to tweak the palette

assets/js/main.js      small interactive bits: mobile menu, project
                        gallery slideshows, image lightbox, vendor logo
                        carousel, cookie notice

assets/img/             all photos and logos, organized into subfolders by
                        where they're used (agriculture, concrete, millwright,
                        special-projects, vendors, favicon, index). Photos
                        live in each folder's `web/` subfolder as resized,
                        compressed copies, so the site doesn't ship
                        multi-megabyte camera photos to visitors. See
                        "Adding a new photo" below.

originals/              full-size camera originals of the photos/video in
                        assets/img/, same subfolder layout. Kept here so a
                        photo can be re-cropped or re-sized later, but this
                        folder is NOT uploaded to the live site, so never
                        link a page to anything in it.

contact-handler.php    processes the "Contact Us" form
apply-handler.php      processes the "Apply for a job" form + resume upload

uploads/                where submitted resumes and an applications.csv log
                        land. This folder is blocked from being viewed on the
                        live website, to see submissions, download them via
                        FTP or your hosting provider's file manager.
```

## Editing the project galleries

[projects.php](projects.php) is the Projects overview page (the 3 cards linking out to
each gallery). The galleries themselves, [projects/concrete.php](projects/concrete.php),
[projects/agricultural.php](projects/agricultural.php), [projects/special-projects.php](projects/special-projects.php), each
start with a `$sections` array listing that gallery's categories and photos,
then hand off to the shared [includes/photo-sections.php](includes/photo-sections.php) to render them. To add,
remove, or re-caption a photo, edit that page's `$sections` array, nothing
else needs to change:

- A category with 2+ photos and `'layout' => 'carousel'` renders as an
  auto-advancing slideshow with arrows and dots.
- A category with exactly 1 photo renders as a single enlarged photo, no
  controls (there'd be nothing to navigate to).
- A category with 0 photos (`'photos' => []`) renders just its `'note'` text,
  e.g. "Photos for this section are coming soon."

## Employee Dashboard

Employees sign in at **https://rjtide.com/dashboard/** (also linked as "Employee
Login" in the footer) in one of two ways:

- **Sign in with Microsoft**: office staff, with their RJ Tide Microsoft 365
  account. When IT disables someone's Microsoft 365 account, their dashboard
  access ends too.
- **Sign in with Google**: field employees without a company email, with any
  Google (Gmail) account. The first time someone signs in with Google, they're
  held until an Admin **approves** them on the Users page (Admins get an email,
  and the dashboard home shows how many are waiting). The company doesn't
  control Google accounts, so **when a Google user leaves, turn their access off
  on the Users page**. Google accounts are never automatically Admins, even if
  they use an @rjtide.com email address.

Either button only appears once its settings are filled in on the server.

**Built so far:** sign-in, roles, **My Requests** and **Review Requests** (see
below), the **Job Openings** page (check the positions you're hiring for, the
Careers page updates immediately), the **Job Postings** page (Admins add new
postings, see below), and the **Users** page (Admins change roles or turn off
access). Document uploads are shown as "Coming soon" and come next. Timecards
aren't part of the dashboard, they're handled by a separate service.

**Employee requests:** every employee can send HR a **Time Off** request
(Vacation/PTO, Sick, Unpaid, or Other with a note), an **Address Change**, or a
**Direct Deposit Change** from **My Requests**, follow its status there, and
cancel it while it's still waiting. People with the **HR** role (and Admins)
handle them on **Review Requests**: time off is approved or denied, address and
direct deposit changes are marked done once entered into payroll, or rejected,
each with an optional note. HR is emailed when a request comes in, and the
employee is emailed when it's decided. Nobody can decide their own request.

Direct deposit bank numbers get extra protection: they're stored encrypted with
`DASHBOARD_ENCRYPTION_KEY` (setup step 3 below), only HR can see them, behind a
**Show bank details** button on that request's page, and every viewing is
recorded in "Recent activity". Emails and lists only ever show "account ending
1234". The numbers are permanently erased the moment the request is marked
done, rejected, or cancelled. Until the key is set, the direct deposit form is
switched off (time off and address changes still work). The logic for all
three is in [includes/dashboard/requests.php](includes/dashboard/requests.php).

**Job postings added on the dashboard** get their own page
(`careers/posting.php?id=...`) that looks just like the built-in ones, and
appear on Job Openings, the Careers page, and the application's position list
like any other job. The description box uses a simple format: a line starting
with `# ` is a heading, `- ` is a bullet point, and a blank line starts a new
paragraph. **Preview** shows exactly how it will look before saving. Job titles
must be unique (they're how the openings list and the application tell jobs
apart). Changing a title keeps the same web address. The 11 original postings
are built into the site and are still edited in [careers/](careers/).

**Roles** (set in `DASHBOARD_ROLES` in [includes/config.php](includes/config.php)):

| Role | Can do |
|---|---|
| Employee | My Requests (time off, address, direct deposit) |
| Office | + manage Job Openings |
| HR | + Review Requests, including viewing direct deposit bank details |
| Admin | everything: Job Openings, Job Postings, Review Requests, and the Users page |

Only give the HR role to people who handle payroll, since it can see bank details.

To let Office add postings too, add `'edit_postings'` to the Office line in
`DASHBOARD_ROLES`.

Everyone starts as an Employee the first time they sign in. The Microsoft 365
accounts in `DASHBOARD_ADMIN_EMAILS` are always Admins, so the dashboard can't
end up with nobody able to manage it. Every role and job-opening change is recorded under
"Recent activity" on the Users page. Dashboard pages are hidden from search
engines and never load Google Analytics or Clarity.

**Where things live:** pages in [dashboard/](dashboard/), building blocks in
[includes/dashboard/](includes/dashboard/) (`sign-in.php` for Microsoft and
Google sign-in, `accounts.php` for creating accounts and approvals, `auth.php`
for sessions, roles and form protection), job listing logic (openings,
added postings, the description format) in
[includes/job-postings.php](includes/job-postings.php), and the server-written
data (database, sign-in sessions, `job-openings.json`, `job-postings.json`) in
`uploads/dashboard/`, which visitors can't reach, git ignores, and the deploy
never overwrites. Back up that folder along with `uploads/resumes/`, since
added postings live only there.

### Employee Dashboard setup (one time)

**1. Register the dashboard with Microsoft** (needs a Microsoft 365 admin):

1. Go to https://entra.microsoft.com > **Applications** > **App registrations** > **New registration**.
2. Name: `RJ Tide Employee Dashboard`. Supported account types:
   **Accounts in this organizational directory only (single tenant)**.
3. Redirect URI: platform **Web**, address
   `https://rjtide.com/dashboard/auth-callback.php` (exactly this), then **Register**.
4. On the app's Overview page, copy the **Application (client) ID** and the
   **Directory (tenant) ID**.
5. **Certificates & secrets** > **New client secret** > pick 24 months > **Add**,
   then copy the secret's **Value** right away (it's only shown once).
   Put a reminder on the calendar a few weeks before it expires: when it does,
   sign-in stops working until a new secret is made and swapped in.
6. Optional, to limit who can sign in: **Enterprise applications** > the app >
   **Properties** > **Assignment required** = Yes, then add the allowed people or
   groups under **Users and groups**. Otherwise anyone with an RJ Tide account can
   sign in (as an Employee, with no extra access until an Admin gives it).

**2. Register the dashboard with Google** (for field crews; skip if you only
want Microsoft sign-in). Any Google account can do this, ideally a shared
company one so it isn't tied to one person:

1. Go to https://console.cloud.google.com, create a project named
   `RJ Tide Employee Dashboard`.
2. Open **Google Auth Platform** (called "OAuth consent screen" in some menus) >
   **Get started**. App name `RJ Tide Employee Dashboard`, your support email,
   audience **External**, then finish.
3. Under **Audience**, click **Publish app** so it's "In production". (While
   it's in "Testing", only listed test users can sign in.) The dashboard only
   asks for name and email, so Google doesn't need to review it.
4. Under **Clients** > **Create client**: type **Web application**, name
   `RJ Tide Employee Dashboard`, and under **Authorized redirect URIs** add
   `https://rjtide.com/dashboard/auth-callback.php` (exactly this; the same
   address as Microsoft's). Click **Create**.
5. Copy the **Client ID** (ends in `.apps.googleusercontent.com`) and the
   **Client secret**.

**3. Add the values to `includes/secrets.php` on the server first** (via FTP or
the hosting file manager, it's never uploaded by the deploy), before deploying,
so the sign-in page is never live half set up. See
[includes/secrets.example.php](includes/secrets.example.php) for the exact lines:
`MS_TENANT_ID`, `MS_CLIENT_ID`, `MS_CLIENT_SECRET` for Microsoft, and
`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` for Google.

Also add `DASHBOARD_ENCRYPTION_KEY`, which encrypts direct deposit bank numbers.
Make one by running this once on your computer and pasting the result between
the quotes (treat it like a password; don't email it or put it anywhere else):

```
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
```

Keep the same key from then on: if it ever changes, direct deposit requests
still waiting for review can't be read, so HR would reject them and the
employees would send them again.

**4. Deploy:** merge the dashboard work into `master` and push. The deploy
only runs for `master`.

**5. Run the setup check:** open **https://rjtide.com/dashboard/setup-check.php**.
It checks the PHP version (8.1+ needed), database support, write permission
for `uploads/dashboard/`, the Microsoft and Google settings, and that the
server can reach both, each with how to fix it. It shows pass/fail only, never secrets.
If SQLite isn't available, either ask HostPapa to enable `pdo_sqlite`, or
create a MySQL database in the control panel and fill in the three
`DASHBOARD_DB_...` lines in `secrets.php`.

**6. First sign-in:** sign in at https://rjtide.com/dashboard/ with Microsoft
as one of the `DASHBOARD_ADMIN_EMAILS` accounts, then have others sign in, set
their roles, and approve Google sign-ins on the Users page.

The dashboard always runs at `SITE_URL` (https://rjtide.com, set in
[includes/config.php](includes/config.php)): visitors arriving at
www.rjtide.com are sent there first, since Microsoft and Google only return
people to the one registered address. If the site's main address ever changes,
update `SITE_URL` and the redirect address in both Entra and Google Cloud.

### Testing the dashboard on your own computer

1. Make sure PHP's SQLite support is on: in the `php.ini` file shown by
   `php --ini`, the line `extension=pdo_sqlite` must not start with `;`.
2. In your **local** `includes/secrets.php` (never the server's), add:
   ```php
   define('DASHBOARD_LOCAL_TESTING', true);
   ```
   To try direct deposit requests too, also add a `DASHBOARD_ENCRYPTION_KEY`
   made with the command in setup step 3 (use a different key than the server's).
3. Run `php -S localhost:8000` and open http://localhost:8000/dashboard/, then
   click **Local test sign-in**.

On the **Local Testing** page (also a tab once signed in) you can sign in as
anyone, as a Microsoft-style account (mcross@rjtide.com is an Admin, anyone else
starts as an Employee) or a Google-style one (waits for approval), and switch
between everyone you've created with one click, e.g. an employee sending a
request and an HR person reviewing it. **No real emails are sent**: every email
the dashboard would send is listed at the bottom of that page instead.

This only works with that setting on, on PHP's preview server, from your own
computer, so it can never be switched on for the live site, and the deploy never
uploads the Local Testing page. Your test data lives in `uploads/dashboard/` on
your computer; delete that folder to start fresh.

## Printable application PDF

Every job application email to HR has a PDF attached, laid out like a paper
application (boxed fields, checkboxes, signature block, and a "For Office Use
Only" box), so it can be printed and filed. The email body still has the same
information as plain text.

- The layout is regular HTML and CSS inside
  [includes/application-pdf.php](includes/application-pdf.php). The checkbox
  options come from the same `APPLICATION_...` lists in `config.php` as the
  online form, so adding a checkbox there adds it to the PDF too.
- If the PDF ever fails to generate, the email still goes out without it and
  the reason is written to the server's PHP error log, so no application is lost.
- The PDF is made by the free [dompdf](https://github.com/dompdf/dompdf) library
  in `includes/vendor/`. It's committed to this repo on purpose, the FTP deploy
  just copies files and has no install step. To update it, download
  [composer.phar](https://getcomposer.org/download/) and run
  `php -d extension=zip composer.phar update --working-dir=includes`, then
  commit the changed `includes/vendor/` files.

## Adding a new photo

Photos are shown at a fixed size everywhere on the site (hero banners,
gallery boxes, showcase grids), so a phone or camera's full-resolution
original, often several megabytes, is far larger than what actually gets
displayed. Put the original in the matching [originals/](originals/) subfolder
(e.g. `originals/concrete/`), then shrink it and save the copy into
`assets/img/<folder>/web/` (e.g. `assets/img/concrete/web/`) and point the
page at that `web/` copy. Don't commit originals into `assets/img/`, anything
there gets uploaded to the live site. This machine can
do the resize without installing anything, via PowerShell:

```powershell
Add-Type -AssemblyName System.Drawing
$img = [System.Drawing.Image]::FromFile("C:\path\to\original.jpg")
# If the photo needs rotating first (phone photos sometimes do), add e.g.:
# $img.RotateFlip([System.Drawing.RotateFlipType]::Rotate90FlipNone)
$maxDim = 1600
$w = $img.Width; $h = $img.Height
if ($w -ge $h) { $newW = [Math]::Min($maxDim, $w); $newH = [int]($h * $newW / $w) }
else           { $newH = [Math]::Min($maxDim, $h); $newW = [int]($w * $newH / $h) }
$bmp = New-Object System.Drawing.Bitmap($newW, $newH)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$g.DrawImage($img, 0, 0, $newW, $newH)
$jpegCodec = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq "image/jpeg" }
$encParams = New-Object System.Drawing.Imaging.EncoderParameters(1)
$encParams.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter([System.Drawing.Imaging.Encoder]::Quality, [int64]82)
$bmp.Save("C:\path\to\assets\img\<folder>\web\<name>.jpg", $jpegCodec, $encParams)
```

That resizes to a 1600px-max dimension at 82% JPEG quality, usually a
90%+ size reduction with no visible quality loss at the sizes photos display
on the site.