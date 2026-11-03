<?php
// Opens one uploaded document (document.php?id=...), decrypted, for the
// employee who uploaded it or someone whose role reviews that type. Anyone
// else opening a payroll form is recorded in the activity log.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

$doc = find_document((int) ($_GET['id'] ?? 0));
if (!$doc || !can_open_document($user, $doc)) {
    flash('error', 'That document wasn\'t found, or you don\'t have access to it.');
    redirect(BASE_URL . '/dashboard/documents.php');
}
$contents = document_contents($doc);
if ($contents === null) {
    flash('error', $doc['stored_name']
        ? 'That document can\'t be read (the encryption key may have changed). Ask the employee to upload it again.'
        : 'That document has already been processed or removed, so the file was deleted.');
    redirect(BASE_URL . '/dashboard/' . ((int) $doc['user_id'] === (int) $user['id'] ? 'documents.php' : 'documents-review.php'));
}
if ($doc['type'] === 'payroll' && (int) $doc['user_id'] !== (int) $user['id']) {
    audit((int) $user['id'], 'document_viewed', $user['name'] . ' opened ' . $doc['employee_name'] . '\'s ' . document_type_label($doc['type']) . ': ' . $doc['title']);
}

// PDFs and JPG/PNG photos open in the browser; HEIC downloads (browsers can't
// show it). The sandbox header stops the file from running anything even if
// it were crafted to try.
$inline = in_array($doc['mime'], ['application/pdf', 'image/jpeg', 'image/png'], true);
header('Content-Type: ' . $doc['mime']);
header('Content-Length: ' . strlen($contents));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . str_replace('"', '', $doc['file_name']) . '"');
header('Content-Security-Policy: sandbox');
header('X-Content-Type-Options: nosniff');
echo $contents;
