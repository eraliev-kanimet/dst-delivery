<?php

namespace App\Filament\Resources\UserResource;

use App\Helpers\FilamentHelper;
use App\Models\Store;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserResourceForm
{
    public static function form(
        array|Collection $stores,
        array|Collection $roles,
        bool $isAdmin = true
    ): array
    {
        $roles_count = count($roles);

        $helper = new FilamentHelper();

        $schema = [
            $helper->input('name')
                ->label(__('common.name'))
                ->disabled(!$isAdmin)
                ->required(),
            $helper->input('email')
                ->label(__('common.email'))
                ->disabled(!$isAdmin)
                ->required()
                ->email()
                ->unique(ignorable: fn(?Model $record): ?Model => $record),
        ];

        if ($isAdmin) {
            $schema[] = $helper->input('password')
                ->label(__('common.password'))
                ->required(fn(?Model $record): bool => is_null($record))
                ->password()
                ->maxLength(255)
                ->columnSpan($roles_count ? 1 : 2);
        }

        if ($roles_count) {
            $schema[] = $helper->select('role_id')
                ->label(__('common.role'))
                ->options($roles)
                ->reactive()
                ->default(2);
        } else {
            $schema[] = $helper->hidden('role_id')->default(3);
        }

        $schema[] = $helper->checkbox('permissions', $stores)
            ->label(__('common.permissions'))
            ->required()
            ->columns()
            ->visible(fn(Get $get) => $get('role_id') == 3)
            ->columnSpan(2)
            ->dehydrateStateUsing(function ($state) {
                $array = [];

                foreach ($state as $value) {
                    $array[] = (int)$value;
                }

                return Store::whereIn('id', $array)
                    ->pluck('id')
                    ->toArray();
            });

        return $schema;
    }
}
