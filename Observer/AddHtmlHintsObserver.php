<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Yireo\HtmlHints\Util\CommentSanitizer;
use Yireo\HtmlHints\Util\HintGuard;
use Yireo\HtmlHints\Util\RenderTimer;

class AddHtmlHintsObserver implements ObserverInterface
{
    public function __construct(
        private readonly HintGuard $hintGuard,
        private readonly RenderTimer $renderTimer,
        private readonly CommentSanitizer $commentSanitizer,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        if (false === $block instanceof AbstractBlock || false === is_object($transport)) {
            return;
        }

        if (false === $this->hintGuard->isEnabled()) {
            return;
        }

        $html = $transport->getHtml();
        if (false === $this->hintGuard->allows($block->getLayout(), $html)) {
            return;
        }

        $time = 'unknown';
        $microseconds = 0;
        $elapsed = $this->renderTimer->getElapsedMicroseconds($block);
        if ($elapsed !== null) {
            $microseconds = $elapsed;
            $time = round($microseconds / 1000, 2) . 'ms';
        }

        $blockName = $this->commentSanitizer->sanitize((string)$block->getNameInLayout());
        $templateName = '';
        $templateFile = '';
        if ($block instanceof Template) {
            $templateName = (string)$block->getTemplate();
            $templateFile = (string)$block->getTemplateFile();
        }

        $comments = [];
        $comments[] = 'BLOCK CLASS: ' . $this->commentSanitizer->sanitize(get_class($block));
        $comments[] = 'BLOCK NAME: ' . $blockName;
        $comments[] = 'TEMPLATE NAME: ' . $this->commentSanitizer->sanitize($templateName);
        $comments[] = 'TEMPLATE FILE: ' . $this->commentSanitizer->sanitize($templateFile);
        $comments[] = 'MICROSECONDS: ' . round($microseconds);
        $comments[] = 'TIME: ' . $time;

        $transport->setHtml(
            '<!-- ' . implode(' / ', $comments) . ' -->'
            . $html
            . '<!-- END BLOCK ' . $blockName . ' -->'
        );
    }
}
