# RJ Tide Construction — website source

Plain PHP with shared includes. 

## Requirements

- PHP 8.x with the `fileinfo` extension enabled (on by default in most installs).
  This machine has PHP 8.4 installed via `winget install PHP.PHP.8.4`.
- Any standard web host that runs PHP (shared hosting, PHP-FPM + nginx, Apache, etc.)
  works for deployment — this is exactly what your current WordPress host already runs.

## Local preview

From the project root:

```
php -S localhost:8000
```

Then open http://localhost:8000/index.php in a browser. PHP's built-in server sets
`DOCUMENT_ROOT` correctly for the includes to resolve.

## Structure

```
/includes/       header.php, footer.php, config.php (site-wide constants + nav)
/includes/       mailer.php (Brevo email helper)
/includes/       secrets.php (API key — gitignored), secrets.example.php (template)
/includes/       projects-data.php (project/contract history — see "Editing the projects list")
/assets/css/     style.css — all design tokens (colors, fonts) at the top
/assets/js/      main.js — mobile nav toggle, slideshow, lightbox
/assets/img/     images (logos, associations, vendor logos, projects)
/services/       9 service pages (concrete, millwright, agricultural, etc.)
/careers/        9 job posting pages
/uploads/        resume uploads + applications.csv land here — blocked from the
                 web entirely by .htaccess; retrieve via FTP/hosting file manager
index.php, about.php, contact.php, projects.php, careers.php, employment.php
contact-handler.php   processes the contact form
apply-handler.php     processes job applications + resume upload
```

## Editing content

Every page is a plain PHP file with HTML in it, edit the text directly. Site-wide
values (phone number, email, address, nav menu) live in `includes/config.php` so you
only change them once.

### Editing the projects list

`includes/projects-data.php` holds the 355 project/contract records shown on the
Projects page (id, name, type), generated from an Acumatica export. To rename a
project, find its record and edit the text inside the `'name' => '...'` quotes —
nothing else needs to change, the page rebuilds the pie chart, legend, and category
lists from this array on every load. If a name contains an apostrophe, escape it
with a backslash (`'Farmer\'s Co-op'`). This file is hand-maintained now; if you
later hand over a fresh Acumatica export, regenerating from it will overwrite any
manual edits made here in the meantime.