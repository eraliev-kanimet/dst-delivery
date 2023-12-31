<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Enums\OrderStatus;
use App\Helpers\FilamentHelper;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Selection;
use App\Service\ProductSelectionService;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public Model|Order $ownerRecord;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('common.products');
    }

    public static function getPluralRecordLabel(): string
    {
        return __('common.products');
    }

    public static function getModelLabel(): ?string
    {
        return __('common.product');
    }

    public function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        return $form->schema([
            $helper->select('product.selection_id')
                ->options(function (?OrderItem $item) {
                    if (!$item->exists) {
                        return ProductSelectionService::new()
                            ->getSelectSelection(
                                $this->ownerRecord->store_id,
                                $this->ownerRecord->store->fallback_locale
                            );
                    }

                    return [];
                })
                ->label(__('common.product'))
                ->required()
                ->reactive()
                ->hidden(fn(?OrderItem $item) => $item->exists),
            $helper->input('price')
                ->label(__('common.price'))
                ->disabled()
                ->visible(fn(?OrderItem $item) => $item->exists),
            $helper->input('quantity')
                ->label(__('common.quantity'))
                ->required()
                ->numeric()
                ->disabled(fn(Get $get) => is_null($get('product.selection_id')))
                ->minValue(1)
                ->maxValue(function (Get $get, ?OrderItem $item) {
                    if ($item->exists) {
                        $selection = Selection::find($item->getProduct('selection_id'));

                        if ($selection) {
                            return $selection->quantity;
                        }
                    } else {
                        $selection_id = $get('product.selection_id');

                        if ($selection_id) {
                            $selection = Selection::find($selection_id);

                            if ($selection) {
                                return $selection->quantity;
                            }
                        }
                    }

                    return 1;
                }),
        ])->columns(1);
    }

    public function table(Table $table): Table
    {
        $service = ProductSelectionService::new();

        return $table
            ->columns([
                TextColumn::make('product')
                    ->state(fn(OrderItem $orderItem) => $service->getOrderItemProduct(
                        $orderItem,
                        $this->ownerRecord->store->fallback_locale
                    ))
                    ->label(__('common.product')),
                TextColumn::make('quantity')
                    ->label(__('common.quantity')),
                TextColumn::make('price')
                    ->label(__('common.price')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('common.add_product'))
                    ->after(fn() => $this->refreshOwnerRecord()),
            ])
            ->actions([
                EditAction::make()->after(fn() => $this->refreshOwnerRecord()),
                DeleteAction::make()->after(fn() => $this->refreshOwnerRecord()),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->after(fn() => $this->refreshOwnerRecord()),
            ])
            ->emptyStateHeading(__('common.order_items_not_found.header'))
            ->emptyStateDescription(__('common.order_items_not_found.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('common.add_product'))
                    ->after(fn() => $this->refreshOwnerRecord()),
            ]);
    }

    protected function refreshOwnerRecord(): void
    {
        if ($this->ownerRecord->status == OrderStatus::inactive->value && $this->ownerRecord->orderItems->count()) {
            $this->ownerRecord->actionTotalCostRecalculation([
                'status' => OrderStatus::pending_payment->value
            ]);
        } else {
            $this->ownerRecord->actionTotalCostRecalculation();
        }

        $this->redirect(route('filament.admin.resources.orders.edit', [
            'record' => $this->ownerRecord->uuid,
        ]));
    }
}
