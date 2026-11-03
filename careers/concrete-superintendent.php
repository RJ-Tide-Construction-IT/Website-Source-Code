<?php
// Old address of one of the original job postings, kept so links already
// shared (job boards, emails, search results) still work. The posting itself
// is now edited on the Employee Dashboard and shown by careers/posting.php.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/careers/posting.php?id=' . basename(__FILE__, '.php'), true, 301);
exit;
