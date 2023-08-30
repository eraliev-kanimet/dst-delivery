<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Models\Category;
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
    public array $locales = [];

    public bool $category_disabled = false;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->locales = $this->record->store->locales;

        $locale = config('app.locale');

        $this->categories = Category::whereIn('store_id', $this->record->store_id)
            ->get()
            ->pluck("name.$locale", 'id')
            ->toArray();

        if (!in_array($this->record->category->id, array_keys($this->categories))) {
            $this->category_disabled = true;

            $this->categories = [
                $this->record->category->id => $this->record->category->name[$locale],
            ];
        }

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

        redirect()->route('filament.admin.resources.products.edit', ['record' => $this->record->id]);
    }
}
