<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Nitsan\NsComments\Controller\CommentController;

if (!defined('TYPO3')) {
    die('Access denied.');
}

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

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1792056230] = [
    'nodeName' => 'flexFormNoTabsContainer',
    'priority' => 47,
    'class' => \Nitsan\NsComments\Form\Resolver\PremiumStripFlexFormNoTabsContainerResolver::class,
];
