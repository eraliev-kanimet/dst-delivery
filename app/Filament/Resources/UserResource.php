<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string
    {
        return __('common.users');
    }

    public static function getPluralLabel(): string
    {
        return __('common.users');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery();
        }

        $stores = $user->stores->pluck('id')->toArray();
        $users = User::whereRoleId(3)->get(['id', 'permissions']);

        $array = [];

        foreach ($users as $user) {
            if (is_null($user->permissions)) {
                continue;
            }

            if (count(array_intersect($user->permissions, $stores))) {
                $array[] = $user->id;
            }
        }

        return parent::getEloquentQuery()->whereIn('id', $array);
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->hasRole('store_owner');
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
