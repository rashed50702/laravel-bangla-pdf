<?php

namespace Rashed50702\BanglaPdf\Commands;

use Illuminate\Console\Command;
use Rashed50702\BanglaPdf\PdfFactory;
use Rashed50702\BanglaPdf\Support\Bangla;
use Throwable;

class DoctorCommand extends Command
{
    protected $signature = 'bangla-pdf:doctor {--sample= : Save a sample PDF to this path}';

    protected $description = 'Check Bangla PDF fonts and settings, and optionally create a sample PDF';

    public function handle(PdfFactory $factory): int
    {
        $config = config('bangla-pdf');
        $failed = false;
        $rows = [];

        $tempDir = $config['temp_dir'];
        $writable = (is_dir($tempDir) || @mkdir($tempDir, 0775, true)) && is_writable($tempDir);
        $rows[] = ['Temp directory', $tempDir, $writable ? 'OK' : 'Not writable'];
        $failed = $failed || !$writable;

        foreach ($config['fonts'] ?? [] as $name => $files) {
            foreach (array_filter($files, 'is_string') as $style => $file) {
                $exists = is_file(rtrim($config['font_path'], '/') . '/' . $file);
                $rows[] = ["Font {$name} ({$style})", $file, $exists ? 'Found' : 'Missing in ' . $config['font_path']];
                $failed = $failed || !$exists;
            }
        }

        $fonts = array_unique([strtolower($config['bengali_font']), ...array_map('strtolower', array_keys($config['fonts'] ?? []))]);

        foreach ($fonts as $font) {
            try {
                $factory->make(['bengali_font' => $font, 'footer' => null])->html('<p>আমার সোনার বাংলা ক্ষ্ম স্ত্র শ্রী র্ক কৌ</p>')->output();
                $rows[] = ["Render test: {$font}", 'Bangla conjuncts', 'OK'];
            } catch (Throwable $e) {
                $rows[] = ["Render test: {$font}", 'Bangla conjuncts', 'Failed: ' . $e->getMessage()];
                $failed = true;
            }
        }

        $this->table(['Check', 'Value', 'Status'], $rows);

        if ($path = $this->option('sample')) {
            $factory->make()
                ->title('Bangla PDF sample')
                ->html($this->sampleHtml())
                ->save($path);
            $this->info("Sample PDF saved to {$path}");
        }

        $failed ? $this->error('Some checks failed. See the README section "Choosing a Bangla font".') : $this->info('All checks passed.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function sampleHtml(): string
    {
        $amount = 1250500.75;

        return '<h2>প্রত্যয়নপত্র — Certificate</h2>'
            . '<p>তারিখ: ' . Bangla::date('now') . '</p>'
            . '<p>এই মর্মে প্রত্যয়ন করা যাচ্ছে যে, “মোঃ রহিম উদ্দিন” আমাদের প্রতিষ্ঠানে কর্মরত আছেন। Mixed English text works too…</p>'
            . '<table class="table-bordered"><thead><tr><th>বিবরণ / Description</th><th class="th-right">টাকা / Amount</th></tr></thead><tbody>'
            . '<tr><td>মোট অনুদান</td><td class="text-right">' . Bangla::taka($amount) . '</td></tr>'
            . '<tr><td>Total grant</td><td class="text-right">' . Bangla::taka($amount, banglaDigits: false) . '</td></tr>'
            . '<tr class="row-total"><td colspan="2">কথায়: ' . Bangla::takaInWords($amount) . '</td></tr>'
            . '</tbody></table>';
    }
}
