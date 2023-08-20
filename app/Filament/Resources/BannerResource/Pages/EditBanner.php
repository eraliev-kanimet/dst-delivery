<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\BannerResourceForm;
use App\Models\Store;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    public Collection|array $stores = [];

    public function mount(int|string $record): void
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            $this->stores = Store::whereIn('id', $user->permissions)->pluck('name', 'id');
        } else if ($user->hasRole('store_owner')) {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        } else {
            $this->stores = Store::pluck('name', 'id');
        }

        parent::mount($record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['type_' . $data['type']] = $data['type_value'];

        return $data;
    }

    public function form(Form $form): Form
    {
        return parent::form($form->schema(BannerResourceForm::form($this->stores)));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type_value'] = $data['type_' . $data['type']];

        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
