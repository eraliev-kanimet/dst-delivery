<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') == 'local') {
            DB::enableQueryLog();
        }

        Filament::serving(function () {
            $current = session('locale', config('app.fallback_locale'));
            $locales = [];

            foreach (config('app.locales') as $locale => $name) {
                if ($current != $locale) {
                    $locales[] = MenuItem::make()
                        ->label($name)
                        ->url(route('set.locale', $locale))
                        ->icon('heroicon-m-language');
                }
            }

            Filament::registerUserMenuItems($locales);
        });
    }
}
