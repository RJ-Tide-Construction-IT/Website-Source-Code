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
| Add/edit a job posting | the matching file in [careers/](careers/), and the `$GLOBALS['JOBS']` list in [includes/config.php](includes/config.php) |
| Stop listing a position you're not hiring for right now | `$GLOBALS['JOBS']` in [includes/config.php](includes/config.php) — find the job and change its `'open' => true` to `'open' => false` |
| Change the EEO / Work Authorization / Other Duties wording on job postings | [includes/job-posting-standard-sections.php](includes/job-posting-standard-sections.php) (shared by every posting that shows them) |
| Add/remove a checkbox on the job application (licenses, physical requirements, experience) | the `APPLICATION_...` lists in [includes/config.php](includes/config.php) |
| Add, remove, or edit a photo on the Projects page | the `$sections` array at the top of the matching file in [projects/](projects/), see below |
| Change colors, fonts, or overall look | [assets/css/style.css](assets/css/style.css) |
| Change the mobile menu, slideshow, or image lightbox behavior | [assets/js/main.js](assets/js/main.js) |
| Change what's in the top menu bar on every page | `$GLOBALS['MAIN_NAV']` inside [includes/config.php](includes/config.php) |
| Change the very top or bottom of every page (logo bar, footer, social links) | [includes/header.php](includes/header.php) / [includes/footer.php](includes/footer.php) |
| Swap out a photo or logo | replace the file in [assets/img/](assets/img/) (keep the same filename so nothing breaks) |

For any page's body text, just open the file and edit the words between the
HTML tags (the bits like `<p>...</p>`), you don't need to touch anything
that starts with `<?php`.

## Requirements

- PHP 8.x with the `fileinfo` extension enabled (on by default in most installs).
  This machine has PHP 8.4 installed via `winget install PHP.PHP.8.4`.
- Any standard web host that runs PHP (shared hosting, PHP-FPM + nginx, Apache, etc.)
  works for deployment, this is exactly what your current WordPress host already runs.

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
- **[includes/footer.php](includes/footer.php)**, the bottom of every page: contact info, social links.
- **[includes/config.php](includes/config.php)**, site-wide facts (phone number, email, address, the menu links) plus a couple of small logo lists. Change a value here and it updates everywhere it's used.

## Full folder guide

```
index.php, about.php, contact.php, projects.php, careers.php, employment.php
                       the main pages of the site

includes/              shared pieces every page uses
  header.php             top of the page (logo, menu)
  footer.php              bottom of the page (contact info, socials)
  config.php              phone/email/address + menu links, edited in one place
  mailer.php               sends emails for the contact/application forms
  secrets.php              API key for the email service (not in this repo,
                            see "Email setup" below)
  photo-sections.php       shared renderer for the photo galleries on the
                            Projects sub-pages, see "Editing the project
                            galleries" below
  job-posting-header.php    shared hero + back-link/Apply-Now/job-meta markup
  job-posting-footer.php    for every page in careers/, see below
  job-posting-standard-sections.php
                            the Work Authorization / EEO / Other Duties text
                            shared by the full-format postings

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

assets/js/main.js      small interactive bits: mobile menu, homepage
                        slideshow, image lightbox

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