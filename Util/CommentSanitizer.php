<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Util;

class CommentSanitizer
{
    public function sanitize(string $value): string
    {
        $value = (string)preg_replace('/[^A-Za-z0-9_.:\/\\\\ -]/', '', $value);
        while (str_contains($value, '--')) {
            $value = str_replace('--', '- -', $value);
        }

        return $value;
    }
}
