<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yireo\HtmlHints\Observer\AddAdditionalDataObserver;
use Yireo\HtmlHints\Util\HintGuard;
use Yireo\HtmlHints\Util\RenderTimer;

class AddAdditionalDataObserverTest extends TestCase
{
    #[DataProvider('guardStates')]
    public function testStartsTimerOnlyWhenHintsAreEnabled(bool $enabled): void
    {
        $block = new stdClass();
        $guard = $this->createMock(HintGuard::class);
        $guard->method('isEnabled')->willReturn($enabled);
        $timer = new RenderTimer();
        $observer = new AddAdditionalDataObserver($guard, $timer);

        $observer->execute(new Observer(['event' => new Event(['block' => $block])]));

        $elapsed = $timer->getElapsedMicroseconds($block);
        if ($enabled) {
            $this->assertIsFloat($elapsed);
            $this->assertGreaterThanOrEqual(0.0, $elapsed);
        } else {
            $this->assertNull($elapsed);
        }
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function guardStates(): array
    {
        return ['enabled' => [true], 'disabled' => [false]];
    }
}
