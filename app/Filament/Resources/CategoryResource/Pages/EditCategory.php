<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Helpers\FilamentFormHelper;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    /**
     * @var Category
     */
    public $record;

    protected function form(Form $form): Form
    {
        $helper = new FilamentFormHelper;
        $locale = config('app.locale');
        $locales = array_keys(config('app.locales'));
        $categories = Category::whereCategoryId($this->record->category?->category_id)
            ->whereNot('id', $this->record->id)
            ->get()
            ->pluck("name.$locale", 'id');

        return $form
            ->schema([
                $helper->select('category_id')
                    ->options($categories)
                    ->label('Category')
                    ->required(!is_null($this->record->category_id)),
                $helper->tabsTextInput('name', $locales),
                $helper->tabsTextarea('description', $locales),
                $helper->image('images')->multiple()
            ])->columns(1);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->_images->values;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['images'])) {
            $this->record->_images()->update([
                'values' => $data['images']
            ]);
        }

        return $data;
    }
}
