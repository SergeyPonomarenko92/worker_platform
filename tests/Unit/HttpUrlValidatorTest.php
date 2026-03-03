<?php

namespace Tests\Unit;

use App\Support\HttpUrlValidator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HttpUrlValidatorTest extends TestCase
{
    #[DataProvider('validCases')]
    public function test_it_allows_null_and_valid_http_urls(?string $input): void
    {
        HttpUrlValidator::validateOrFail($input, 'website');

        $this->assertTrue(true);
    }

    #[DataProvider('invalidCases')]
    public function test_it_rejects_invalid_or_non_http_urls(string $input, string $expectedMessage): void
    {
        try {
            HttpUrlValidator::validateOrFail($input, 'website');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $messages = $e->errors();
            $this->assertArrayHasKey('website', $messages);
            $this->assertSame([$expectedMessage], $messages['website']);
        }
    }

    public static function validCases(): array
    {
        return [
            'null' => [null],
            'https' => ['https://example.com'],
            'http' => ['http://example.com'],
            'mixed case scheme' => ['HTTPS://example.com'],
            'url with path query fragment' => ['https://example.com/path?x=1#top'],
        ];
    }

    public static function invalidCases(): array
    {
        return [
            'not a url' => ['not-a-url', 'Некоректний URL вебсайту.'],
            'javascript scheme' => ['javascript:alert(1)', 'Некоректний URL вебсайту.'],
            'mailto scheme' => ['mailto:test@example.com', 'URL вебсайту має починатися з http:// або https://'],
            'ftp scheme' => ['ftp://example.com', 'URL вебсайту має починатися з http:// або https://'],
        ];
    }
}
