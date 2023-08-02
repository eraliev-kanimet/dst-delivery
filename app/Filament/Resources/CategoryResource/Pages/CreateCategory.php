<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Image;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public ?int $category_id = null;
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
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {}

        parent::mount();
    }

    protected function form(Form $form): Form
    {
        return CategoryResource\CategoryResourceForm::form($form, $this->categories, $this->category_id);
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
