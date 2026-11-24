<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Plugin;

use Magento\Framework\View\Layout;
use Yireo\HtmlHints\Util\CommentSanitizer;
use Yireo\HtmlHints\Util\HintGuard;

class AddContainerHintsPlugin
{
    public function __construct(
        private readonly HintGuard $hintGuard,
        private readonly CommentSanitizer $commentSanitizer,
    ) {
    }

    public function afterRenderNonCachedElement(Layout $subject, mixed $result, string $name): mixed
    {
        if (false === $subject->isContainer($name)) {
            return $result;
        }

        if (false === $this->hintGuard->allows($subject, $result)) {
            return $result;
        }

        $containerName = $this->commentSanitizer->sanitize($name);

        return '<!-- CONTAINER NAME: ' . $containerName . ' -->'
            . $result
            . '<!-- END CONTAINER NAME: ' . $containerName . ' -->';
    }
}
