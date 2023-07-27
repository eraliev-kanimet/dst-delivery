<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Helpers\FilamentHelper;
use App\Models\Role;
use App\Models\User;
use Exception;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        $helper = new FilamentHelper();

        return $form->schema([
            $helper->textInput('name')
                ->required(),
            $helper->textInput('email')
                ->required()
                ->email()
                ->unique(ignorable: fn(?Model $record): ?Model => $record),
            $helper->textInput('password')
                ->required(fn(?Model $record): bool => is_null($record))
                ->password()
                ->maxLength(255)
                ->dehydrateStateUsing(static function ($state) use ($form) {
                    if (!empty($state)) {
                        return Hash::make($state);
                    }

                    $user = User::find($form->getColumns());
                    if ($user) {
                        return $user->password;
                    }

                    return $state;
                }),
            $helper->select('role_id')
                ->options(Role::pluck('name', 'id'))
                ->label('Role')
                ->default(2)
        ])->columns(2);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('role.name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
