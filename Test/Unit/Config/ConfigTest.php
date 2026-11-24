<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Config\Config;

class ConfigTest extends TestCase
{
    #[DataProvider('configurations')]
    public function testIsEnabledRequiresBothTheStoreFlagAndDeveloperMode(
        bool $flag,
        string $mode,
        bool $expected
    ): void {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('dev/html_hints/enabled', ScopeInterface::SCOPE_STORE)
            ->willReturn($flag);

        $state = $this->createMock(State::class);
        $state->method('getMode')->willReturn($mode);

        $this->assertSame($expected, (new Config($scopeConfig, $state))->isEnabled());
    }

    /**
     * @return array<string, array{bool, string, bool}>
     */
    public static function configurations(): array
    {
        return [
            'flag on, developer mode' => [true, State::MODE_DEVELOPER, true],
            'flag on, production mode' => [true, State::MODE_PRODUCTION, false],
            'flag on, default mode' => [true, State::MODE_DEFAULT, false],
            'flag off, developer mode' => [false, State::MODE_DEVELOPER, false],
            'flag off, production mode' => [false, State::MODE_PRODUCTION, false],
            'flag off, default mode' => [false, State::MODE_DEFAULT, false],
        ];
    }
}
