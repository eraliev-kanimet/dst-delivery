<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\BannerResourceForm;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    public Collection|array $stores = [];

    public function mount(): void
    {
        $this->stores = getEloquentQueryFilament(Store::query())->pluck('name', 'id');

        parent::mount();
    }

    public function form(Form $form): Form
    {
        return parent::form($form->schema(BannerResourceForm::form($this->stores)));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type_value'] = $data['type_' . $data['type']];

        return $data;
    }
}
