<?php

namespace Tests\Unit;

use App\Support\HttpUrlValidator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HttpUrlValidatorTest extends TestCase
{
    #[Test]
    public function it_allows_null_values(): void
    {
        HttpUrlValidator::validateOrFail(null);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_non_urls(): void
    {
        try {
            HttpUrlValidator::validateOrFail('not-a-url', 'website');
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['website' => ['Некоректний URL вебсайту.']],
                $e->errors()
            );
        }
    }

    #[Test]
    public function it_rejects_non_http_schemes(): void
    {
        // Some non-http values may not be considered valid URLs by FILTER_VALIDATE_URL
        // (e.g. "javascript:"), but they still must be rejected.
        try {
            HttpUrlValidator::validateOrFail('javascript:alert(1)', 'website');
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['website' => ['Некоректний URL вебсайту.']],
                $e->errors()
            );
        }

        try {
            HttpUrlValidator::validateOrFail('ftp://example.com', 'website');
            $this->fail('Expected ValidationException to be thrown.');
        } catch (ValidationException $e) {
            $this->assertSame(
                ['website' => ['URL вебсайту має починатися з http:// або https://']],
                $e->errors()
            );
        }
    }

    #[Test]
    public function it_allows_http_and_https_urls(): void
    {
        HttpUrlValidator::validateOrFail('http://example.com', 'website');
        HttpUrlValidator::validateOrFail('https://example.com', 'website');

        $this->assertTrue(true);
    }
}
