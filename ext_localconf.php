<?phphttps://gitlab.nitsantech.com/t3planet/extensions/ns_news_comments/-/tree/features/stability-v11?ref_type=heads

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
    ]
);
