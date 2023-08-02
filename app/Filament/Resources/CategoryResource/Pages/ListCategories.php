<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ListCategories extends ListRecords
{
    public static ?Category $category = null;

    protected static string $resource = CategoryResource::class;

    /**
     * @return Builder
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getTableQuery(): Builder
    {
        if (!is_null(request()->get('category_id'))) {
            $category = Category::find(request()->get('category_id'));

            if (is_null($category)) {
                abort(404);
            }

            $category->name = truncateStr($category->name[config('app.locale')]);

            self::$category = $category;
        }

        return parent::getTableQuery()
            ->with(['category', 'categories'])
            ->where('category_id', self::$category?->id);
    }

    public function getTitle(): string
    {
        return self::$category ? '"' . self::$category->name .'" categories'  : 'Main categories';
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        $label = 'New main category';
        $actions = [];
        $params = [];

        $category = self::$category;

        if ($category) {
            $label = 'New category for ' . $category->name;

            $params['category_id'] = $category->id;

            $label2 = 'Back to the main categories';
            $params2 = [];

            if ($category->category) {
                $label2 = 'Go back to previous category';

                $params2['category_id'] = $category->category_id;
            }

            $actions[] = Actions\Action::make('parent category')
                ->label($label2)
                ->url(route('filament.resources.categories.index', $params2));
        }

        $actions[] = Actions\Action::make('create category')
            ->label($label)
            ->url(route('filament.resources.categories.create', $params));

        return $actions;
    }
}
