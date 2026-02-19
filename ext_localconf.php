<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Nitsan\NsComments\Controller\CommentController;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$versionNumber =  VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());

if ($versionNumber['version_main'] <= 12) {

    // @extensionScannerIgnoreLine
    ExtensionUtility::configurePlugin(
        'ns_comments',
        'Comment',
        [
            CommentController::class => 'list, new, create',
        ],
        // non-cacheable actions
        [
            CommentController::class => 'list, new, create',
        ]
    );
} else {
    ExtensionUtility::configurePlugin(
        'ns_comments',
        'Comment',
        [
            CommentController::class => 'list, new, create',
        ],
        // non-cacheable actions
        [
            CommentController::class => 'list, new, create',
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
    );
}
