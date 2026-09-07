<?php

//CAPTCHA Matching code
session_name('ns_comments_captcha');
session_start();

if (($_SESSION['ns_comments_captcha_code'] ?? '') == ($_POST['captcha'] ?? '')) {
    echo 'true';
} else {
    echo 'false';
}
exit;
