<?php
// Old address of the Millwright Services page (removed in August 2026), kept so
// links and search results still work. Millwright work is now on the
// Industrial Maintenance page.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/services/ag-industrial-maintenance.php', true, 301);
exit;
