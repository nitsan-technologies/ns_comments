<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

defined('TYPO3') or die();

$_EXTKEY = 'ns_comments';

/***************
 * Plugin
 */
$versionNumber =  VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());

if ($versionNumber['version_main'] <= '12') {
    $pluginSignature = ExtensionUtility::registerPlugin(
        $_EXTKEY,
        'Comment',
        'Comment',
        '',
        'plugins'
    );

    /* Flexform setting  */
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'recursive,select_key,pages';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    // @extensionScannerIgnoreLine
    ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForm/FlexForm.xml');
} else {
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
}
