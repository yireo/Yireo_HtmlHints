<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Util;

use WeakMap;

class RenderTimer
{
    /**
     * @var WeakMap<object, float>
     */
    private WeakMap $startTimes;

    public function __construct()
    {
        $this->startTimes = new WeakMap();
    }

    public function start(object $block): void
    {
        $this->startTimes[$block] = microtime(true);
    }

    public function getElapsedMicroseconds(object $block): ?float
    {
        if (false === isset($this->startTimes[$block])) {
            return null;
        }

        return (microtime(true) - $this->startTimes[$block]) * 1000000;
    }
}
