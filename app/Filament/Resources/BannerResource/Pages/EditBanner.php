<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Filament\Resources\BannerResource\BannerResourceForm;
use App\Models\Store;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    public Collection|array $stores = [];

    public function mount($record): void
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $this->stores = Store::pluck('name', 'id');
        } else {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        }

        parent::mount($record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['type_' . $data['type']] = $data['type_value'];

        return $data;
    }

    protected function form(Form $form): Form
    {
        return $form->schema(BannerResourceForm::form($this->stores));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type_value'] = $data['type_' . $data['type']];

        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
