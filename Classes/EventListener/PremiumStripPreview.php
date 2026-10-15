<?php

declare(strict_types=1);

namespace Nitsan\NsComments\EventListener;

use Nitsan\NsComments\Backend\PremiumStrip;
use TYPO3\CMS\Backend\View\Event\PageContentPreviewRenderingEvent;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Adds the premium strip to the Page module preview of the Comments content element.
 */
final class PremiumStripPreview
{
    public function __construct(
        private readonly PremiumStrip $premiumStrip,
        private readonly PageRenderer $pageRenderer,
    ) {}

    public function __invoke(PageContentPreviewRenderingEvent $event): void
    {
        if ($event->getTable() !== 'tt_content' || $this->getRecordType($event) !== PremiumStrip::PLUGIN_SIGNATURE) {
            return;
        }

        $strip = $this->premiumStrip->render();
        if ($strip === '') {
            return;
        }

        $this->pageRenderer->addCssFile(PremiumStrip::STYLESHEET);
        $event->setPreviewContent($strip . ($event->getPreviewContent() ?? ''));
    }

    /**
     * TYPO3 v13+ exposes the record type on the event, v12 only passes the record row.
     */
    private function getRecordType(PageContentPreviewRenderingEvent $event): string
    {
        if (method_exists($event, 'getRecordType')) {
            return (string)$event->getRecordType();
        }

        $record = $event->getRecord();

        return is_array($record) ? (string)($record['CType'] ?? '') : '';
    }
}
