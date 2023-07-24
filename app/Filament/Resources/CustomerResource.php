<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Helpers\FilamentFormHelper;
use App\Models\Customer;
use App\Models\Store;
use Exception;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $query = parent::getEloquentQuery()->with('store');

        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->whereIn('store_id', $user->stores->pluck('id'));
    }

    public static function form(Form $form): Form
    {
        $helper = new FilamentFormHelper;

        return $form
            ->schema([
                $helper->select('store_id')
                    ->label('Store')
                    ->options(self::getStores())
                    ->required(),
                $helper->textInput('name')
                    ->required(),
                $helper->textInput('phone')
                    ->label('Phone number')
                    ->regex('/^\+\d{1,}$/')
                    ->required(),
                $helper->toggle('active')
                    ->default(true)
            ])->columns(1);
    }

    protected static function getStores(): Collection
    {
        $user = Auth::user();
        $query = Store::query();

        if (!$user->hasRole('admin')) {
            $query->whereUserId($user->id);
        }

        return $query->pluck('name', 'id');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->id == $record->store->user_id;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->id == $record->store->user_id;
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.name'),
                Tables\Columns\TextColumn::make('Client phone number')
                    ->formatStateUsing(fn (Model $record) => $record->phone),
                Tables\Columns\IconColumn::make('active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
