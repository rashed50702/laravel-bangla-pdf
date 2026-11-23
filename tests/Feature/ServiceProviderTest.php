<?php

namespace Rashed50702\BanglaPdf\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use Rashed50702\BanglaPdf\BanglaPdfServiceProvider;
use Rashed50702\BanglaPdf\Facades\BanglaPdf;
use Rashed50702\BanglaPdf\PdfFactory;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [BanglaPdfServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['BanglaPdf' => BanglaPdf::class];
    }

    public function test_binds_factory(): void
    {
        $this->assertInstanceOf(PdfFactory::class, $this->app->make('bangla-pdf'));
    }

    public function test_renders_blade_directives(): void
    {
        $this->assertSame('৳১,০০০.০০ | এক হাজার টাকা মাত্র | ২০২৬', Blade::render('@taka(1000) | @takaInWords(1000) | @bnDigits(2026)'));
    }

    public function test_downloads_pdf_from_html(): void
    {
        $response = BanglaPdf::html('<p>বাংলা</p>')->download('রিপোর্ট.pdf');

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_doctor_command_passes_with_default_font(): void
    {
        $this->artisan('bangla-pdf:doctor')->assertSuccessful();
    }
}
