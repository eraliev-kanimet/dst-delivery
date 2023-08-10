<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Exception;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery()->with(['store', 'customer']);
        }

        return parent::getEloquentQuery()
            ->with(['store', 'customer'])
            ->whereRelation('store', 'user_id', $user->id);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('store.name'),
                Tables\Columns\TextColumn::make('customer.phone'),
                Tables\Columns\TextColumn::make('total'),
                Tables\Columns\TextColumn::make('status')
                    ->enum(OrderStatus::getSelect())
                    ->color(function (Model $record) {
                        return match ($record->status) {
                            0 => 'warning',
                            1, 2, 4 => 'secondary',
                            5 => 'primary',
                            7 => 'danger',
                            default => 'success'
                        };
                    }),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
