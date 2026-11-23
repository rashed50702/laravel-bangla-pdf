<?php

namespace Rashed50702\BanglaPdf\Fonts;

use Mpdf\Language\LanguageToFont;
use Mpdf\Language\LanguageToFontInterface;

final class BengaliLanguageToFont implements LanguageToFontInterface
{
    private readonly LanguageToFont $fallback;

    public function __construct(private readonly string $bengaliFont)
    {
        $this->fallback = new LanguageToFont();
    }

    public function getLanguageOptions($llcc, $adobeCJK): array
    {
        $tags = explode('-', strtolower((string) $llcc));

        if (in_array($tags[0], ['bn', 'as'], true) || in_array('beng', $tags, true)) {
            return [false, $this->bengaliFont];
        }

        return $this->fallback->getLanguageOptions($llcc, $adobeCJK);
    }
}
