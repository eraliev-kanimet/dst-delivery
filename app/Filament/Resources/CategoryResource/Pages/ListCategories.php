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
            ->with(['category'])
            ->where('category_id', $this->category?->id);
    }

    public function getTitle(): string
    {
        return $this->category ? '"' . $this->category->name . '" categories' : 'Main categories';
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $locale = config('app.locale');

        return $table
            ->columns([
                TextColumn::make("name.$locale"),
            ])
            ->actions([
                ActionTable::make('view')
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
        $label = 'New main category';
        $actions = [];
        $params = [];

        $category = $this->category;

        if ($category) {
            $label = 'New category for ' . $category->name;

            $params['category_id'] = $category->id;

            $label2 = 'Back to the main categories';
            $params2 = [];

            if ($category->category) {
                $label2 = 'Go back to previous category';

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
