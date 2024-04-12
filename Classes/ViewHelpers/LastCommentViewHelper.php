<?php
namespace Nitsan\NsComments\ViewHelpers;

use Nitsan\NsComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *  Get last comment of page record
 */
class LastCommentViewHelper extends AbstractViewHelper
{
    /**
     * commentRepository
     *
     * @var CommentRepository
     */
    protected $commentRepository = null;

    /**
     * Inject a news repository to enable DI
     *
     * @param CommentRepository $commentRepository
     */
    public function injectCommentRepository(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('pageuid', 'int', 'pageuid', true);
    }

    /**
     * Last Comment
     *
     */
    public function render()
    {
        $pageUid = $this->arguments['pageuid'];

        // Get last comment of page
        return $this->commentRepository->getLastCommentOfPage($pageUid);
    }
}
