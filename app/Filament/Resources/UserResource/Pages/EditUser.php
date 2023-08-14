<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\UserResourceForm;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var User
     */
    public $record;

    public array|Collection $stores = [];
    public array|Collection $roles = [];

    public function mount($record): void
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $this->stores = Store::pluck('name', 'id');
            $this->roles = Role::pluck('name', 'id');
        } else {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        }

        parent::mount($record);
    }

    protected function form(Form $form): Form
    {
        return $form->schema(UserResourceForm::form(
            $this->stores,
            $this->roles,
            Auth::user()->hasRole('admin')
        ))->columns(2);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['password'] = null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            $data['password'] = $this->record->password;
        }

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
