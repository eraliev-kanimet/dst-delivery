<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Helpers\FilamentHelper;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected int $category_id = 0;
    protected string $category_name = '';
    protected array|Collection $categories = [];

    protected function getTitle(): string
    {
        return $this->category_id ? 'Create a child category "' . truncateStr($this->category_name) . '"' : 'Create main category';
    }

    public function mount(): void
    {
        $locale = config('app.locale');

        try {
            $category_id = request()->get('category_id');

            if (!is_null($category_id)) {
                $category = Category::find($category_id);

                if (is_null($category)) {
                    abort(404);
                }

                $name = $category->name[$locale];

                $this->categories = [$category_id => $name];

                $this->category_id = $category_id;
                $this->category_name = $name;
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {}

        if (!$this->category_id) {
            $this->categories = Category::whereCategoryId(null)->get()->pluck("name.$locale", 'id');
        }

        parent::mount();
    }

    protected function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        $locales = array_keys(config('app.locales'));

        return $form
            ->schema([
                $helper->select('category_id')
                    ->options($this->categories)
                    ->default($this->category_id)
                    ->disabled($this->category_id)
                    ->label('Category'),
                $helper->tabsTextInput('name', $locales, true),
                $helper->tabsTextarea('description', $locales),
            ])->columns(1);
    }
}
