<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Util;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Yireo\HtmlHints\Config\Config;

class HintGuard
{
    public function __construct(
        private readonly Config $config,
        private readonly RequestInterface $request,
        private readonly LayoutInterface $layout,
        private readonly RenderState $renderState,
    ) {
    }

    public function isEnabled(): bool
    {
        if (false === $this->config->isEnabled()) {
            return false;
        }

        return !($this->request instanceof HttpRequest && $this->request->isAjax());
    }

    public function allows(?LayoutInterface $layout, mixed $html): bool
    {
        if (false === $this->renderState->isActive()) {
            return false;
        }

        if (false === $this->isEnabled()) {
            return false;
        }

        if ($layout !== $this->layout) {
            return false;
        }

        if (false === is_string($html)) {
            return false;
        }

        $html = trim($html, " \f\n\r\t\v\x00");
        if ($html === '') {
            return false;
        }

        return (bool)preg_match('/^<([a-z]{2,})/', $html);
    }
}
