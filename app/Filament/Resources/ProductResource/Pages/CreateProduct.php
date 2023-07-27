<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Filament\Resources\StoreResource;
use App\Helpers\CategoryHelper;
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

    public array $categories = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->categories = CategoryHelper::new()
            ->getCategories($this->record->categories, config('app.locale'));

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [
            'is_available' => true
        ];
    }

    protected function form(Form $form): Form
    {
        return $form->schema(
            ProductResourceForm::getForm($this->record->locales, $this->categories, false)
        )->columns(1);
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
            'properties' => $data['properties'],
            'sorted' => $data['sorted'],
            'is_available' => $data['is_available'],
        ]);

        $product->images()->save(new Image(['values' => $data['images']]));

        $this->redirect(route('filament.resources.products.edit', ['record' => $product->id]));
    }
}
