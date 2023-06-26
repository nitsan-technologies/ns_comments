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
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * CommentController
 */
class CommentController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * commentRepository
     *
     */
    protected $commentRepository = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;

    public function __construct(
        CommentRepository  $commentRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->commentRepository = $commentRepository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction(): void
    {
        // Storage page configuration
        $pageUid = $GLOBALS['TSFE']->id;
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] = $_REQUEST['tx_nscomments_comment']['comments-storage-pid'] ?? '';
        if ($_REQUEST['tx_nscomments_comment']['comments-storage-pid']) {
            $currentPid['persistence']['storagePid'] = $this->request->getParsedBody()['id'];
            $this->configurationManager->setConfiguration(array_merge($extbaseFrameworkConfiguration, $currentPid));
        } else {
            if (empty($extbaseFrameworkConfiguration['persistence']['storagePid'])) {
                if ($_REQUEST['tx_nscomments_comment']) {
                    $_REQUEST['tx_nscomments_comment']['Storagepid'] = $_REQUEST['tx_nscomments_comment']['Storagepid'] ?? '';
                    $currentPid['persistence']['storagePid'] = $_REQUEST['tx_nscomments_comment']['Storagepid'];
                } else {
                    $currentPid['persistence']['storagePid'] = $this->request->getParsedBody()['id'];
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
        $setting = $this->settings;
        $setting['relatedComments'] = $setting['relatedComments'] ?? '';
        $relatedComments = $setting['relatedComments'];
        if ($relatedComments) {
            $setting['custom'] = false;
            $setting['dateFormat'] = $setting['mainConfiguration']['customDateFormat'];
            $setting['timeFormat'] = $setting['mainConfiguration']['customTimeFormat'];
            $setting['captcha'] = $setting['mainConfiguration']['disableCaptcha'];
            $Image = $setting['mainConfiguration']['userImage'];
            $this->view->assign('relatedComments', true);
        }

        $pid = $GLOBALS['TSFE']->id;
        if ($pid) {
            $comments = $this->commentRepository->getCommentsByPage($pid, $setting['commnetlanguageFallbackMode'])->toArray();
            if (Environment::isComposerMode()) {
                $assetPath = $this->getPath('PHP/', 'ns_comments');
                $path = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $assetPath . 'captcha.php';
                $verification = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $assetPath . 'verify.php';
            } else {
                $path = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Public/PHP/captcha.php';
                $verification = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments')) . 'Resources/Public/PHP/verify.php';
            }

            $captcha_path = $path . '?' . rand();
            $Image = $Image ?? '';
            $this->view->assignMultiple([
                'captcha_path' => $captcha_path,
                'verification' => $verification,
                'comments' => $comments,
                'Image' => $Image,
                'pid' => $pid,
                'settings' => $setting
            ]);
        } else {
            $error = LocalizationUtility::translate('tx_nscomments_domain_model_comment.errorMessage', 'NsComments');
            $this->addFlashMessage($error, '', \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR);
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
        $languageid = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
        $newComment->set_languageUid($languageid);
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
    private function buildUriByUid($uid, $arguments = []): string
    {
        $commentid = $arguments['commentid'];
        $excludeFromQueryString = ['tx_nscomments_comment[action]', 'tx_nscomments_comment[controller]', 'tx_nscomments_comment', 'type'];
        $uri = $this->uriBuilder->reset()->setTargetPageUid($uid)->setAddQueryString(true)->setArgumentsToBeExcludedFromQueryString($excludeFromQueryString)->setSection('comments-' . $commentid)->build();
        $uri = $this->addBaseUriIfNecessary($uri);
        return $uri;
    }

    /**
     * getPath for composer based setup
     * @param mixed $path
     * @param mixed $extName
     * @return string
     */
    public function getPath(mixed $path, mixed $extName): string
    {
        $arguments = ['path' => $path, 'extensionName' => $extName];
        $path = $arguments['path'];
        $publicPath = sprintf('EXT:%s/Resources/Public/%s', $arguments['extensionName'], ltrim($path, '/'));
        $uri = PathUtility::getPublicResourceWebPath($publicPath);
        return substr($uri, 1);
    }

}
