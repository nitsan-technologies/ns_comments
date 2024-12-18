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

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Comments
 */
class CommentRepository extends Repository
{
    /**
     * @param $pageId
     * @param $mode
     * @return array|object[]|QueryResultInterface
     */
    public function getCommentsByPage($pageId): QueryResultInterface|array
    {
        $query = $this->createQuery();
      
            $query->matching(
                $query->logicalAnd(
                    $query->equals('pageuid', $pageId),
                    $query->equals('comment', 0)
                )
            );
        
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        $query->getQuerySettings()->setRespectStoragePage(false);
        return $query->execute();
    }


    /**
     * @param $pageuid
     * @return array|object[]|QueryResultInterface
     */
    public function getLastCommentOfPage($pageuid = null): QueryResultInterface|array
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->matching(
            $query->logicalAnd(
                $query->equals('pageuid', $pageuid),
            )
        );
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->setLimit(1)->execute();
    }
}