<?php

namespace Rashed50702\BanglaPdf;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Rashed50702\BanglaPdf\Support\Bangla;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

final class Document
{
    private bool $stylesLoaded = false;

    public function __construct(private readonly Mpdf $mpdf, private readonly array $config, private readonly ?ViewFactory $views = null)
    {
        if (!empty($config['footer'])) {
            $this->footer($config['footer']);
        }
    }

    public function view(string $view, array $data = []): self
    {
        if (!$this->views) {
            throw new RuntimeException('A view factory is required to render Blade views.');
        }

        return $this->html($this->views->make($view, $data)->render());
    }

    public function html(string $html): self
    {
        $this->loadDefaultStyles();

        $this->mpdf->WriteHTML($this->protect($html), HTMLParserMode::HTML_BODY);

        return $this;
    }

    public function css(string $cssOrPath): self
    {
        $this->loadDefaultStyles();
        $this->mpdf->WriteHTML(is_file($cssOrPath) ? file_get_contents($cssOrPath) : $cssOrPath, HTMLParserMode::HEADER_CSS);

        return $this;
    }

    public function header(string $viewOrHtml, array $data = []): self
    {
        $this->mpdf->SetHTMLHeader($this->renderPartial($viewOrHtml, $data));

        return $this;
    }

    public function footer(string $viewOrHtml, array $data = []): self
    {
        $this->mpdf->SetHTMLFooter($this->renderPartial($viewOrHtml, $data));

        return $this;
    }

    public function pageBreak(): self
    {
        $this->mpdf->AddPage();

        return $this;
    }

    public function title(string $title): self
    {
        $this->mpdf->SetTitle($title);

        return $this;
    }

    public function watermark(string $text, float $alpha = 0.08): self
    {
        $this->mpdf->SetWatermarkText($text, $alpha);
        $this->mpdf->showWatermarkText = true;

        return $this;
    }

    public function output(): string
    {
        return $this->mpdf->Output('', Destination::STRING_RETURN);
    }

    public function save(string $path): string
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $this->output());

        return $path;
    }

    public function download(string $filename = 'document.pdf'): Response
    {
        return $this->response($filename, 'attachment');
    }

    public function stream(string $filename = 'document.pdf'): Response
    {
        return $this->response($filename, 'inline');
    }

    public function mpdf(): Mpdf
    {
        return $this->mpdf;
    }

    private function response(string $filename, string $disposition): Response
    {
        $filename = str_ends_with(strtolower($filename), '.pdf') ? $filename : $filename . '.pdf';
        $fallback = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

        return new Response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
        ]);
    }

    private function renderPartial(string $viewOrHtml, array $data): string
    {
        $html = $this->views && !str_contains($viewOrHtml, '<') && $this->views->exists($viewOrHtml)
            ? $this->views->make($viewOrHtml, $data)->render()
            : $viewOrHtml;

        return $this->protect($html);
    }

    private function protect(string $html): string
    {
        return ($this->config['protect_punctuation'] ?? true)
            ? Bangla::protectPunctuation($html, $this->config['latin_font'] ?? 'freeserif', $this->config['bengali_font'] ?? 'freeserifbangla')
            : $html;
    }

    private function loadDefaultStyles(): void
    {
        if ($this->stylesLoaded) {
            return;
        }

        $this->stylesLoaded = true;
        $path = $this->config['stylesheet'] ?? dirname(__DIR__) . '/resources/css/bangla-pdf.css';

        if ($path !== false && is_file($path)) {
            $this->mpdf->WriteHTML(file_get_contents($path), HTMLParserMode::HEADER_CSS);
        }

        $scale = (float) ($this->config['bengali_scale'] ?? 1.15);

        if ($scale > 0 && abs($scale - 1) > 0.001) {
            $this->mpdf->WriteHTML('span[lang=bn] { font-size: ' . round($scale * 100) . '%; }', HTMLParserMode::HEADER_CSS);
        }
    }
}
