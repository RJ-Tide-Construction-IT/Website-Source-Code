<?php
// Safety forms filled out on the Employee Dashboard (dashboard/safety-form.php).
// Each submission becomes a PDF that's emailed to SAFETY_FORMS_EMAIL (config.php)
// and saved under Employee Documents as a Safety Form, where the Safety role and
// the employee who filled it out can open it.
//
// To add a form or change one, edit SAFETY_FORMS below; the page, the PDF and the
// email all follow it. Each field is 'key' => [label, type, required?, options]:
//   text, textarea, date, time   ordinary answers
//   select                       one choice from 'options'
//   yesno                        Yes / No
//   names                        a list, one name per line (e.g. who attended)
// 'title' names the saved copy, built from the answers in 'title_from'.

const SAFETY_FORMS = [
    'incident' => [
        'name'        => 'Incident Report',
        'description' => 'An injury, illness, property or equipment damage, a vehicle accident, or a spill. Fill this out the same day.',
        'title_from'  => ['job_site', 'date'],
        'fields' => [
            'date'               => ['Date of incident', 'date', true],
            'time'               => ['Time', 'time', false],
            'job_site'           => ['Job site', 'text', true],
            'location'           => ['Exact location on site', 'text', false],
            'kind'               => ['Type of incident', 'select', true, ['Injury or illness', 'Property or equipment damage', 'Vehicle', 'Spill or environmental', 'Other']],
            'description'        => ['What happened?', 'textarea', true],
            'people'             => ['People involved (names and roles)', 'textarea', false],
            'injured'            => ['Was anyone hurt?', 'yesno', true],
            'injury'             => ['Injury details (who, what part of the body, how serious)', 'textarea', false],
            'treatment'          => ['Treatment', 'select', false, ['None', 'First aid on site', 'Clinic or doctor', 'Emergency room', 'Hospitalized']],
            'cause'              => ['What caused it?', 'textarea', false],
            'corrective'         => ['What has been done, or should be done, to keep it from happening again?', 'textarea', false],
            'witnesses'          => ['Witnesses (names and phone numbers)', 'textarea', false],
            'supervisor'         => ['Supervisor notified', 'text', false],
        ],
    ],
    'near_miss' => [
        'name'        => 'Near-Miss Report',
        'description' => "Something that could have hurt someone or damaged something, but didn't. Reporting these prevents the real thing.",
        'title_from'  => ['job_site', 'date'],
        'fields' => [
            'date'               => ['Date', 'date', true],
            'time'               => ['Time', 'time', false],
            'job_site'           => ['Job site', 'text', true],
            'location'           => ['Exact location on site', 'text', false],
            'description'        => ['What happened, or almost happened?', 'textarea', true],
            'potential'          => ['What could have happened?', 'select', true, ['Minor injury', 'Serious injury', 'Fatality', 'Property or equipment damage', 'Other']],
            'cause'              => ['What caused it?', 'textarea', false],
            'corrective'         => ['What has been done, or should be done, about it?', 'textarea', false],
            'reported_to'        => ['Reported to (supervisor)', 'text', false],
        ],
    ],
    'toolbox_talk' => [
        'name'        => 'Toolbox Talk Sign-In',
        'description' => 'Record a toolbox talk and who attended.',
        'title_from'  => ['topic', 'date'],
        'fields' => [
            'date'               => ['Date', 'date', true],
            'job_site'           => ['Job site', 'text', true],
            'topic'              => ['Topic', 'text', true],
            'presenter'          => ['Presented by', 'text', true],
            'key_points'         => ['Key points covered', 'textarea', false],
            'attendees'          => ['Attendees (one name per line)', 'names', true],
            'concerns'           => ['Questions or concerns raised', 'textarea', false],
        ],
    ],
];

/**
 * Checks a submitted form.
 * @return array [answers keyed by field, list of problems]
 */
function validate_safety_form(array $form, array $post): array {
    $answers = [];
    $errors  = [];
    foreach ($form['fields'] as $key => $field) {
        [$label, $type, $required] = $field;
        $value = posted_text($post, $key);
        if ($type === 'names') {
            $value = implode("\n", array_filter(array_map('trim', preg_split('/\R/u', $value))));
        }
        if ($value === '') {
            if ($required) {
                $errors[] = "Fill in \"$label\".";
            }
        } elseif ($type === 'date' && !valid_date($value)) {
            $errors[] = "\"$label\" isn't a valid date.";
        } elseif ($type === 'time' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value)) {
            $errors[] = "\"$label\" isn't a valid time.";
        } elseif (($type === 'select' && !in_array($value, $field[3], true)) || ($type === 'yesno' && !in_array($value, ['Yes', 'No'], true))) {
            $errors[] = "Choose an answer for \"$label\".";
        } elseif (strlen($value) > ($type === 'text' ? 200 : 5000)) {
            $errors[] = "\"$label\" is too long.";
        }
        $answers[$key] = $value;
    }
    if (empty($post['confirm'])) {
        $errors[] = 'Check the box confirming the form is accurate.';
    }
    return [$answers, $errors];
}

// An answer as people read it: dates as "Oct 14, 2026", times as "2:30 PM".
function safety_answer(array $field, string $value): string {
    if ($value === '') {
        return '';
    }
    if ($field[1] === 'date') {
        return format_date($value);
    }
    return $field[1] === 'time' ? date('g:i A', strtotime($value)) : $value;
}

// e.g. "Incident Report - Smith Farms - Oct 14, 2026"
function safety_form_title(array $form, array $answers): string {
    $parts = array_map(fn($key) => safety_answer($form['fields'][$key], $answers[$key]), $form['title_from']);
    return implode(' - ', array_merge([$form['name']], array_filter($parts)));
}

// The filled-out form as a printable PDF (same dompdf library as the job application PDF).
function render_safety_form_pdf(array $form, array $answers, array $user, DateTime $submitted): string {
    require_once dirname(__DIR__) . '/pdf.php';
    $e = fn(string $v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

    $rows = '';
    foreach ($form['fields'] as $key => $field) {
        $value = safety_answer($field, $answers[$key]);
        $shown = $field[1] === 'names' && $value !== ''
            ? '<ol>' . implode('', array_map(fn($n) => '<li>' . $e($n) . '</li>', explode("\n", $value))) . '</ol>'
            : nl2br($e($value !== '' ? $value : '-'));
        $rows .= '<tr><th>' . $e($field[0]) . '</th><td>' . $shown . '</td></tr>';
    }
    $logo = dirname(__DIR__) . '/pdf-logo.jpg';

    $html = '<html><head><meta charset="UTF-8"><style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #222; }
        .head { width: 100%; border-bottom: 2px solid #0F172A; margin-bottom: 12pt; }
        .head td { vertical-align: bottom; padding-bottom: 6pt; }
        h1 { font-size: 16pt; margin: 0; color: #0F172A; text-align: right; }
        table.f { width: 100%; border-collapse: collapse; }
        table.f th { width: 32%; text-align: left; vertical-align: top; background: #f2f2f2; font-weight: bold; }
        table.f th, table.f td { border: 1px solid #999; padding: 5pt 7pt; }
        ol { margin: 0; padding-left: 16pt; }
        .sig { margin-top: 14pt; font-size: 9pt; }
    </style></head><body>
        <table class="head"><tr><td><img src="' . $logo . '" style="height:42pt"></td><td><h1>' . $e($form['name']) . '</h1></td></tr></table>
        <table class="f">' . $rows . '</table>
        <p class="sig">Submitted electronically by <strong>' . $e($user['name']) . '</strong> (' . $e($user['email']) . ') on '
            . $submitted->format('M j, Y \a\t g:i A T') . ', who confirmed the information is accurate to the best of their knowledge.</p>
    </body></html>';

    return render_pdf($html)->output();
}

/**
 * Emails the completed form's PDF to SAFETY_FORMS_EMAIL and saves a copy under
 * Employee Documents (only when the encryption key is set, since every saved
 * document is encrypted). Returns false if the email couldn't be sent.
 */
function submit_safety_form(array $user, string $formKey, array $answers): bool {
    $form      = SAFETY_FORMS[$formKey];
    $submitted = new DateTime('now', new DateTimeZone('America/Chicago')); // the office is in Iowa
    $title     = safety_form_title($form, $answers);
    $pdf       = render_safety_form_pdf($form, $answers, $user, $submitted);

    if (encryption_ready()) {
        store_document($user, 'safety', $title, 'Filled out on the dashboard', $pdf, 'application/pdf', $title . '.pdf', null);
    }
    audit((int) $user['id'], 'safety_form_submitted', $user['name'] . ' submitted a ' . $title);

    $body = $user['name'] . ' (' . $user['email'] . ') submitted a ' . $form['name'] . " on the Employee Dashboard.\n\n";
    foreach ($form['fields'] as $key => $field) {
        $body .= $field[0] . ': ' . (safety_answer($field, $answers[$key]) ?: '-') . "\n";
    }
    $body .= "\nThe completed form is attached as a PDF, and saved under Employee Documents:\n"
           . dashboard_url('documents-review.php') . "\n";
    $sent = dashboard_send_email(SAFETY_FORMS_EMAIL, 'Safety form: ' . header_safe($title) . ' (from ' . header_safe($user['name']) . ')',
                                 $body, [['name' => $title . '.pdf', 'content' => $pdf]], $user['email']);
    if (!$sent) {
        error_log('submit_safety_form: email to ' . SAFETY_FORMS_EMAIL . ' failed, the form is saved under Employee Documents');
    }
    return $sent;
}
