<?php

namespace Nitsan\NsComments\ViewHelpers;

use Nitsan\NsComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 *  Get last comment of page record
 */
class LastCommentViewHelper extends AbstractViewHelper
{

    public function __construct(
        protected  CommentRepository      $commentRepository,
    ) {
    }


    /**
     * Initialize
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('pageuid', 'int', 'pageuid', true);
    }

    /**
     * Last Comment
     *
     */
    public function render(): array|QueryResultInterface
    {
        $pageUid = $this->arguments['pageuid'];

        // Get last comment of page
        return $this->commentRepository->getLastCommentOfPage($pageUid);
    }
}
