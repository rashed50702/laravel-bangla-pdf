<?php

namespace Rashed50702\BanglaPdf\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rashed50702\BanglaPdf\Document;
use Rashed50702\BanglaPdf\PdfFactory;

class PdfFactoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/bangla-pdf-tests';
    }

    private function factory(array $config = []): PdfFactory
    {
        return new PdfFactory(array_replace(['temp_dir' => $this->tempDir], $config));
    }

    public function test_makes_a_document(): void
    {
        $this->assertInstanceOf(Document::class, $this->factory()->make());
    }

    public function test_renders_mixed_bangla_and_english_pdf(): void
    {
        $pdf = $this->factory()->make()->html('<p><b>আমার সোনার বাংলা</b> — Hello “world” ক্ষ্ম শ্রী র্ক।</p>')->output();

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('FreeSerif', $pdf);
        $this->assertStringNotContainsString('FreeSerifBold', $pdf);
    }

    public function test_applies_landscape_orientation(): void
    {
        $mpdf = $this->factory(['orientation' => 'L'])->make()->mpdf();

        $this->assertGreaterThan($mpdf->h, $mpdf->w);
    }

    public function test_saves_pdf_to_disk(): void
    {
        $path = $this->tempDir . '/out/sample.pdf';

        $this->factory()->make()->footer('<div>পৃষ্ঠা {PAGENO}</div>')->html('<p>পরীক্ষা</p>')->save($path);

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-', file_get_contents($path));
    }

    public function test_bold_bangla_uses_regular_freeserif_glyphs(): void
    {
        $mpdf = $this->factory()->make()->mpdf();

        $this->assertSame('FreeSerif.ttf', $mpdf->fontdata['freeserifbangla']['B']);
    }

    public function test_registers_custom_fonts_with_otl(): void
    {
        $mpdf = $this->factory(['fonts' => ['SolaimanLipi' => ['R' => 'SolaimanLipi.ttf']]])->make()->mpdf();

        $this->assertSame('SolaimanLipi.ttf', $mpdf->fontdata['solaimanlipi']['R']);
        $this->assertSame(0xFF, $mpdf->fontdata['solaimanlipi']['useOTL']);
    }
}
