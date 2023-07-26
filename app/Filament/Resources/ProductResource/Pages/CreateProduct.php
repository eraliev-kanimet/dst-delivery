<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Filament\Resources\StoreResource;
use App\Models\Image;
use App\Models\Product;
use App\Models\Store;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;

class CreateProduct extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected static ?string $title = 'Create Product';
    protected static ?string $breadcrumb = 'Create Product';

    /**
     * @var Store
     */
    public $record;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [
            'is_available' => true
        ];
    }

    protected function form(Form $form): Form
    {
        return $form->schema(ProductResourceForm::getForm($this->record, false))->columns(1);
    }

    protected function getActions(): array
    {
        return [];
    }

    public function save(bool $shouldRedirect = true): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        $product = Product::create([
            'store_id' => $this->record->id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'attributes' => $data['attributes'],
            'sorted' => $data['sorted'],
            'is_available' => $data['is_available'],
        ]);

        $product->images()->save(new Image(['values' => $data['images']]));

        $this->redirect(route('filament.resources.products.edit', ['record' => $product->id]));
    }
}
