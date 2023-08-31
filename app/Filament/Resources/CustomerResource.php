<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Helpers\FilamentHelper;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return __('common.customers');
    }

    public static function getPluralLabel(): string
    {
        return __('common.customers');
    }

    public static function getEloquentQuery(): Builder
    {
        return getQueryFilamentQuery(parent::getEloquentQuery());
    }

    public static function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        return $form
            ->schema([
                $helper->select('store_id')
                    ->label(__('common.store'))
                    ->options(getQueryFilamentStore())
                    ->reactive()
                    ->required(),
                $helper->input('name')
                    ->label(__('common.name'))
                    ->required(),
                $helper->input('phone')
                    ->label(__('common.phone_number'))
                    ->regex('/^\+\d{1,}$/')
                    ->hidden(fn(Get $get) => is_null($get('store_id')))
                    ->unique(
                        ignorable: fn(?Model $record) => $record,
                        modifyRuleUsing: fn(Unique $rule, Get $get) => $rule->where('store_id', $get('store_id')))
                    ->required(),
                $helper->toggle('active')
                    ->label(__('common.active'))
                    ->default(true)
            ])->columns(1);
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
