<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Plugin;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;
use Yireo\HtmlHints\Util\RenderState;

class EnableHintsDuringRenderPlugin
{
    public function __construct(
        private readonly RenderState $renderState,
    ) {
    }

    public function aroundRenderResult(Layout $subject, callable $proceed, ResponseInterface $response): mixed
    {
        $this->renderState->startRendering();
        try {
            return $proceed($response);
        } finally {
            $this->renderState->stopRendering();
        }
    }
}
