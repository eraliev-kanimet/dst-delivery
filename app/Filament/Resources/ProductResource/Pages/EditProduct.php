<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Models\Product;
use App\Models\Content;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return __('common.edit_product');
    }

    public string|int|null|Model|Product $record;

    public array $categories = [];
    public array $attr = [];
    public array $locales = [];

    public bool $category_disabled = false;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->locales = $this->record->store->locales;

        $locale = config('app.locale');

        $this->categories = $this->record->store->categories()->get(['name', 'id'])->pluck("name.$locale", 'id')->toArray();

        if (!in_array($this->record->category->id, array_keys($this->categories))) {
            $this->category_disabled = true;

            $this->categories = [
                $this->record->category->id => $this->record->category->name[$locale],
            ];
        }

        $attributes = [];

        foreach ($this->record->store->attr as $item) {
            $attributes[$item->id] = $item->name[$locale] ?? $item->name[$this->record->store->fallback_locale];
        }

        $this->attr = $attributes;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->images->values;

        foreach ($this->record->store->locales as $locale) {
            if ($this->record->{"content_$locale"}) {
                $data['name'][$locale] = $this->record->{"content_$locale"}->name;
                $data['description'][$locale] = $this->record->{"content_$locale"}->description;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['images'])) {
            $this->record->images()->update([
                'values' => $data['images']
            ]);
        }

        return $data;
    }

    public function form(Form $form): Form
    {
        $productForm = ProductResourceForm::create();

        $productForm->setCategories($this->categories);
        $productForm->setLocales($this->locales);
        $productForm->setAttributes($this->attr);
        $productForm->setCategoryDisabled($this->category_disabled);

        return parent::form($form->schema($productForm->form()))
            ->columns(1);
    }

    public function afterSave(): void
    {
        $data = $this->data;

        foreach ($this->record->store->locales as $locale) {
            if ($this->record->{"content_$locale"}) {
                $content = [];

                if ($this->record->{"content_$locale"}->name != $data['name'][$locale]) {
                    $content['name'] = $data['name'][$locale];
                }

                if ($this->record->{"content_$locale"}->description != $data['description'][$locale]) {
                    $content['description'] = $data['description'][$locale];
                }

                if (count($content)) {
                    $this->record->{"content_$locale"}->update($content);
                }
            } else {
                $this->record->{"content_$locale"}()->save(new Content([
                    'locale' => $locale,
                    'name' => $data['name'][$locale],
                    'description' => $data['description'][$locale],
                ]));
            }
        }
    }
}
