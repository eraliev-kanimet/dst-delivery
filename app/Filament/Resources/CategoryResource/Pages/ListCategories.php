<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ListCategories extends ListRecords
{
    public ?Category $category = null;

    protected static string $resource = CategoryResource::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(): void
    {
        if (!is_null(request()->get('category_id'))) {
            $category = Category::find(request()->get('category_id'));

            if (is_null($category)) {
                abort(404);
            }

            $category->name = truncateStr($category->name[config('app.locale')]);

            $this->category = $category;
        }

        parent::mount();
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['category', 'store'])
            ->where('category_id', $this->category?->id);
    }

    public function getTitle(): string
    {
        return $this->category ? '"' . $this->category->name . '" ' . strtolower(__('common.categories')) : __('common.main_categories');
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                TextColumn::make("name.$locale")
                    ->label(__('common.name')),
                TextColumn::make('store.name')
                    ->label(__('common.store_name')),
            ])
            ->actions([
                ActionTable::make('view')
                    ->label(__('common.view'))
                    ->hidden(fn(Category $record) => $record->category?->category_id)
                    ->url(fn(Category $record) => route(
                        'filament.admin.resources.categories.index', ['category_id' => $record->id]
                    )),
                EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /**
     * @throws Exception
     */
    protected function getHeaderActions(): array
    {
        $label = __('common.create_main_category');
        $actions = [];
        $params = [];

        $category = $this->category;

        if ($category) {
            $label = __('common.create_category_for') . ' ' . $category->name;

            $params['category_id'] = $category->id;

            $label2 = __('common.back_to_the_main_categories');
            $params2 = [];

            if ($category->category) {
                $label2 = __('common.go_back_to_previous_category');

                $params2['category_id'] = $category->category_id;
            }

            $actions[] = Action::make('parent category')
                ->label($label2)
                ->url(route('filament.admin.resources.categories.index', $params2));
        }

        $actions[] = Action::make('create category')
            ->label($label)
            ->url(route('filament.admin.resources.categories.create', $params));

        return $actions;
    }
}
