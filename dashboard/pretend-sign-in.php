<?php
// LOCAL TESTING ONLY: a pretend Microsoft or Google sign-in page, so the whole
// sign-in flow (buttons, return page, security checks, approvals) can be tried
// on your own computer without registering anything with Microsoft or Google.
// The Microsoft/Google buttons use it only while their real settings are blank.
// Works only when pretend_sign_in_allowed() is true (same rules as the local
// test sign-in), and the deploy never uploads this file.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

$method  = is_string($_REQUEST['service'] ?? null) ? $_REQUEST['service'] : '';
$service = sign_in_services()[$method] ?? null;
if (!pretend_sign_in_allowed() || !$service || empty($service['pretend'])) {
    http_response_code(404);
    exit;
}

// The sign-in request, as sent by login.php (kept in hidden fields on submit).
$request = [];
foreach (['client_id', 'redirect_uri', 'response_type', 'state', 'nonce', 'code_challenge', 'code_challenge_method'] as $key) {
    $request[$key] = posted_text($_REQUEST, $key);
}

// Check the request the way the real services do.
$problem = match (true) {
    $request['client_id'] !== $service['client_id']   => 'unknown client_id',
    $request['redirect_uri'] !== sign_in_redirect_uri() => 'redirect_uri doesn\'t match the registered address',
    $request['response_type'] !== 'code'               => 'response_type must be "code"',
    $request['code_challenge_method'] !== 'S256' || $request['code_challenge'] === '' => 'missing PKCE code challenge',
    $request['state'] === '' || $request['nonce'] === '' => 'missing state or nonce',
    default => null,
};
if ($problem) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("Pretend {$service['label']} sign-in refused the request: $problem.");
}

$backTo = fn(array $params) => $request['redirect_uri'] . '?' . http_build_query($params + ['state' => $request['state']]);

if (form_submitted()) {
    if (posted_text($_POST, 'action') === 'cancel') {
        redirect($backTo(['error' => 'access_denied']));
    }
    $email = strtolower(posted_text($_POST, 'email'));
    $name  = posted_text($_POST, 'name');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a name and a valid email.');
        redirect(BASE_URL . '/dashboard/pretend-sign-in.php?' . http_build_query(['service' => $method] + $request));
    }

    // The claims the real service would put in the ID token. The same email
    // always gets the same account id, like a real account would.
    $claims = ['aud' => $request['client_id'], 'nonce' => $request['nonce'], 'iat' => time(), 'exp' => time() + 3600, 'name' => $name];
    if ($method === 'microsoft') {
        $claims += [
            'iss' => 'https://login.microsoftonline.com/' . strtolower($service['tenant']) . '/v2.0',
            'tid' => $service['tenant'],
            'oid' => 'pretend-' . substr(hash('sha256', $email), 0, 24),
            'preferred_username' => $email,
        ];
    } else {
        $claims += [
            'iss' => 'https://accounts.google.com',
            'sub' => (string) hexdec(substr(hash('sha256', $email), 0, 12)),
            'email' => $email,
            'email_verified' => !empty($_POST['email_verified']),
        ];
    }
    redirect($backTo(['code' => pretend_issue_code($method, $request['client_id'], $request['code_challenge'], $claims)]));
}

dashboard_page_start('Pretend ' . $service['label'] . ' Sign-In', null);
?>
<div class="dash-signin">
    <div class="form-note dash-notice">Local testing only. This page stands in for
        <?= htmlspecialchars($service['label']) ?>&rsquo;s real sign-in page, so you can try the whole
        sign-in flow without registering anything. It&rsquo;s never on the live site.</div>
    <form method="post" class="form-width dash-signin__form">
        <?= csrf_field() ?>
        <input type="hidden" name="service" value="<?= htmlspecialchars($method) ?>">
        <?php foreach ($request as $key => $value): ?>
        <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
        <?php endforeach; ?>
        <div class="form-field"><label for="name">Name</label><input type="text" id="name" name="name" required></div>
        <div class="form-field"><label for="email">Email</label><input type="email" id="email" name="email" required
            placeholder="<?= $method === 'microsoft' ? 'e.g. mcross@rjtide.com' : 'e.g. joe.crew@gmail.com' ?>"></div>
        <?php if ($method === 'google'): ?>
        <div class="form-field">
            <label class="checkbox-option"><input type="checkbox" name="email_verified" value="1" checked>
                Email is verified (untick to test that unverified Google accounts are refused)</label>
        </div>
        <?php endif; ?>
        <div class="dash-buttons">
            <button type="submit" name="action" value="sign_in" class="btn">Sign in</button>
            <button type="submit" name="action" value="cancel" class="btn btn--outline-dark" formnovalidate>Cancel</button>
        </div>
    </form>
</div>
<?php dashboard_page_end(); ?>
