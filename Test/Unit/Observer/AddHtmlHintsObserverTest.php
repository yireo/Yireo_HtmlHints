<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Observer\AddHtmlHintsObserver;
use Yireo\HtmlHints\Util\CommentSanitizer;
use Yireo\HtmlHints\Util\HintGuard;
use Yireo\HtmlHints\Util\RenderTimer;

class AddHtmlHintsObserverTest extends TestCase
{
    private const HTML = '<div>x</div>';
    private const TEMPLATE_NAME = 'Magento_Theme::html/title.phtml';
    private const TEMPLATE_FILE = '/var/www/templates/html/title.phtml';

    public function testLeavesTransportUnchangedWhenGuardDeniesHints(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createTemplate($layout, 'page.title');
        $transport = new DataObject(['html' => self::HTML]);
        $observer = new AddHtmlHintsObserver(
            $this->createGuard($layout, false),
            new RenderTimer(),
            new CommentSanitizer()
        );

        $observer->execute($this->createEventObserver($block, $transport));

        $this->assertSame(self::HTML, $transport->getData('html'));
    }

    public function testWrapsHtmlWithBlockTemplateAndTimingHints(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createTemplate($layout, 'page.title');
        $transport = new DataObject(['html' => self::HTML]);
        $timer = $this->createMock(RenderTimer::class);
        $timer->expects($this->once())
            ->method('getElapsedMicroseconds')
            ->with($this->identicalTo($block))
            ->willReturn(1234.0);
        $observer = new AddHtmlHintsObserver($this->createGuard($layout, true), $timer, new CommentSanitizer());

        $observer->execute($this->createEventObserver($block, $transport));

        $this->assertSame(
            '<!-- BLOCK CLASS: ' . get_class($block) . ' / BLOCK NAME: page.title'
            . ' / TEMPLATE NAME: ' . self::TEMPLATE_NAME . ' / TEMPLATE FILE: ' . self::TEMPLATE_FILE
            . ' / MICROSECONDS: 1234 / TIME: 1.23ms -->' . self::HTML . '<!-- END BLOCK page.title -->',
            $transport->getData('html')
        );
    }

    public function testBlockNameCannotTerminateHintCommentsOrInjectMarkup(): void
    {
        $layout = $this->createMock(LayoutInterface::class);
        $block = $this->createTemplate($layout, 'x --><script>alert(1)</script><!--');
        $transport = new DataObject(['html' => self::HTML]);
        $observer = new AddHtmlHintsObserver(
            $this->createGuard($layout, true),
            new RenderTimer(),
            new CommentSanitizer()
        );

        $observer->execute($this->createEventObserver($block, $transport));

        $html = $transport->getData('html');
        $this->assertIsString($html);
        $this->assertSame(2, substr_count($html, '-->'));
        $this->assertSame(2, substr_count($html, '<!--'));
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString(' / MICROSECONDS: 0 / TIME: unknown -->' . self::HTML, $html);
        $this->assertStringContainsString('BLOCK NAME: x', $html);
        $this->assertStringContainsString('<!-- END BLOCK x', $html);
    }

    private function createTemplate(LayoutInterface $layout, string $name): Template
    {
        $block = $this->createMock(Template::class);
        $block->method('getLayout')->willReturn($layout);
        $block->method('getNameInLayout')->willReturn($name);
        $block->method('getTemplate')->willReturn(self::TEMPLATE_NAME);
        $block->method('getTemplateFile')->willReturn(self::TEMPLATE_FILE);

        return $block;
    }

    private function createGuard(LayoutInterface $layout, bool $allowed): HintGuard
    {
        $guard = $this->createMock(HintGuard::class);
        $guard->expects($this->once())
            ->method('allows')
            ->with($this->identicalTo($layout), self::HTML)
            ->willReturn($allowed);

        return $guard;
    }

    private function createEventObserver(Template $block, DataObject $transport): Observer
    {
        return new Observer(['event' => new Event(['block' => $block, 'transport' => $transport])]);
    }
}
