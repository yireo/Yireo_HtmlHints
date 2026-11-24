<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Observer;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\HtmlHints\Util\HintGuard;

class AddHttpContextObserver implements ObserverInterface
{
    public const CONTEXT_KEY = 'yireo_html_hints';

    public function __construct(
        private readonly HintGuard $hintGuard,
        private readonly HttpContext $httpContext,
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (false === $this->hintGuard->isEnabled()) {
            return;
        }

        $this->httpContext->setValue(self::CONTEXT_KEY, 1, 0);
    }
}
