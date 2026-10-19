<?php
// Cloudflare Turnstile, the "are you human?" check on public forms.
// Keys come from includes/secrets.php (TURNSTILE_SITE_KEY / TURNSTILE_SECRET_KEY).
// Until both are filled in, the check is switched off and forms work as before.
//
// On a form page, call turnstile_widget() inside the <form>, just above the
// submit button. In its handler, reject the submission if turnstile_passed()
// returns false.
if (is_file(__DIR__ . '/secrets.php')) {
    require_once __DIR__ . '/secrets.php';
}

function turnstile_enabled(): bool {
    return defined('TURNSTILE_SITE_KEY') && TURNSTILE_SITE_KEY !== ''
        && defined('TURNSTILE_SECRET_KEY') && TURNSTILE_SECRET_KEY !== '';
}

// The checkbox widget plus Cloudflare's script. Prints nothing when switched off.
// assets/js/main.js stops the form submitting until the check is done.
function turnstile_widget(): void {
    if (!turnstile_enabled()) {
        return;
    }
    echo '<div class="form-field">'
       . '<div class="cf-turnstile" data-sitekey="' . htmlspecialchars(TURNSTILE_SITE_KEY) . '"></div>'
       . "</div>\n"
       . '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' . "\n";
}

// Asks Cloudflare, server-to-server, whether this submission passed. Always
// true when switched off. If Cloudflare can't be reached the submission is
// rejected, so a bot can't get through just by the check failing.
function turnstile_passed(): bool {
    if (!turnstile_enabled()) {
        return true;
    }
    $token = posted_text($_POST, 'cf-turnstile-response');
    if ($token === '') {
        return false;
    }
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret'   => TURNSTILE_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]),
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if ($response === false) {
        error_log('turnstile_passed: could not reach Cloudflare');
        return false;
    }
    $result = json_decode($response, true);
    return is_array($result) && !empty($result['success']);
}
