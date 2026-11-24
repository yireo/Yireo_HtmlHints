<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Util;

use PHPUnit\Framework\TestCase;
use stdClass;
use Yireo\HtmlHints\Util\RenderTimer;

class RenderTimerTest extends TestCase
{
    public function testTimingIsTrackedIndependentlyForEachObject(): void
    {
        $timer = new RenderTimer();
        $firstBlock = new stdClass();
        $secondBlock = new stdClass();

        $this->assertNull($timer->getElapsedMicroseconds($firstBlock));

        $timer->start($firstBlock);
        $firstElapsed = $timer->getElapsedMicroseconds($firstBlock);
        $this->assertIsFloat($firstElapsed);
        $this->assertGreaterThanOrEqual(0.0, $firstElapsed);
        $this->assertNull($timer->getElapsedMicroseconds($secondBlock));

        $timer->start($secondBlock);
        $secondElapsed = $timer->getElapsedMicroseconds($secondBlock);
        $this->assertIsFloat($secondElapsed);
        $this->assertGreaterThanOrEqual(0.0, $secondElapsed);
        $this->assertNotNull($timer->getElapsedMicroseconds($firstBlock));
    }
}
