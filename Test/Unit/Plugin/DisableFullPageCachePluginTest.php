<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Plugin;

use Magento\PageCache\Model\Config as PageCacheConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Config\Config;
use Yireo\HtmlHints\Plugin\DisableFullPageCachePlugin;

class DisableFullPageCachePluginTest extends TestCase
{
    #[DataProvider('states')]
    public function testDisablesFullPageCacheOnlyWhenHintsAreEnabled(
        bool $cacheEnabled,
        bool $hintsEnabled,
        bool $expected
    ): void {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn($hintsEnabled);

        $plugin = new DisableFullPageCachePlugin($config);

        $this->assertSame(
            $expected,
            $plugin->afterIsEnabled($this->createMock(PageCacheConfig::class), $cacheEnabled)
        );
    }

    /**
     * @return array<string, array{bool, bool, bool}>
     */
    public static function states(): array
    {
        return [
            'cache on, hints on' => [true, true, false],
            'cache on, hints off' => [true, false, true],
            'cache off, hints on' => [false, true, false],
            'cache off, hints off' => [false, false, false],
        ];
    }
}
