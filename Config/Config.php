<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_ENABLED = 'dev/html_hints/enabled';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly State $appState,
    ) {
    }

    public function isEnabled(): bool
    {
        if (false === $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }
}
