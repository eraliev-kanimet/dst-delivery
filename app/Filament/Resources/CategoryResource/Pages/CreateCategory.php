<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Helpers\FilamentHelper;
use App\Models\Category;
use App\Models\Image;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public int $category_id = 0;
    public string $category_name = '';
    public array $categories = [];

    /**
     * @var Category
     */
    public $record;

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
            } else {
                $this->category_id = 0;
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {}

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
                    ->visible($this->category_id)
                    ->default($this->category_id)
                    ->disabled($this->category_id)
                    ->label('Category'),
                $helper->tabsTextInput('name', $locales, true),
                $helper->tabsTextarea('description', $locales),
                $helper->image('images')->multiple()
            ])->columns(1);
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        $data = $this->mutateFormDataBeforeCreate($data);

        $this->record = $this->handleRecordCreation($data);

        $this->record->images()->save(new Image(['values' => $data['images']]));

        $this->redirect(route('filament.resources.categories.edit', ['record' => $this->record->id]));
    }
}
