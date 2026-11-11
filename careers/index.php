<?php
// The careers/ folder has no page of its own, so anyone who opens
// rjtide.com/careers/ is sent to the Careers page instead of an error.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/careers.php', true, 301);
exit;
