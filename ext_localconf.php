<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ns_comments',
    'Comment',
    [
        \Nitsan\NsComments\Controller\CommentController::class => 'list, new, create, approveComment',
    ],
    // non-cacheable actions
    [
        \Nitsan\NsComments\Controller\CommentController::class => 'list, new, create, approveComment',
    ]
);

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
    'ext-ns-comment-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:ns_comments/Resources/Public/Icons/plug_comment.svg']
);
