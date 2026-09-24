<?php
// Site-wide constants. Edit these in one place instead of hunting through pages.

define('SITE_NAME', 'RJ Tide Construction Company, Inc.');
define('SITE_PHONE', '712-255-0175');
define('SITE_PHONE_TEL', '+17122550175');
define('SITE_EMAIL', 'info@rjtide.com');
define('SITE_ADDRESS', '1821 US Highway 20, Lawton, IA 51030');

// Shown on the Agricultural/Industrial pages in place of the phone number.
// This inbox needs to actually exist (an alias/mailbox forwarding to
// shodgson@rjtide.com) in Microsoft 365, that setup happens over there, not here.
define('AG_EMAIL', 'ag@rjtide.com');

// Where form submissions actually get delivered, separate from SITE_EMAIL
// (the address shown publicly on the site) so the two can change independently.
define('CAREERS_EMAIL', 'tanderson@rjtide.com');            // every job application
define('CONTACT_EMAIL_DEFAULT', 'mcross@rjtide.com');       // contact form: no specific-department match
define('CONTACT_EMAIL_AGRICULTURAL', 'shodgson@rjtide.com'); // contact form: "Agricultural" checked
define('CONTACT_EMAIL_CONCRETE', 'khodgson@rjtide.com');     // contact form: "Concrete" checked

// Root-relative base path. Leave as '' when the site is hosted at the domain root
// (e.g. https://rjtide.com/). Set to '/subfolder' if it's hosted in a subfolder.
define('BASE_URL', '');

// Analytics tracking IDs, both public identifiers (they're visible in every
// page's HTML source), not secrets, safe to keep here rather than in
// secrets.php. Leave either one blank to skip loading that script.
// GA4: Google Analytics admin > Data Streams > your web stream > "Measurement ID" (starts with "G-").
define('GA4_MEASUREMENT_ID', 'G-E8E5BGQ1C5');
// Clarity: clarity.microsoft.com > Settings > Setup > "Project ID".
define('CLARITY_PROJECT_ID', 'ylxgkwutz5');

// Strips CR/LF from a value before it's used in an email header (Subject, Reply-To)
// so form input can't inject extra headers into an outgoing email.
function header_safe(string $value): string {
    return trim(str_replace(["\r", "\n"], '', $value));
}

// Association logos linked from the homepage/about page.
$GLOBALS['ASSOCIATIONS'] = [
    ['name' => 'American Concrete Institute',            'img' => 'aci-logo.webp',  'url' => 'https://www.concrete.org'],
    ['name' => 'National Center for Employee Ownership',  'img' => 'nceo-logo.webp', 'url' => 'https://www.nceo.org'],
    ['name' => 'The ESOP Association',                    'img' => 'esop-logo.webp', 'url' => 'https://www.esopassociation.org'],
];

// Vendor logos, shared by the Agricultural and Ag/Industrial Maintenance pages.
$GLOBALS['VENDORS'] = [
    ['name' => 'Sukup Manufacturing',   'img' => 'SUKUP-Logo.webp',           'url' => 'https://www.sukup.com'],
    ['name' => 'GSI (Grain Systems)',   'img' => 'GSI-Logo.webp',             'url' => 'https://www.grainsystems.com'],
    ['name' => 'Chief Agri',            'img' => 'chief-logo.png',            'url' => 'https://agri.chiefind.com'],
    ['name' => 'AGI',                   'img' => 'AGI-Logo.webp',             'url' => 'https://www.aggrowth.com'],
    ['name' => 'Schlagel',              'img' => 'Schlagel-logo.webp',        'url' => 'https://www.schlagel.com'],
    ['name' => 'Warrior Mfg.',          'img' => 'warrior-logo.webp',         'url' => 'https://www.warriormfgllc.com'],
    ['name' => 'Baker-Rullman',         'img' => 'baker-rullman-logo.webp',   'url' => 'https://www.baker-rullman.com'],
    ['name' => 'Springland Mfg.',       'img' => 'springland-logo.webp',      'url' => 'https://www.springland.ca'],
    ['name' => 'Schuld Bushnell',       'img' => 'schuld-bushnell-logo.webp', 'url' => 'https://schuldbushnell.com'],
    ['name' => 'Sudenga Industries',    'img' => 'Sudenga-logo.webp',         'url' => 'https://www.sudenga.com'],
    ['name' => '4B Components',         'img' => '4b-Components-logo.webp',   'url' => 'https://www.go4b.com/usa'],
    ['name' => 'Bin Gator',             'img' => 'Bin-Gator-logo.webp',       'url' => 'https://bingator.com'],
];

// Primary navigation, shared by header.php on every page.
$GLOBALS['MAIN_NAV'] = [
    'About'        => '/about.php',
    'Concrete'     => '/services/concrete-services.php',
    'Agricultural' => '/services/agricultural-services.php',
    'Industrial'   => '/services/ag-industrial-maintenance.php',
    'Careers'      => '/careers.php',
];

// "I'm interested in" checkboxes on the Contact form. contact-handler.php
// checks submissions against this same list so only these values can ever
// end up in the notification email, no matter what's POSTed.
$GLOBALS['CONTACT_INTERESTS'] = ['Concrete', 'Agricultural', 'Industrial', 'Other'];

// Checkbox/dropdown options on the Job Application form (employment.php).
// apply-handler.php reads these same lists, so a checkbox added here shows up
// on the form AND in the HR email, and only these values can ever end up in
// that email, no matter what's POSTed.
// Experience skills: 'field_key' => 'Label shown on the form and in the email'.
$GLOBALS['APPLICATION_EXPERIENCE_SKILLS'] = [
    'concrete_flatwork'    => 'Concrete Flatwork',
    'concrete_foundations' => 'Concrete Foundations',
    'heavy_equipment'      => 'Heavy Equipment Operator',
    'carpentry'            => 'Finish/Rough Carpentry',
    'millwright'           => 'Millwright',
];
$GLOBALS['APPLICATION_LICENSES'] = [
    'State Driver\'s License', 'CDL', 'First Aid/CPR', 'Forklift',
    'ACI Certified', 'Welding', 'NCCCO, Crane Operator', 'NCCCO, Rigger',
];
$GLOBALS['APPLICATION_PHYSICAL_REQUIREMENTS'] = [
    'Lift 50 lbs, 10 times a day',
    'Climb up to 25 ft vertically without rest',
    'Bend at the waist for long periods of time',
    'Lift 20 lbs overhead, 50 times',
    'Ability to work at 100 ft or higher',
];

// Every job posting. Shared by careers.php (which also uses 'open' to decide
// what's listed, see the README) and employment.php's Position dropdown, so
// the two can't drift out of sync with each other.
// To stop showing a position (not currently hiring for it), change its
// 'open' value below from true to false, its posting page still works at
// the same address in case anyone has the link saved, it just won't be
// listed on careers.php. Set it back to true whenever you're hiring for it again.
$GLOBALS['JOBS'] = [
    ['title' => 'Craftsman 1',                 'href' => '/careers/craftsman-1.php',               'open' => true],
    ['title' => 'Craftsman 2',                 'href' => '/careers/craftsman-2.php',               'open' => true],
    ['title' => 'Craftsman 3',                 'href' => '/careers/craftsman-3.php',               'open' => true],
    ['title' => 'Concrete Foreman',            'href' => '/careers/foreman.php',                   'open' => false],
    ['title' => 'Concrete Superintendent',     'href' => '/careers/concrete-superintendent.php',    'open' => false],
    ['title' => 'Millwright Superintendent',   'href' => '/careers/millwright-superintendent.php',  'open' => false],
    ['title' => 'Millwright Foreman',          'href' => '/careers/millwright-foreman.php',         'open' => false],
    ['title' => 'Project Manager / Estimator', 'href' => '/careers/project-manager-estimator.php',  'open' => false],
    ['title' => 'Project Engineer',            'href' => '/careers/project-engineer.php',           'open' => false],
    ['title' => 'Millwright 1',                'href' => '/careers/millwright-1.php',               'open' => true],
    ['title' => 'Millwright 2',                'href' => '/careers/millwright-2.php',               'open' => true],
];
