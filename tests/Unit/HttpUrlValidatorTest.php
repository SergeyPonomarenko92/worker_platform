<?php

namespace Tests\Unit;

use App\Support\HttpUrlValidator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HttpUrlValidatorTest extends TestCase
{
    public static function validUrlProvider(): array
    {
        return [
            'null is allowed' => [null],
            'empty string is treated as missing' => [''],
            'spaces-only string is treated as missing' => ["   \n\t"],
            'unicode spaces-only string is treated as missing' => ["\u{00A0}\u{202F}"],

            'https url is allowed' => ['https://example.com'],
            'http url is allowed' => ['http://example.com/path?x=1#y'],
            'url with surrounding whitespace is allowed' => ["  https://example.com/path  \n"],
        ];
    }

    #[DataProvider('validUrlProvider')]
    public function test_allows_valid_http_urls(?string $url): void
    {
        HttpUrlValidator::validateOrFail($url);

        $this->assertTrue(true); // no exception
    }

    public static function invalidUrlProvider(): array
    {
        return [
            'plain text is rejected' => ['example.com'],
            'not a url is rejected' => ['not a url'],
            'mailto scheme is rejected' => ['mailto:test@example.com'],
            'ftp scheme is rejected' => ['ftp://example.com'],
            'javascript scheme is rejected' => ['javascript:alert(1)'],
        ];
    }

    #[DataProvider('invalidUrlProvider')]
    public function test_rejects_non_http_urls(string $url): void
    {
        $this->expectException(ValidationException::class);

        HttpUrlValidator::validateOrFail($url);
    }
}
