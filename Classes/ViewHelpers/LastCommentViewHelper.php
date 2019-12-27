<?php
namespace Nitsan\NsComments\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *  Get last comment of page record
 */
class LastCommentViewHelper extends AbstractViewHelper
{
    /**
     * commentRepository
     *
     * @var \Nitsan\NsComments\Domain\Repository\CommentRepository
     */
    protected $commentRepository = null;

    /**
     * Inject a news repository to enable DI
     *
     * @param \Nitsan\NsComments\Domain\Repository\CommentRepository $commentRepository
     */
    public function injectCommentRepository(\Nitsan\NsComments\Domain\Repository\CommentRepository $commentRepository)
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
        $pageuid = $this->arguments['pageuid'];

        // Get last comment of page
        $pagecommentData = $this->commentRepository->getLastCommentOfPage($pageuid);
        return $pagecommentData;
    }
}
