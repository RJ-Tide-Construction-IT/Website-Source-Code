<?php
// The services/ folder has no page of its own, so anyone who opens
// rjtide.com/services/ is sent to the homepage, which lists every service,
// instead of an error.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
header('Location: ' . BASE_URL . '/', true, 301);
exit;
