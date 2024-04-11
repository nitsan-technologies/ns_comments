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
use Nitsan\NsComments\Domain\Repository\CommentRepository;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * CommentController
 */
class CommentController extends ActionController
{
    protected $pageUid;

    /**
     * commentRepository
     *
     * @var CommentRepository
     */
    protected $commentRepository = null;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * User Repository
     *
     * @var FrontendUserRepository
     */
    protected $userRepository;

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
     * Inject a news repository to enable DI
     *
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
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
        $this->pageUid = $GLOBALS['TSFE']->id;
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] = isset($_REQUEST['tx_nscomments_comment']['comments-storage-pid']) ? $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] : '';
        if ($_REQUEST['tx_nscomments_comment']['comments-storage-pid']) {
            if ($this->settings['mainConfiguration']['recordStoragePage']) {
                $currentPid['persistence']['storagePid'] = $_REQUEST['tx_nscomments_comment']['comments-storage-pid'];
            } else {
                $currentPid['persistence']['storagePid'] = GeneralUtility::_GP('id');
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
                        $currentPid['persistence']['storagePid'] = GeneralUtility::_GP('id');
                    }
                }
                $this->configurationManager->setConfiguration(array_merge($extbaseFrameworkConfiguration, $currentPid));
            }
        }
    }

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $this->settings['relatedComments'] = isset($this->settings['relatedComments']) ? $this->settings['relatedComments'] : '';
        $relatedComments = $this->settings['relatedComments'];
        if ($relatedComments) {
            $this->settings['custom'] = false;
            $this->settings['dateFormat'] = $this->settings['mainConfiguration']['customDateFormat'];
            $this->settings['timeFormat'] = $this->settings['mainConfiguration']['customTimeFormat'];
            $this->settings['captcha'] = $this->settings['mainConfiguration']['disableCaptcha'];
            $this->view->assign('relatedComments', true);
        }

        $pid = $GLOBALS['TSFE']->id;
        $setting = $this->settings;
        if ($pid) {
            $comments = $this->commentRepository->getCommentsByPage($pid,$setting['commnetlanguageFallbackMode'])->toArray();
            $paths = $this->captchaVerificationPath();

            $captcha_path = $paths['captcha'] . '?' . rand();
            $image = isset($image) ? $image : '';
            $this->view->assign('captcha_path', $captcha_path);
            $this->view->assign('verification', $paths['verification']);
            $this->view->assign('comments', $comments);
            $this->view->assign('Image', $image);
            $this->view->assign('pid', $pid);
            $this->view->assign('settings', $setting);
        } else {
            $error = LocalizationUtility::translate('tx_nscomments_domain_model_comment.errorMessage', 'NsComments');
            $this->addFlashMessage($error, '', AbstractMessage::ERROR);
        }
    }

    /**
     * action create
     *
     * @param Comment $newComment
     *
     * @return void
     */
    public function createAction(Comment $newComment)
    {
        $request = $this->request->getArguments();
        $newComment->setCrdate(time());
        if (version_compare(TYPO3_branch, '9.0', '>')) {
            $languageid = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language');
            $newComment->set_languageUid($languageid->getId());
        } else {
            $newComment->set_languageUid($GLOBALS['TSFE']->sys_language_uid);
        }
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
        $paramlink = $this->buildUriByUid($this->pageUid, ['commentid' => $newComment->getUid()]);
        $newComment->setParamlink($paramlink);
        $this->commentRepository->update($newComment);

        $this->persistenceManager->persistAll();
        $json[$newComment->getUid()] = ['parentId' => $parentId, 'comment' => 'comment'];
        return json_encode($json);

    }

    /**
     * Returns a built URI by pageUid
     *
     * @param int $uid The uid to use for building link
     * @param bool $arguments
     * @return string The link
     */
    private function buildUriByUid($uid, $arguments = []): string
    {
        $commentId = $arguments['commentid'];
        $excludeFromQueryString = [
            'tx_nscomments_comment[action]',
            'tx_nscomments_comment[controller]',
            'tx_nscomments_comment',
            'type'
        ];
        $uri = $this->uriBuilder->reset()
            ->setTargetPageUid($uid)
            ->setAddQueryString(true)
            ->setArgumentsToBeExcludedFromQueryString($excludeFromQueryString)
            ->setSection('comments-' . $commentId)
            ->build();
        return $this->addBaseUriIfNecessary($uri);
    }

    /**
     * @return array
     */
    private function captchaVerificationPath(): array
    {
        $paths = [];
        if (version_compare(TYPO3_branch, '9.0', '>')) {
            $paths['captcha'] = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Private/PHP/captcha.php';
            $paths['verification'] = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Private/PHP/verify.php';
        } else {
            $paths['captcha'] = ExtensionManagementUtility::siteRelPath('ns_comments') . 'Resources/Private/PHP/captcha.php';
            $paths['verification'] = ExtensionManagementUtility::siteRelPath('ns_comments') . 'Resources/Private/PHP/verify.php';
        }
        return $paths;
    }

}
