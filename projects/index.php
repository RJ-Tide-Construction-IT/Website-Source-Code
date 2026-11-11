<?php
// The projects/ folder has no page of its own, so anyone who opens
// rjtide.com/projects/ is sent to the Projects page instead of an error.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/projects.php', true, 301);
exit;
