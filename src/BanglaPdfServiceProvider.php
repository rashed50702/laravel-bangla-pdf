<?php

namespace Rashed50702\BanglaPdf;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Rashed50702\BanglaPdf\Commands\DoctorCommand;

class BanglaPdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bangla-pdf.php', 'bangla-pdf');

        $this->app->singleton(PdfFactory::class, fn ($app) => new PdfFactory($app['config']->get('bangla-pdf', []), $app['view']));
        $this->app->alias(PdfFactory::class, 'bangla-pdf');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/bangla-pdf.php' => config_path('bangla-pdf.php')], 'bangla-pdf-config');
            $this->publishes([__DIR__ . '/../resources/css/bangla-pdf.css' => resource_path('css/bangla-pdf.css')], 'bangla-pdf-css');
            $this->commands([DoctorCommand::class]);
        }

        Blade::directive('bnDigits', fn (string $expression) => "<?php echo e(\\Rashed50702\\BanglaPdf\\Support\\Bangla::digits($expression)); ?>");
        Blade::directive('bnNumber', fn (string $expression) => "<?php echo e(\\Rashed50702\\BanglaPdf\\Support\\Bangla::number($expression)); ?>");
        Blade::directive('taka', fn (string $expression) => "<?php echo e(\\Rashed50702\\BanglaPdf\\Support\\Bangla::taka($expression)); ?>");
        Blade::directive('takaInWords', fn (string $expression) => "<?php echo e(\\Rashed50702\\BanglaPdf\\Support\\Bangla::takaInWords($expression)); ?>");
        Blade::directive('bnDate', fn (string $expression) => "<?php echo e(\\Rashed50702\\BanglaPdf\\Support\\Bangla::date($expression)); ?>");
    }
}
