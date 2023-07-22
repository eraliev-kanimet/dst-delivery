<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var User
     */
    public $record;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            if (empty($data['password'])) {
                $data['password'] = $user->password;
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        $user = Auth::user();

        if ($user->role->slug != 'admin' || $user->id == $this->record->id) {
            return [];
        }

        return [
            Actions\DeleteAction::make(),
        ];
    }
}
