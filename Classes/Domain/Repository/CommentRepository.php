<?php

namespace Nitsan\NsComments\Domain\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2023
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The repository for Comments
 */
class CommentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @param $pageId
     * @param $mode
     * @return array|object[]|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getCommentsByPage($pageId, $mode): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
    {
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $languageid = $context->getPropertyFromAspect('language', 'id');
        $query = $this->createQuery();
        if ($mode > 0) {
            $query->getQuerySettings()->setRespectSysLanguage(false);
            $query->matching(
                $query->logicalAnd(
                    $query->equals('pageuid', $pageId),
                    $query->equals('comment', 0),
                    $query->equals('sys_language_uid', $languageid)
                )
            );
        } else {
            $query->matching(
                $query->logicalAnd(
                    $query->equals('pageuid', $pageId),
                    $query->equals('comment', 0)
                )
            );
        }
        $query->setOrderings(['crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $result = $query->execute();
        return $result;
    }


    /**
     * @param $pageuid
     * @return array|object[]|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getLastCommentOfPage($pageuid = null): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('pageuid', $pageuid),
            )
        );
        $query->setOrderings(['crdate' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $result = $query->setLimit(1)->execute();
        return $result;
    }
}
