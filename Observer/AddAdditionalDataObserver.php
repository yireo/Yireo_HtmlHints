<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Yireo\HtmlHints\Util\HintGuard;
use Yireo\HtmlHints\Util\RenderTimer;

class AddAdditionalDataObserver implements ObserverInterface
{
    public function __construct(
        private readonly HintGuard $hintGuard,
        private readonly RenderTimer $renderTimer,
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (false === $this->hintGuard->isEnabled()) {
            return;
        }

        $block = $observer->getEvent()->getBlock();
        if (false === is_object($block)) {
            return;
        }

        if ($block instanceof AbstractBlock) {
            $block->setData('cache_lifetime', false);
        }

        $this->renderTimer->start($block);
    }
}
