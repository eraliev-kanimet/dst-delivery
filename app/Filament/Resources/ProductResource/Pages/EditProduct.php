<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\ProductResourceForm;
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
        return $form->schema(ProductResourceForm::getForm($this->record->store))->columns(1);
    }
}
