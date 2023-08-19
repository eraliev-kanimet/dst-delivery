<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\BannerResourceForm;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    public Collection|array $stores = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            $this->stores = Store::whereIn('id', $user->stores_permission)->pluck('name', 'id');
        } else if ($user->hasRole('store_owner')) {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        } else {
            $this->stores = Store::pluck('name', 'id');
        }

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
