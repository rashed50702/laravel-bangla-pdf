<?php

namespace Rashed50702\BanglaPdf;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Rashed50702\BanglaPdf\Fonts\BengaliLanguageToFont;

final class PdfFactory
{
    public function __construct(private readonly array $config, private readonly ?ViewFactory $views = null)
    {
    }

    public function make(array $overrides = []): Document
    {
        $config = array_replace_recursive($this->config, $overrides);

        return new Document($this->createMpdf($config), $config, $this->views);
    }

    public function createMpdf(array $config): Mpdf
    {
        $tempDir = $config['temp_dir'] ?? sys_get_temp_dir() . '/bangla-pdf';
        $fontPath = $config['font_path'] ?? null;

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $orientation = strtoupper($config['orientation'] ?? 'P');
        $format = $config['format'] ?? 'A4';
        $margins = $config['margins'] ?? [];

        return new Mpdf(array_replace([
            'mode' => 'utf-8',
            'format' => is_string($format) && $orientation === 'L' && !str_ends_with($format, '-L') ? $format . '-L' : $format,
            'orientation' => $orientation,
            'margin_top' => $margins['top'] ?? 10,
            'margin_right' => $margins['right'] ?? 8,
            'margin_bottom' => $margins['bottom'] ?? 15,
            'margin_left' => $margins['left'] ?? 8,
            'margin_header' => $margins['header'] ?? 5,
            'margin_footer' => $margins['footer'] ?? 5,
            'tempDir' => $tempDir,
            'fontDir' => array_values(array_filter([...(new ConfigVariables())->getDefaults()['fontDir'], $fontPath && is_dir($fontPath) ? $fontPath : null])),
            'fontdata' => (new FontVariables())->getDefaults()['fontdata'] + self::fontData($config['fonts'] ?? []),
            'default_font' => $config['latin_font'] ?? 'freeserif',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'languageToFont' => new BengaliLanguageToFont($config['bengali_font'] ?? 'freeserifbangla'),
            'useKashida' => 0,
        ], $config['mpdf'] ?? []));
    }

    private static function fontData(array $fonts): array
    {
        $data = ['freeserifbangla' => ['R' => 'FreeSerif.ttf', 'B' => 'FreeSerif.ttf', 'I' => 'FreeSerif.ttf', 'BI' => 'FreeSerif.ttf', 'useOTL' => 0xFF, 'useKashida' => 0]];

        foreach ($fonts as $name => $files) {
            $data[strtolower($name)] = array_filter($files, 'is_string') + ['useOTL' => 0xFF, 'useKashida' => 0];
        }

        return $data;
    }
}
