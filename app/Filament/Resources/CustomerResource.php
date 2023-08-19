<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Helpers\FilamentHelper;
use App\Models\Customer;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('store_manager')) {
            return parent::getEloquentQuery()->whereIn('store_id', $user->stores_permission);
        } elseif ($user->hasRole('store_owner')) {
            return parent::getEloquentQuery()->whereRelation('store', 'user_id', $user->id);
        }

        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        return $form
            ->schema([
                $helper->select('store_id')
                    ->label('Store')
                    ->options(self::getStores())
                    ->reactive()
                    ->required(),
                $helper->input('name')
                    ->required(),
                $helper->input('phone')
                    ->label('Phone number')
                    ->regex('/^\+\d{1,}$/')
                    ->hidden(fn(Get $get) => is_null($get('store_id')))
                    ->unique(
                        ignorable: fn(?Model $record) => $record,
                        modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('store_id', $get('store_id')))
                    ->required(),
                $helper->toggle('active')
                    ->default(true)
            ])->columns(1);
    }

    protected static function getStores(): Collection
    {
        $user = Auth::user();
        $query = Store::query();

        if ($user->hasRole('store_manager')) {
            $query->where('id', $user->stores_permission);
        } else if ($user->hasRole('store_owner')) {
            $query->whereUserId($user->id);
        }

        return $query->pluck('name', 'id');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user->hasRole('admin') || $user->hasRole('store_owner');
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
