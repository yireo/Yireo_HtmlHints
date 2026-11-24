<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Plugin;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Layout;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\HtmlHints\Plugin\EnableHintsDuringRenderPlugin;
use Yireo\HtmlHints\Util\RenderState;

class EnableHintsDuringRenderPluginTest extends TestCase
{
    public function testActivatesRenderStateOnlyWhileProceeding(): void
    {
        $state = new RenderState();
        $subject = $this->createMock(Layout::class);
        $response = $this->createMock(ResponseInterface::class);
        $activeDuringProceed = null;
        $passedResponse = null;

        $result = (new EnableHintsDuringRenderPlugin($state))->aroundRenderResult(
            $subject,
            function (ResponseInterface $argument) use ($state, $subject, &$activeDuringProceed, &$passedResponse) {
                $activeDuringProceed = $state->isActive();
                $passedResponse = $argument;
                return $subject;
            },
            $response
        );

        $this->assertSame($subject, $result);
        $this->assertTrue($activeDuringProceed);
        $this->assertSame($response, $passedResponse);
        $this->assertFalse($state->isActive());
    }

    public function testResetsRenderStateWhenRenderingThrows(): void
    {
        $state = new RenderState();
        $plugin = new EnableHintsDuringRenderPlugin($state);

        try {
            $plugin->aroundRenderResult(
                $this->createMock(Layout::class),
                function (): never {
                    throw new RuntimeException('render failed');
                },
                $this->createMock(ResponseInterface::class)
            );
            $this->fail('Expected exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('render failed', $exception->getMessage());
        }

        $this->assertFalse($state->isActive());
        $state->startRendering();
        $this->assertTrue($state->isActive());
    }
}
