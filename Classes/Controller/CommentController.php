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
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * CommentController
 */
class CommentController extends ActionController
{
    public function __construct(
        protected CommentRepository  $commentRepository,
        protected PersistenceManager $persistenceManager,
    ) {}

    /**
     * action initialize
     *
     * @return void
     */
    public function initializeAction(): void
    {
        // Storage page: use TypoScript/plugin persistence, else current page (same as previous
        // form Storagepid = page id). Never take storage PID from the request.
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        if (empty($extbaseFrameworkConfiguration['persistence']['storagePid'])) {
            $storagePid = $this->getCurrentPageId();
            if ($storagePid > 0) {
                $extbaseFrameworkConfiguration['persistence']['storagePid'] = (string)$storagePid;
                $this->configurationManager->setConfiguration($extbaseFrameworkConfiguration);
            }
        }
    }


    /**
     * action list
     *
     * @return ResponseInterface
     */
    public function listAction(): ResponseInterface
    {
        $setting = $this->settings;
        $setting['relatedComments'] ??= '';
        $relatedComments = $setting['relatedComments'];

        if ($relatedComments) {
            $setting['dateFormat'] = ($setting['dateFormat'] ?? '')
                ?: ($setting['mainConfiguration']['customDateFormat'] ?? 'F j, Y');
            $setting['timeFormat'] = ($setting['timeFormat'] ?? '')
                ?: ($setting['mainConfiguration']['customTimeFormat'] ?? 'g:i a');
            $setting['captcha'] = $setting['mainConfiguration']['disableCaptcha'] ?? '';

            // Check if 'userImage' exists before assigning it to $image
            $image = $setting['mainConfiguration']['userImage'] ?? null;

            $this->view->assign('relatedComments', true);
        }

        if ((string)($setting['global'] ?? '') === '1') {
            $setting = array_merge($setting, $this->getGlobalDateTimeFormats());
        }

        $pid = $this->getCurrentPageId();

        if ($pid) {
            $comments = $this->commentRepository->getCommentsByPage($pid)->toArray();
            $paths = $this->captchaVerificationPath();

            $captcha_path = $paths['captcha'] . '?' . random_int(0, mt_getrandmax());
            $image ??= '';
            $this->view->assignMultiple([
                'captcha_path' => $captcha_path,
                'verification' => $paths['verification'],
                'comments' => $comments,
                'Image' => $image,
                'pid' => $pid,
                'settings' => $setting,
            ]);
        } else {
            $error = LocalizationUtility::translate('tx_nscomments_domain_model_comment.errorMessage', 'NsComments');
            $this->addFlashMessage($error, '', ContextualFeedbackSeverity::ERROR);
        }
        return $this->htmlResponse();
    }

    /**
     * action create
     *
     * @param Comment|null $newComment
     *
     * @return ResponseInterface
     */
    public function createAction(?Comment $newComment = null): ResponseInterface
    {

        // If no form data was submitted (e.g. direct GET access by bots/crawlers), redirect
        if ($newComment === null) {
            return $this->redirect('list');
        }

        if ($this->isCaptchaEnabled() && !$this->isSubmittedCaptchaValid()) {
            return $this->jsonResponse(json_encode(['status' => 'error', 'message' => 'captcha']));
        }

        $pageUid = $this->getCurrentPageId();

        $request = $this->request->getArguments();
        // Ignore request-controlled internals (mass-assignment hardening). Trusted form fields
        // still map username/usermail/description/terms; everything sensitive is reset here.
        $newComment->setCrdate(time());
        $newComment->setPageuid($pageUid);
        $newComment->setHidden(0);
        $newComment->setAccesstoken('');
        $newComment->setFeuserid(0);
        $newComment->setUserimage('');
        $newComment->setParamlink('');
        $newComment->setCaptcha('');
        $languageId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
        $newComment->set_languageUid($languageId);

        $parentId = (int)($request['parentId'] ?? 0);
        if ($parentId > 0) {
            $parentComment = $this->commentRepository->findByUid($parentId);
            if ($parentComment instanceof Comment && $parentComment->getPageuid() === $pageUid) {
                $parentComment->addChildcomment($newComment);
                $this->commentRepository->update($parentComment);
            } else {
                $parentId = 0;
            }
        }

        // Add comment to repository
        $this->commentRepository->add($newComment);
        $this->persistenceManager->persistAll();

        // Add paramlink to comments for scrolling to comment
        $paramlink = $this->buildUriByUid($pageUid, ['commentid' => $newComment->getUid()]);
        $newComment->setParamlink($paramlink);
        $this->commentRepository->update($newComment);

        $this->persistenceManager->persistAll();
        $json[$newComment->getUid()] = ['parentId' => $parentId, 'comment' => 'comment'];
        return $this->jsonResponse(json_encode($json));

    }

    /**
     * Captcha is shown when settings.captcha == 0 (FlexForm), or for relatedComments when
     * mainConfiguration.disableCaptcha is not enabled.
     *
     * typeNum=991 create often has no FlexForm settings, so also treat a posted captcha_code
     * field (present only when the form rendered the captcha) as captcha-enabled.
     */
    private function isCaptchaEnabled(): bool
    {
        if (!empty($this->settings['relatedComments'])) {
            return (int)($this->settings['mainConfiguration']['disableCaptcha'] ?? 0) === 0;
        }
        if (array_key_exists('captcha', $this->settings)) {
            return (int)$this->settings['captcha'] === 0;
        }
        return array_key_exists('captcha_code', $this->request->getArguments());
    }

    /**
     * Validate against the captcha session written by Resources/Public/PHP/captcha.php.
     */
    private function isSubmittedCaptchaValid(): bool
    {
        $submitted = trim((string)($this->request->getArguments()['captcha_code'] ?? ''));
        if ($submitted === '') {
            return false;
        }

        $previousName = session_name();
        $hadSession = session_status() === PHP_SESSION_ACTIVE;
        if ($hadSession) {
            session_write_close();
        }

        session_name('ns_comments_captcha');
        session_start();
        $expected = (string)($_SESSION['ns_comments_captcha_code'] ?? '');
        $valid = $expected !== '' && hash_equals($expected, $submitted);
        if ($valid) {
            unset($_SESSION['ns_comments_captcha_code']);
        }
        session_write_close();

        if ($hadSession) {
            session_name($previousName);
            session_start();
        }

        return $valid;
    }

    private function getCurrentPageId(): int
    {
        $versionNumber = VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());
        if ($versionNumber['version_main'] <= 12) {
            // @extensionScannerIgnoreLine
            return (int)($GLOBALS['TSFE']->id ?? 0);
        }
        $pageInformation = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.page.information');
        return $pageInformation ? (int)$pageInformation->getId() : 0;
    }

    /**
     * Site settings are used when the site defines them (Site Set or settings.yaml), otherwise
     * TypoScript, e.g. on TYPO3 v12. TypoScript is read directly because FlexForm values
     * override the plugin settings.
     *
     * @return array{dateFormat: string, timeFormat: string}
     */
    private function getGlobalDateTimeFormats(): array
    {
        $site = $this->request->getAttribute('site');
        $siteSettings = $site instanceof Site ? $site->getSettings() : null;
        $typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $typoScriptSettings = $typoScript['plugin.']['tx_nscomments.']['settings.'] ?? [];
        $mainConfiguration = $typoScriptSettings['mainConfiguration.'] ?? [];

        $formats = [];
        foreach (['dateFormat' => 'F j, Y', 'timeFormat' => 'g:i a'] as $format => $default) {
            $customKey = 'custom' . ucfirst($format);
            $siteSetting = 'ns_comments.configuration.main.' . $customKey;
            $value = $siteSettings?->has($siteSetting)
                ? $siteSettings->get($siteSetting)
                : ($typoScriptSettings[$format] ?? $mainConfiguration[$customKey] ?? '');
            $formats[$format] = trim(is_scalar($value) ? (string)$value : '') ?: $default;
        }

        return $formats;
    }

    /**
     * Returns a built URI by pageUid
     *
     * @param int $uid The uid to use for building link
     * @param array $arguments
     * @return string The link
     */
    private function buildUriByUid(int $uid, array $arguments = []): string
    {
        $commentId = $arguments['commentid'];
        $excludeFromQueryString = [
            'tx_nscomments_comment[action]',
            'tx_nscomments_comment[controller]',
            'tx_nscomments_comment',
            'type',
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
     * getPath for composer based setup
     * @param mixed $path
     * @param mixed $extName
     * @return string
     */
    public function getPath(mixed $path, mixed $extName): string
    {
        $publicPath = sprintf('EXT:%s/Resources/Public/%s', $extName, ltrim($path, '/'));

        $versionNumber =  VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());
        
        if ($versionNumber['version_main'] >= 14) {
            $systemResourceFactory = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\SystemResource\SystemResourceFactory::class
            );
            $resource = $systemResourceFactory->createPublicResource($publicPath);
            $resourcePublisher = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface::class
            );

            return (string)$resourcePublisher->generateUri(
                $resource,
                $this->request
            );
        } else {
            // @extensionScannerIgnoreLine
            $uri = PathUtility::getPublicResourceWebPath($publicPath);
            return substr($uri, 1);
        }
    }

    /**
     * @return array
     */
    private function captchaVerificationPath(): array
    {
        $paths = [];
        if (Environment::isComposerMode()) {
            $assetPath = $this->getPath('PHP/', 'ns_comments');
            $basePath = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $paths['captcha'] = $basePath . $assetPath . 'captcha.php';
            $paths['verification'] = $basePath . $assetPath . 'verify.php';
        } else {
            $basePath = PathUtility::stripPathSitePrefix(ExtensionManagementUtility::extPath('ns_comments'));
            $paths['captcha'] = '/' . $basePath . 'Resources/Public/PHP/captcha.php';
            $paths['verification'] = '/' . $basePath . 'Resources/Public/PHP/verify.php';
        }
        return $paths;
    }
}
