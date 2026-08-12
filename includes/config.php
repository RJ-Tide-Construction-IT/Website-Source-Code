<?php
// Site-wide constants. Edit these in one place instead of hunting through pages.

define('SITE_NAME', 'RJ Tide Construction Company, Inc.');
define('SITE_PHONE', '712-255-0175');
define('SITE_PHONE_TEL', '+17122550175');
define('SITE_EMAIL', 'info@rjtide.com');
define('SITE_ADDRESS', '1821 US Highway 20, Lawton, IA 51030');

// Root-relative base path. Leave as '' when the site is hosted at the domain root
// (e.g. https://rjtide.com/). Set to '/subfolder' if it's hosted in a subfolder.
define('BASE_URL', '');

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

// Vendor logos, shared by the Millwright, Design Build, and Ag/Industrial pages.
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
    'Home'         => '/index.php',
    'About'        => '/about.php',
    'Concrete'     => '/services/concrete-services.php',
    'Millwright'   => '/services/millwright-services.php',
    'Agricultural' => '/services/agricultural-services.php',
    'Industrial'   => '/services/ag-industrial-maintenance.php',
    'Projects'     => '/projects.php',
    'Careers'      => '/careers.php',
];
