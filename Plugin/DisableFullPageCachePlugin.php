<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Plugin;

use Magento\PageCache\Model\Config as PageCacheConfig;
use Yireo\HtmlHints\Config\Config;

class DisableFullPageCachePlugin
{
    public function __construct(
        private readonly Config $config,
    ) {
    }

    public function afterIsEnabled(PageCacheConfig $subject, bool $result): bool
    {
        if (false === $result) {
            return false;
        }

        return false === $this->config->isEnabled();
    }
}
