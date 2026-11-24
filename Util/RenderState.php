<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Util;

class RenderState
{
    private int $renderDepth = 0;
    private int $suspendDepth = 0;

    public function startRendering(): void
    {
        $this->renderDepth++;
    }

    public function stopRendering(): void
    {
        $this->renderDepth = max(0, $this->renderDepth - 1);
    }

    public function suspend(): void
    {
        $this->suspendDepth++;
    }

    public function resume(): void
    {
        $this->suspendDepth = max(0, $this->suspendDepth - 1);
    }

    public function isActive(): bool
    {
        return $this->renderDepth > 0 && $this->suspendDepth === 0;
    }
}
