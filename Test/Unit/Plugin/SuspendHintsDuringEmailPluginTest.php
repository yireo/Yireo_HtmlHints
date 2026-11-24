<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Plugin;

use Magento\Framework\Mail\TemplateInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\HtmlHints\Plugin\SuspendHintsDuringEmailPlugin;
use Yireo\HtmlHints\Util\RenderState;

class SuspendHintsDuringEmailPluginTest extends TestCase
{
    public function testSuspendsHintsOnlyWhileProcessingTemplate(): void
    {
        $state = new RenderState();
        $state->startRendering();
        $activeDuringProceed = null;

        $result = (new SuspendHintsDuringEmailPlugin($state))->aroundProcessTemplate(
            $this->createMock(TemplateInterface::class),
            function () use ($state, &$activeDuringProceed): string {
                $activeDuringProceed = $state->isActive();
                return '<p>mail</p>';
            }
        );

        $this->assertSame('<p>mail</p>', $result);
        $this->assertFalse($activeDuringProceed);
        $this->assertTrue($state->isActive());
    }

    public function testResumesHintsWhenProcessingThrows(): void
    {
        $state = new RenderState();
        $state->startRendering();
        $plugin = new SuspendHintsDuringEmailPlugin($state);

        try {
            $plugin->aroundProcessTemplate(
                $this->createMock(TemplateInterface::class),
                function (): never {
                    throw new RuntimeException('mail failed');
                }
            );
            $this->fail('Expected exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('mail failed', $exception->getMessage());
        }

        $this->assertTrue($state->isActive());
    }
}
