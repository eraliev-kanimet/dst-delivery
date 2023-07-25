<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Helpers\FilamentFormHelper;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateCategory extends CreateRecord
{
    protected ?Category $category = null;

    protected string $locale;

    protected static string $resource = CategoryResource::class;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->locale = config('app.locale');

        try {
            $category_id = request()->get('category_id');

            if (!is_null($category_id)) {
                $category = Category::find($category_id);

                if (is_null($category)) {
                    abort(404);
                }

                $this->category = $category;
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {
        }
    }

    protected function getTitle(): string
    {
        return $this->category ? 'Create a child category "' . truncateStr($this->category->name[$this->locale]) . '"' : 'Create main category';
    }

    protected function form(Form $form): Form
    {
        $helper = new FilamentFormHelper;

        $locales = array_keys(config('app.locales'));

        if ($this->category) {
            $categories = [
                $this->category->id => $this->category->name[$this->locale]
            ];
        } else {
            $categories = Category::whereCategoryId(null)->get()->pluck('name.' . $this->locale, 'id');
        }

        return $form
            ->schema([
                $helper->select('category_id')
                    ->options($categories)
                    ->default($this->category?->id)
                    ->disabled(!is_null($this->category))
                    ->label('Category'),
                $helper->tabsTextInput('name', $locales),
                $helper->tabsTextarea('description', $locales),
            ])->columns(1);
    }
}
