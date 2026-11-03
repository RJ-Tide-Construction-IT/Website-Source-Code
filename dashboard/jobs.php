<?php
// Old address of the Job Openings page, now part of Job Postings
// (dashboard/postings.php). Kept so bookmarks still work.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/dashboard/postings.php', true, 301);
exit;
