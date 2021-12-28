<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (version_compare(TYPO3_branch, '10.0', '>=')) {
    $moduleClass = \Nitsan\NsComments\Controller\CommentController::class;
} else {
    $moduleClass = 'Comment';
}
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Nitsan.ns_comments',
    'Comment',
    [
        $moduleClass => 'list, new, create, approveComment',
    ],
    // non-cacheable actions
    [
        $moduleClass => 'list, new, create, approveComment',
    ]
);

if (version_compare(TYPO3_branch, '7.0', '>')) {
    if (TYPO3_MODE === 'BE') {
        $icons = [
            'ext-ns-comment-icon' => 'plug_comment.svg',
        ];
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        foreach ($icons as $identifier => $path) {
            $iconRegistry->registerIcon(
                $identifier,
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                ['source' => 'EXT:ns_comments/Resources/Public/Icons/' . $path]
            );
        }
    }
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['ns_comments']
= \Nitsan\NsComments\Hooks\PageLayoutView::class;
