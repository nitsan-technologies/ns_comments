<?php

// CAPTCHA matching (client-side pre-check; createAction also validates server-side)
session_name('ns_comments_captcha');
session_start();

$expected = (string)($_SESSION['ns_comments_captcha_code'] ?? '');
$submitted = (string)($_POST['captcha'] ?? '');

if ($expected !== '' && $submitted !== '' && hash_equals($expected, $submitted)) {
    echo 'true';
} else {
    echo 'false';
}
exit;
