<?php

namespace App\Filament\Resources\CategoryResource;

use App\Helpers\FilamentHelper;
use Filament\Forms\Form;
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
            ->label(__('common.category'))
            ->hidden(is_null($category_id));

        if ($create) {
            $categorySelect->default($category_id)->disabled(!is_null($category_id));
        } else {
            $categorySelect->nullable(is_null($category_id));
        }

        return $form
            ->schema([
                $categorySelect,
                $helper->tabsInput('name', $locales, true, __('common.name')),
                $helper->tabsTextarea('description', $locales, false, __('common.description')),
                $helper->radio('preview', [
                    2 => 'Normal',
                    1 => 'Large',
                ])
                    ->inline()
                    ->default(2)
                    ->label(__('common.preview')),
                $helper->image('images')
                    ->multiple()
                    ->imageEditor()
                    ->label(__('common.images')),
            ]);
    }
}
