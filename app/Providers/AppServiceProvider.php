<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Filament::serving(function () {
            $current = session('locale', config('app.fallback_locale'));
            $locales = [];

            foreach (config('app.locales') as $locale => $name) {
                if ($current != $locale) {
                    $locales[] = UserMenuItem::make()
                        ->label($name)
                        ->url(route('set.locale', $locale))
                        ->icon('heroicon-s-translate');
                }
            }

            Filament::registerUserMenuItems($locales);
        });
    }
}
