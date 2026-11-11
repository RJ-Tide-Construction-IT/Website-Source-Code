<?php
// The safety-data-sheets/ folder holds one page per group, so anyone who opens
// the folder itself is sent to the main Safety Data Sheets page instead of an
// error. This is not a group page: don't copy it to make a new group.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/safety-data-sheets.php', true, 301);
exit;
