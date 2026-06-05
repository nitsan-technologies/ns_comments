<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$_EXTKEY = 'ns_comments';

/***************
 * Plugin
 */
$ctypeKey = ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Comment',
    'LLL:EXT:ns_comments/Resources/Private/Language/locallang_db.xlf:comment',
    'ext-ns-comment-icon',
    'plugins',
    'LLL:EXT:ns_comments/Resources/Private/Language/locallang_db.xlf:comment.description',
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/FlexForm.xml',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform,',
    $ctypeKey,
    'after:subheader',
);

// @extensionScannerIgnoreLine
ExtensionManagementUtility::addPiFlexFormValue(
    '*',
    'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/FlexForm.xml',
    $ctypeKey,
);
