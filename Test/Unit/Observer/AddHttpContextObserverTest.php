<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Observer;

use Magento\Framework\App\Http\Context;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Observer\AddHttpContextObserver;
use Yireo\HtmlHints\Util\HintGuard;

class AddHttpContextObserverTest extends TestCase
{
    #[DataProvider('guardStates')]
    public function testSetsHttpContextOnlyWhenHintsAreEnabled(bool $enabled): void
    {
        $guard = $this->createMock(HintGuard::class);
        $guard->method('isEnabled')->willReturn($enabled);
        $context = $this->createMock(Context::class);
        if ($enabled) {
            $context->expects($this->once())
                ->method('setValue')
                ->with('yireo_html_hints', $this->identicalTo(1), $this->identicalTo(0));
        } else {
            $context->expects($this->never())->method('setValue');
        }

        (new AddHttpContextObserver($guard, $context))->execute(new Observer());
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function guardStates(): array
    {
        return ['enabled' => [true], 'disabled' => [false]];
    }
}
