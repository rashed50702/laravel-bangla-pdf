<?php

namespace Rashed50702\BanglaPdf\Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rashed50702\BanglaPdf\Support\Bangla;

class BanglaTest extends TestCase
{
    public function test_converts_digits_both_ways(): void
    {
        $this->assertSame('০১২৩৪৫৬৭৮৯', Bangla::digits('0123456789'));
        $this->assertSame('2026-11-23', Bangla::englishDigits('২০২৬-১১-২৩'));
        $this->assertSame('', Bangla::digits(null));
    }

    public function test_groups_numbers_in_lakh_and_crore(): void
    {
        $this->assertSame('১,২৩,৪৫,৬৭৮', Bangla::number(12345678));
        $this->assertSame('1,00,000.00', Bangla::number(100000, 2, false));
        $this->assertSame('৯৯৯', Bangla::number(999));
        $this->assertSame('-১,০০০.৫০', Bangla::number(-1000.5, 2));
    }

    public function test_formats_taka(): void
    {
        $this->assertSame('৳১২,৫০,৫০০.৭৫', Bangla::taka(1250500.75));
        $this->assertSame('৳12,50,500.75', Bangla::taka(1250500.75, banglaDigits: false));
        $this->assertSame('-৳৫০০.০০', Bangla::taka(-500));
        $this->assertSame('Tk 5,000', Bangla::taka(5000, 0, false, 'Tk '));
        $this->assertSame('৳১,০০০.০০', Bangla::taka('১০০০'));
    }

    #[DataProvider('wordsProvider')]
    public function test_converts_numbers_to_bangla_words(int $number, string $expected): void
    {
        $this->assertSame($expected, Bangla::words($number));
    }

    public static function wordsProvider(): array
    {
        return [
            [0, 'শূন্য'],
            [7, 'সাত'],
            [21, 'একুশ'],
            [99, 'নিরানব্বই'],
            [100, 'একশত'],
            [505, 'পাঁচশত পাঁচ'],
            [1000, 'এক হাজার'],
            [125500, 'এক লক্ষ পঁচিশ হাজার পাঁচশত'],
            [10000000, 'এক কোটি'],
            [1234567890, 'একশত তেইশ কোটি পঁয়তাল্লিশ লক্ষ সাতষট্টি হাজার আটশত নব্বই'],
        ];
    }

    public function test_converts_taka_to_words(): void
    {
        $this->assertSame('বারো লক্ষ পঞ্চাশ হাজার পাঁচশত টাকা পঁচাত্তর পয়সা মাত্র', Bangla::takaInWords(1250500.75));
        $this->assertSame('এক হাজার টাকা মাত্র', Bangla::takaInWords(1000));
        $this->assertSame('পাঁচ টাকা', Bangla::takaInWords(5, ''));
    }

    public function test_rejects_negative_amount_in_words(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Bangla::takaInWords(-1);
    }

    public function test_formats_dates_in_bangla(): void
    {
        $date = new DateTimeImmutable('2026-03-05 14:30:00');

        $this->assertSame('৫ মার্চ, ২০২৬', Bangla::date($date));
        $this->assertSame('বৃহস্পতিবার, ০৫ মার্চ ২০২৬', Bangla::date($date, 'l, d F Y'));
        $this->assertSame('৫ মে ২০২৬', Bangla::date('2026-05-05', 'j M Y'));
        $this->assertSame('০২:৩০ অপরাহ্ণ', Bangla::date($date, 'h:i A'));
        $this->assertSame('', Bangla::date(null));
    }

    public function test_protects_typographic_punctuation_in_text_only(): void
    {
        $html = '<p title="a—b">বাংলা — “উদ্ধৃতি”</p><style>p:after { content: "—"; }</style>';
        $result = Bangla::protectPunctuation($html, 'dejavuserif');

        $this->assertStringContainsString('title="a—b"', $result);
        $this->assertStringContainsString('<span style="font-family: dejavuserif;">—</span>', $result);
        $this->assertStringContainsString('<span style="font-family: dejavuserif;">“</span>', $result);
        $this->assertStringContainsString('content: "—";', $result);
    }

    public function test_keeps_danda_in_bengali_font(): void
    {
        $this->assertSame('আছেন<span lang="bn" style="font-family: kalpurush;">।</span> Next', Bangla::protectPunctuation('আছেন। Next', 'freeserif', 'kalpurush'));
    }

    public function test_protects_html_entities(): void
    {
        $this->assertSame('ক<span style="font-family: dejavuserif;">&mdash;</span>খ', Bangla::protectPunctuation('ক&mdash;খ', 'dejavuserif'));
    }
}
