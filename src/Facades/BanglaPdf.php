<?php

namespace Rashed50702\BanglaPdf\Facades;

use Illuminate\Support\Facades\Facade;
use Rashed50702\BanglaPdf\Document;
use Rashed50702\BanglaPdf\PdfFactory;

/**
 * @method static Document make(array $overrides = [])
 *
 * @see PdfFactory
 */
class BanglaPdf extends Facade
{
    public static function view(string $view, array $data = [], array $overrides = []): Document
    {
        return static::make($overrides)->view($view, $data);
    }

    public static function html(string $html, array $overrides = []): Document
    {
        return static::make($overrides)->html($html);
    }

    protected static function getFacadeAccessor(): string
    {
        return PdfFactory::class;
    }
}
