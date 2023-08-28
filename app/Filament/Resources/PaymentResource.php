<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationLabel(): string
    {
        return __('common.payments');
    }

    public static function getPluralLabel(): string
    {
        return __('common.payments');
    }

    public static function getEloquentQuery(): Builder
    {
        return getQueryFilamentStore(parent::getEloquentQuery());
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }
}
