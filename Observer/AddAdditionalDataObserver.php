<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
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

        $this->renderTimer->start($block);
    }
}
