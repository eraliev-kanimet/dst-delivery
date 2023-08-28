<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Exception;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('order');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $statuses = PaymentStatus::getSelect();

        return $table
            ->columns([
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->label(__('common.transaction_id')),
                TextColumn::make('amount')
                    ->sortable()
                    ->label(__('common.amount')),
                TextColumn::make('status')
                    ->label(__('common.status'))
                    ->formatStateUsing(fn(Payment $payment) => $statuses[$payment->status])
                    ->color(function (Payment $record) {
                        return match ($record->status) {
                            1 => 'primary',
                            2 => 'danger',
                            default => 'success'
                        };
                    }),
                TextColumn::make('provider')
                    ->label(__('common.provider'))
                    ->formatStateUsing(function (Payment $payment) {
                        return __('common.' . $payment->provider);
                    }),
                TextColumn::make('order.store.name')
                    ->label(__('common.store_name')),
                TextColumn::make('updated_at')
                    ->sortable()
                    ->label(__('common.updated_at'))
                    ->date('Y-m-d'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('common.status'))
                    ->options(PaymentStatus::getSelect()),
            ])
            ->actions([
                Action::make('payment.order')
                    ->button()
                    ->label(__('common.order'))
                    ->url(function (Payment $payment) {
                        return route('filament.admin.resources.orders.edit', [
                            'record' => $payment->order->uuid
                        ]);
                    })
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
