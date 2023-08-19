<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\UserResourceForm;
use App\Models\Role;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public array|Collection $stores = [];
    public array|Collection $roles = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $this->stores = Store::pluck('name', 'id');
            $this->roles = Role::pluck('name', 'id');
        } else {
            $this->stores = Store::whereUserId($user->id)->pluck('name', 'id');
        }

        parent::mount();
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);

        return $data;
    }

    public function form(Form $form): Form
    {
        return parent::form($form->schema(UserResourceForm::form(
            $this->stores,
            $this->roles,
        )));
    }
}
