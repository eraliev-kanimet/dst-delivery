<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Models\Category;
use App\Models\Product;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @var Product
     */
    public $record;

    public array $categories = [];

    public bool $category_disabled = false;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $locale = config('app.locale');

        $this->categories = Category::whereIn('id', $this->record->store->categories)
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

    protected function form(Form $form): Form
    {
        $productForm = ProductResourceForm::create();

        $productForm->setCategories($this->categories);
        $productForm->setLocales($this->record->store->locales);
        $productForm->setCategoryDisabled($this->category_disabled);

        return $form->schema($productForm->form())->columns(1);
    }
}
