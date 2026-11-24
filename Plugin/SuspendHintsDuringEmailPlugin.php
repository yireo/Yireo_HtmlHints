<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Plugin;

use Magento\Framework\Mail\TemplateInterface;
use Yireo\HtmlHints\Util\RenderState;

class SuspendHintsDuringEmailPlugin
{
    public function __construct(
        private readonly RenderState $renderState,
    ) {
    }

    public function aroundProcessTemplate(TemplateInterface $subject, callable $proceed): mixed
    {
        $this->renderState->suspend();
        try {
            return $proceed();
        } finally {
            $this->renderState->resume();
        }
    }
}
