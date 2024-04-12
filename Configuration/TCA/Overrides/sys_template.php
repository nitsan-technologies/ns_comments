<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

$extKey = 'ns_comments';

// Adding fields to the tt_content table definition in TCA
ExtensionManagementUtility::addStaticFile(
    $extKey,
    'Configuration/TypoScript',
    '[NITSAN] Comment'
);
