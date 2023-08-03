<?php

namespace App\Filament\Resources\CategoryResource;

use App\Helpers\FilamentHelper;
use Filament\Resources\Form;
use Illuminate\Support\Collection;

class CategoryResourceForm
{
    public static function form(Form $form, array|Collection $categories, ?int $category_id, bool $create = true): Form
    {
        $helper = new FilamentHelper;

        $locales = array_keys(config('app.locales'));

        $categorySelect = $helper
            ->select('category_id')
            ->options($categories)
            ->label('Category')
            ->hidden(is_null($category_id));

        if ($create) {
            $categorySelect->default($category_id)->disabled(!is_null($category_id));
        } else {
            $categorySelect->nullable(is_null($category_id));
        }

        return $form
            ->schema([
                $categorySelect,
                $helper->tabsInput('name', $locales, true),
                $helper->tabsTextarea('description', $locales),
                $helper->radio('preview', [
                    1 => 'Normal',
                    2 => 'Large',
                ])->inline()->default(1),
                $helper->image('images')->multiple(),
            ])->columns(1);
    }
}
