<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Plugin;

use Magento\Framework\View\Layout;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Plugin\AddContainerHintsPlugin;
use Yireo\HtmlHints\Util\CommentSanitizer;
use Yireo\HtmlHints\Util\HintGuard;

class AddContainerHintsPluginTest extends TestCase
{
    public function testLeavesNonContainerOutputUnchangedWithoutConsultingGuard(): void
    {
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())->method('isContainer')->with('x')->willReturn(false);
        $guard = $this->createMock(HintGuard::class);
        $guard->expects($this->never())->method('allows');

        $plugin = new AddContainerHintsPlugin($guard, new CommentSanitizer());

        $this->assertSame('<div>x</div>', $plugin->afterRenderNonCachedElement($layout, '<div>x</div>', 'x'));
    }

    public function testLeavesContainerOutputUnchangedWhenGuardDeniesHints(): void
    {
        $html = '<div>x</div>';
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())->method('isContainer')->with('x')->willReturn(true);
        $guard = $this->createMock(HintGuard::class);
        $guard->expects($this->once())->method('allows')->with($this->identicalTo($layout), $html)->willReturn(false);

        $plugin = new AddContainerHintsPlugin($guard, new CommentSanitizer());

        $this->assertSame($html, $plugin->afterRenderNonCachedElement($layout, $html, 'x'));
    }

    #[DataProvider('containerNames')]
    public function testWrapsAllowedContainerOutputWithSafeName(string $name, string $expectedName): void
    {
        $html = '<div>x</div>';
        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())->method('isContainer')->with($name)->willReturn(true);
        $guard = $this->createMock(HintGuard::class);
        $guard->expects($this->once())->method('allows')->with($this->identicalTo($layout), $html)->willReturn(true);

        $plugin = new AddContainerHintsPlugin($guard, new CommentSanitizer());

        $this->assertSame(
            '<!-- CONTAINER NAME: ' . $expectedName . ' -->' . $html
            . '<!-- END CONTAINER NAME: ' . $expectedName . ' -->',
            $plugin->afterRenderNonCachedElement($layout, $html, $name)
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function containerNames(): array
    {
        return [
            'plain name' => ['x', 'x'],
            'name with markup' => ['<x>', 'x'],
        ];
    }
}
