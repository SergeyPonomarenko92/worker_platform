<?php

namespace Tests\Unit;

use App\Support\HttpUrlValidator;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HttpUrlValidatorTest extends TestCase
{
    #[Test]
    public function it_allows_null_and_empty_strings(): void
    {
        // Should not throw.
        HttpUrlValidator::validateOrFail(null);
        HttpUrlValidator::validateOrFail('');
        HttpUrlValidator::validateOrFail("\n\t  ");
        HttpUrlValidator::validateOrFail("\u{00A0}   \n\t");

        $this->assertTrue(true);
    }

    #[Test]
    public function it_accepts_http_and_https_urls_and_trims_unicode_spaces(): void
    {
        // Should not throw.
        HttpUrlValidator::validateOrFail('http://example.com');
        HttpUrlValidator::validateOrFail('https://example.com');

        // Including unicode spaces (NBSP, NNBSP etc.) around the URL.
        HttpUrlValidator::validateOrFail("\u{00A0}https://example.com\u{202F}");

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_non_urls(): void
    {
        $this->expectException(ValidationException::class);

        HttpUrlValidator::validateOrFail('not a url');
    }

    #[Test]
    public function it_rejects_non_http_schemes(): void
    {
        try {
            HttpUrlValidator::validateOrFail('javascript:alert(1)', 'website');
            $this->fail('Expected ValidationException for non-http(s) scheme.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
        }

        try {
            HttpUrlValidator::validateOrFail('mailto:test@example.com', 'website');
            $this->fail('Expected ValidationException for non-http(s) scheme.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
        }

        try {
            HttpUrlValidator::validateOrFail('ftp://example.com', 'website');
            $this->fail('Expected ValidationException for non-http(s) scheme.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
        }
    }
}
