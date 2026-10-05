<?php
// Checks each sign-in service's ID-token rules with sample claims (no network).
// Prints one PASS/FAIL line per check; run by tests/run-tests.ps1.
$root = dirname(__DIR__, 2);
require $root . '/includes/config.php';
require $root . '/tests/fixtures/fake-sign-in-settings.php';
require $root . '/includes/dashboard/db.php';
require $root . '/includes/dashboard/auth.php';
require $root . '/includes/dashboard/accounts.php';
require $root . '/includes/dashboard/sign-in.php';

function attempt(string $label, callable $identity, array $claims, bool $shouldPass): void {
    try {
        [, $email] = $identity($claims);
        $ok = $shouldPass;
        $detail = $email;
    } catch (RuntimeException $e) {
        $ok = !$shouldPass;
        $detail = $e->getMessage();
    }
    echo ($ok ? 'PASS' : 'FAIL'), "|$label|$detail\n";
}
function expect(string $label, bool $ok): void {
    echo ($ok ? 'PASS' : 'FAIL'), "|$label|\n";
}

$services = sign_in_services();

$ms = $services['microsoft']['identity'];
$msGood = ['tid' => MS_TENANT_ID, 'iss' => 'https://login.microsoftonline.com/' . MS_TENANT_ID . '/v2.0', 'oid' => 'oid-123', 'preferred_username' => 'Mcross@RJTide.com', 'name' => 'Mitchel Cross'];
attempt('Microsoft: RJ Tide account accepted', $ms, $msGood, true);
attempt('Microsoft: another company\'s account refused', $ms, ['tid' => '99999999-2222-3333-4444-555555555555'] + $msGood, false);
attempt('Microsoft: wrong issuer refused', $ms, ['iss' => 'https://evil.example/v2.0'] + $msGood, false);
attempt('Microsoft: missing user id refused', $ms, array_diff_key($msGood, ['oid' => 1]), false);

$g = $services['google']['identity'];
$gGood = ['iss' => 'https://accounts.google.com', 'sub' => '1098765', 'email' => 'Joe.Crew@gmail.com', 'email_verified' => true, 'name' => 'Joe Crew'];
attempt('Google: verified account accepted', $g, $gGood, true);
attempt('Google: short-form issuer accepted', $g, ['iss' => 'accounts.google.com'] + $gGood, true);
attempt('Google: unverified email refused', $g, ['email_verified' => false] + $gGood, false);
attempt('Google: email_verified as text "true" refused', $g, ['email_verified' => 'true'] + $gGood, false);
attempt('Google: wrong issuer refused', $g, ['iss' => 'https://evil.example'] + $gGood, false);
attempt('Google: missing user id refused', $g, array_diff_key($gGood, ['sub' => 1]), false);

// Sign-in time limit and one-time state, using a backdated sign-in in progress.
function finish_with(array $pending, array $get): string {
    $_SESSION = ['pending_sign_ins' => ['state-1' => $pending + ['method' => 'microsoft', 'nonce' => 'n', 'verifier' => 'v', 'next' => '/dashboard/']]];
    $_GET = $get;
    try {
        finish_sign_in();
        return 'finished';
    } catch (RuntimeException $e) {
        return $e->getCode() === SIGN_IN_EXPIRED ? 'expired' : 'refused: ' . $e->getMessage();
    }
}
expect('sign-in finished after 11 minutes is reported as expired', finish_with(['started' => time() - 660], ['state' => 'state-1', 'code' => 'x']) === 'expired');
expect('unknown state is refused (not reported as expired)', str_starts_with(finish_with(['started' => time()], ['state' => 'other', 'code' => 'x']), 'refused: No matching sign-in'));
expect('a used state is gone from the session', !isset($_SESSION['pending_sign_ins']['other']));

expect('always-Admin email via Microsoft is an Admin', is_permanent_admin('microsoft', 'MCROSS@rjtide.com'));
expect('always-Admin email via Google is not an Admin', !is_permanent_admin('google', 'mcross@rjtide.com'));
expect('Google sign-ins need approval, Microsoft ones don\'t', needs_approval('google') && !needs_approval('microsoft'));
