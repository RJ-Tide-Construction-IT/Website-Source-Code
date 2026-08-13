# RJ Tide Construction — website source

This is the code behind the RJ Tide Construction website. It's written in **PHP**,
a language that mixes regular HTML with small chunks of code (anything between
`<?php ... ?>`) to fill in things like the phone number or a list of projects
automatically instead of typing them out on every page.

You don't need to know PHP to make most changes — the sections below tell you
exactly which file to open for common edits.

## "I want to change..." — quick lookup

| I want to... | Open this file |
|---|---|
| Change the phone number, email, or address shown site-wide | [includes/config.php](includes/config.php) |
| Change the text/wording on the homepage | [index.php](index.php) |
| Change the About page text | [about.php](about.php) |
| Change a service page (Concrete, Agricultural, Industrial Maintenance) | the matching file in [services/](services/) |
| Add/edit/remove a job posting | the matching file in [careers/](careers/), and the list on [careers.php](careers.php) |
| Rename or fix a project on the Projects page | [includes/projects-data.php](includes/projects-data.php) — see below |
| Change colors, fonts, or overall look | [assets/css/style.css](assets/css/style.css) |
| Change the mobile menu, slideshow, or image lightbox behavior | [assets/js/main.js](assets/js/main.js) |
| Change what's in the top menu bar on every page | `$GLOBALS['MAIN_NAV']` inside [includes/config.php](includes/config.php) |
| Change the very top or bottom of every page (logo bar, footer, social links) | [includes/header.php](includes/header.php) / [includes/footer.php](includes/footer.php) |
| Swap out a photo or logo | replace the file in [assets/img/](assets/img/) (keep the same filename so nothing breaks) |

For any page's body text, just open the file and edit the words between the
HTML tags (the bits like `<p>...</p>`) — you don't need to touch anything
that starts with `<?php`.

## Requirements

- PHP 8.x with the `fileinfo` extension enabled (on by default in most installs).
  This machine has PHP 8.4 installed via `winget install PHP.PHP.8.4`.
- Any standard web host that runs PHP (shared hosting, PHP-FPM + nginx, Apache, etc.)
  works for deployment — this is exactly what your current WordPress host already runs.

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

- **[includes/header.php](includes/header.php)** — the top of every page: logo, menu bar.
- **[includes/footer.php](includes/footer.php)** — the bottom of every page: contact info, social links.
- **[includes/config.php](includes/config.php)** — site-wide facts (phone number, email, address, the menu links) plus a couple of small logo lists. Change a value here and it updates everywhere it's used.

## Full folder guide

```
index.php, about.php, contact.php, projects.php, careers.php, employment.php
                       the main pages of the site

includes/              shared pieces every page uses
  header.php             top of the page (logo, menu)
  footer.php              bottom of the page (contact info, socials)
  config.php              phone/email/address + menu links, edited in one place
  mailer.php               sends emails for the contact/application forms
  secrets.php              API key for the email service (not in this repo —
                            see "Email setup" below)
  projects-data.php        the project history shown on the Projects page

services/              one file per service page (Concrete, Agricultural,
                        Ag/Industrial Maintenance)

careers/                one file per job posting

projects/               supporting pages for the Projects section
                        (agricultural.php, concrete.php, special-projects.php)

assets/css/style.css   all colors and fonts — the color/font values are near
                        the top of the file if you want to tweak the palette

assets/js/main.js      small interactive bits: mobile menu, homepage
                        slideshow, image lightbox

assets/img/             all photos and logos, organized into subfolders by
                        where they're used (agriculture, concrete, millwright,
                        special-projects, vendors, favicon, index)

contact-handler.php    processes the "Contact Us" form
apply-handler.php      processes the "Apply for a job" form + resume upload

uploads/                where submitted resumes and an applications.csv log
                        land. This folder is blocked from being viewed on the
                        live website — to see submissions, download them via
                        FTP or your hosting provider's file manager.
```

## Editing the projects list

[includes/projects-data.php](includes/projects-data.php) holds the project/contract records shown on the
Projects page (id, name, type), originally generated from an Acumatica export.
To rename a project, find its record and edit the text inside the
`'name' => '...'` quotes — nothing else needs to change, the page rebuilds the
pie chart, legend, and category lists from this list automatically every time
it loads. If a name contains an apostrophe, put a backslash before it, like
`'Farmer\'s Co-op'`. This file is hand-maintained now; if someone later
imports a fresh Acumatica export, that will overwrite any manual edits made
here in the meantime.