<?php
namespace Nitsan\NsComments\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2018
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
use Nitsan\NsComments\Domain\Model\Comment;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * CommentController
 */
class CommentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * commentRepository
     *
     * @var \Nitsan\NsComments\Domain\Repository\CommentRepository
     */
    protected $commentRepository = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


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
     * Inject a news repository to enable DI
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction()
    {
        // Storage page configuration
        $pageUid = $GLOBALS['TSFE']->id;
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] = isset($_REQUEST['tx_nscomments_comment']['comments-storage-pid']) ? $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] : '';
        if ($_REQUEST['tx_nscomments_comment']['comments-storage-pid']) {
            if ($this->settings['mainConfiguration']['recordStoragePage']) {
                $currentPid['persistence']['storagePid'] = $_REQUEST['tx_nscomments_comment']['comments-storage-pid'];
            } else {
                $currentPid['persistence']['storagePid'] = $this->request->getParsedBody()['id'];
            }
            $this->configurationManager->setConfiguration(array_merge($extbaseFrameworkConfiguration, $currentPid));
        } else {
            if (empty($extbaseFrameworkConfiguration['persistence']['storagePid'])) {
                if ($_REQUEST['tx_nscomments_comment']) {
                    $_REQUEST['tx_nscomments_comment']['Storagepid'] = isset($_REQUEST['tx_nscomments_comment']['Storagepid']) ? $_REQUEST['tx_nscomments_comment']['Storagepid'] : '';
                    $currentPid['persistence']['storagePid'] = $_REQUEST['tx_nscomments_comment']['Storagepid'];
                } else {
                    if ($this->settings['relatedComments'] && $this->settings['mainConfiguration']['recordStoragePage']) {
                        $currentPid['persistence']['storagePid'] = $this->settings['mainConfiguration']['recordStoragePage'];
                    } else {
                        $currentPid['persistence']['storagePid'] = $this->request->getParsedBody()['id'];
                    }
                }
                $this->configurationManager->setConfiguration(array_merge($extbaseFrameworkConfiguration, $currentPid));
            }
        }
    }

    /**
     * action list
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        $this->settings['relatedComments'] = isset($this->settings['relatedComments']) ? $this->settings['relatedComments'] : '';
        $relatedComments = $this->settings['relatedComments'];
        if ($relatedComments) {
            $this->settings['custom'] = false;
            $this->settings['dateFormat'] = $this->settings['mainConfiguration']['customDateFormat'];
            $this->settings['timeFormat'] = $this->settings['mainConfiguration']['customTimeFormat'];
            $this->settings['captcha'] = $this->settings['mainConfiguration']['disableCaptcha'];
            if ($this->settings['mainConfiguration']['commentUserSettings'] == 'feuserOnly') {
                $this->settings['userSettings'] = $this->settings['mainConfiguration']['commentUserSettings'];
                $this->settings['feUserloginpid'] = $this->settings['mainConfiguration']['FEUserLoginPageId'];
            } else {
                $this->settings['userSettings'] = $this->settings['mainConfiguration']['commentUserSettings'];
            }
            $Image = $this->settings['mainConfiguration']['userImage'];
            $this->view->assign('relatedComments', true);
        }

        $pid = $GLOBALS['TSFE']->id;
        $setting = $this->settings;
        if ($pid) {
            $comments = $this->commentRepository->getCommentsByPage($pid, $setting['commnetlanguageFallbackMode'])->toArray();

            $path = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Private/PHP/captcha.php';
            $verification = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Private/PHP/verify.php';

            $captcha_path = $path . '?' . rand();
            $Image = isset($Image) ? $Image : '';
            $this->view->assign('captcha_path', $captcha_path);
            $this->view->assign('verification', $verification);
            $this->view->assign('comments', $comments);
            $this->view->assign('Image', $Image);
            $this->view->assign('pid', $pid);
            $this->view->assign('settings', $setting);
        } else {
            $error = LocalizationUtility::translate('tx_nscomments_domain_model_comment.errorMessage', 'NsComments');
            $this->addFlashMessage($error, '', \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR);
        }
        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @param \Nitsan\NsComments\Domain\Model\Comment $newComment
     *
     * @return ResponseInterface
     */
    public function createAction(Comment $newComment): ResponseInterface
    {
        $pageUid = $GLOBALS['TSFE']->id;

        $request = $this->request->getArguments();
        $newComment->setCrdate(time());
        $languageid = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        $newComment->set_languageUid($languageid->getId());
        $parentId = $request['parentId'];
        if ($request['parentId'] > 0) {
            $childComment = $this->commentRepository->findByUid($parentId);
            $childComment->addChildcomment($newComment);
            $this->commentRepository->update($childComment);
        }

        // Add comment to repository
        $this->commentRepository->add($newComment);
        $this->persistenceManager->persistAll();

        // Add paramlink to comments for scrolling to comment
        $paramlink = $this->buildUriByUid($pageUid, $arguments = ['commentid' => $newComment->getUid()]);
        $newComment->setParamlink($paramlink);
        $this->commentRepository->update($newComment);

        $this->persistenceManager->persistAll();
        $json[$newComment->getUid()] = ['parentId' => $parentId, 'comment' => 'comment'];
        return $this->jsonResponse(json_encode($json));

    }

    /**
     * Returns a built URI by pageUid
     *
     * @param int $uid The uid to use for building link
     * @param array $arguments
     * @return string The link
     */
    private function buildUriByUid($uid, $arguments = [])
    {
        $commentid = $arguments['commentid'];
        $excludeFromQueryString = ['tx_nscomments_comment[action]', 'tx_nscomments_comment[controller]', 'tx_nscomments_comment', 'type'];
        $uri = $this->uriBuilder->reset()->setTargetPageUid($uid)->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString($excludeFromQueryString)->setSection('comments-' . $commentid)->build();
        $uri = $this->addBaseUriIfNecessary($uri);
        return $uri;
    }

}