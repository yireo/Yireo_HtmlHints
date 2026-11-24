<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Util;

use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Util\RenderState;

class RenderStateTest extends TestCase
{
    public function testIsInactiveByDefault(): void
    {
        $this->assertFalse((new RenderState())->isActive());
    }

    public function testNestedRenderingStaysActiveUntilOutermostStop(): void
    {
        $state = new RenderState();

        $state->startRendering();
        $state->startRendering();
        $this->assertTrue($state->isActive());

        $state->stopRendering();
        $this->assertTrue($state->isActive());

        $state->stopRendering();
        $this->assertFalse($state->isActive());
    }

    public function testSuspensionDeactivatesUntilOutermostResume(): void
    {
        $state = new RenderState();
        $state->startRendering();

        $state->suspend();
        $state->suspend();
        $this->assertFalse($state->isActive());

        $state->resume();
        $this->assertFalse($state->isActive());

        $state->resume();
        $this->assertTrue($state->isActive());
    }

    public function testSuspensionWithoutRenderingStaysInactive(): void
    {
        $state = new RenderState();
        $state->suspend();
        $state->resume();

        $this->assertFalse($state->isActive());
    }

    public function testUnbalancedStopAndResumeDoNotGoBelowZero(): void
    {
        $state = new RenderState();
        $state->stopRendering();
        $state->resume();

        $state->startRendering();
        $this->assertTrue($state->isActive());

        $state->suspend();
        $this->assertFalse($state->isActive());
    }
}
