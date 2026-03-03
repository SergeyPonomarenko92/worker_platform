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
    public function it_allows_valid_http_and_https_urls(): void
    {
        HttpUrlValidator::validateOrFail('http://example.test');
        HttpUrlValidator::validateOrFail('https://example.test/path?x=1');

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_non_urls(): void
    {
        try {
            HttpUrlValidator::validateOrFail('not-a-url');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
            $this->assertSame('Некоректний URL вебсайту.', $e->errors()['website'][0] ?? null);
        }
    }

    #[Test]
    public function it_uses_custom_field_name_in_validation_errors(): void
    {
        try {
            HttpUrlValidator::validateOrFail('not-a-url', 'contact_url');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('contact_url', $e->errors());
            $this->assertSame('Некоректний URL вебсайту.', $e->errors()['contact_url'][0] ?? null);
        }
    }

    #[Test]
    public function it_rejects_non_http_schemes(): void
    {
        try {
            HttpUrlValidator::validateOrFail('javascript:alert(1)');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
            // PHP's FILTER_VALIDATE_URL rejects this value entirely, so we expect the generic message.
            $this->assertSame('Некоректний URL вебсайту.', $e->errors()['website'][0] ?? null);
        }

        try {
            HttpUrlValidator::validateOrFail('ftp://example.test');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website', $e->errors());
            $this->assertSame('URL вебсайту має починатися з http:// або https://', $e->errors()['website'][0] ?? null);
        }
    }
}
