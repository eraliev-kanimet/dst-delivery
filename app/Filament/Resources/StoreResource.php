<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Helpers\FilamentFormHelper;
use App\Models\Store;
use App\Models\User;
use Closure;
use Exception;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->role->slug != 'admin') {
            return parent::getEloquentQuery()->where('user_id', $user->id);
        }

        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        $helper = new FilamentFormHelper;
        $locales = config('app.locales');

        return $form
            ->schema([
                $helper->select('user_id')
                    ->options(User::all()->pluck('name', 'id'))
                    ->label('Store Owner')
                    ->required(),
                $helper->textInput('name'),
                $helper->grid([
                    $helper->checkbox('locales', $locales)
                        ->reactive()
                        ->required()
                        ->columns(count($locales)),
                    $helper->radio(
                        'fallback_locale',
                        fn(Closure $get) => filterAvailableLocales($get('locales'))
                    )->hidden(fn(Closure $get) => !count($get('locales')))
                        ->required(fn(Closure $get) => count($get('locales')))
                        ->inline()
                ], 1),
            ])->columns(2);
    }

    public static function canCreate(): bool
    {
        return Auth::user()->role->slug == 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user->role->slug == 'admin' || $user->id == $record->user_id;
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('name'),
        ];

        if (Auth::user()->role->slug == 'admin') {
            $columns[] = Tables\Columns\TextColumn::make('uuid')
                ->copyable();
            $columns[] = Tables\Columns\TextColumn::make('user.name');
        }

        $columns[] = Tables\Columns\TextColumn::make('updated_at');

        return $table
            ->columns($columns)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}