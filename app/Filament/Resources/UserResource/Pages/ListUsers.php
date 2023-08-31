<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\Role;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('common.users');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('common.name')),
                TextColumn::make('email')
                    ->label(__('common.email')),
                TextColumn::make('role_id')
                    ->formatStateUsing(fn(User $user) => __('common.roles.' . Role::from($user->role_id)->name))
                    ->label(__('common.role'))
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('common.create_user'))
        ];
    }
}
