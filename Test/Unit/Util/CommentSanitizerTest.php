<?php

declare(strict_types=1);

namespace Yireo\HtmlHints\Test\Unit\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\HtmlHints\Util\CommentSanitizer;

class CommentSanitizerTest extends TestCase
{
    #[DataProvider('safeValues')]
    public function testPreservesReadableHintValues(string $value): void
    {
        $this->assertSame($value, (new CommentSanitizer())->sanitize($value));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function safeValues(): array
    {
        return [
            'class name' => ['Magento\Framework\View\Element\Template'],
            'file path' => ['/var/www/app/design/frontend/Vendor/theme/Magento_Theme/templates/html/title.phtml'],
            'template identifier' => ['Magento_Theme::html/title.phtml'],
        ];
    }

    #[DataProvider('unsafeValues')]
    public function testNeutralizesCommentDelimitersAndUnsafeCharacters(string $value): void
    {
        $result = (new CommentSanitizer())->sanitize($value);

        $this->assertNotSame($value, $result);
        $this->assertStringNotContainsString('--', $result);
        $this->assertDoesNotMatchRegularExpression('/[<>"\'\r\n]/', $result);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeValues(): array
    {
        return [
            'closing delimiter' => ['-->'],
            'alternate closing delimiter' => ['--!>'],
            'script tag' => ['<script>'],
            'quotes' => ['"double" and \'single\''],
            'line breaks' => ["first\r\nsecond\nthird"],
            'two hyphens' => ['--'],
            'three hyphens' => ['---'],
            'four hyphens' => ['----'],
        ];
    }
}
