<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Util;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Config\Config;
use Yireo\HtmlHints\Util\HintGuard;

class HintGuardTest extends TestCase
{
    #[DataProvider('requestStates')]
    public function testIsEnabledRequiresEnabledConfigAndNonAjaxRequest(
        bool $enabled,
        bool $ajax,
        bool $expected
    ): void {
        $layout = $this->createMock(LayoutInterface::class);
        $guard = $this->createGuard($layout, $enabled, $ajax);

        $this->assertSame($expected, $guard->isEnabled());
        $this->assertSame($expected, $guard->allows($layout, '<div>x</div>'));
    }

    /**
     * @return array<string, array{bool, bool, bool}>
     */
    public static function requestStates(): array
    {
        return [
            'enabled, non-Ajax' => [true, false, true],
            'enabled, Ajax' => [true, true, false],
            'disabled, non-Ajax' => [false, false, false],
            'disabled, Ajax' => [false, true, false],
        ];
    }

    public function testAllowsOnlyTheSharedLayoutInstance(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $differentLayout = $this->createMock(LayoutInterface::class);
        $guard = $this->createGuard($layout, true, false);

        $this->assertFalse($guard->allows($differentLayout, '<div>x</div>'));
        $this->assertFalse($guard->allows(null, '<div>x</div>'));
    }

    #[DataProvider('htmlValues')]
    public function testAllowsOnlyHtmlStartingWithATag(mixed $html, bool $expected): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $guard = $this->createGuard($layout, true, false);

        $this->assertSame($expected, $guard->allows($layout, $html));
    }

    /**
     * @return array<string, array{int|string, bool}>
     */
    public static function htmlValues(): array
    {
        return [
            'non-string' => [42, false],
            'empty string' => ['', false],
            'whitespace' => [" \t\r\n", false],
            'plain text' => ['text', false],
            'HTML comment' => ['<!-- c -->', false],
            'HTML element' => ['<div>x</div>', true],
        ];
    }

    private function createGuard(LayoutInterface $layout, bool $enabled, bool $ajax): HintGuard
    {
        $config = $this->createMock(Config::class);
        $config->method('isEnabled')->willReturn($enabled);

        $request = $this->createMock(Http::class);
        $request->method('isAjax')->willReturn($ajax);

        return new HintGuard($config, $request, $layout);
    }
}
