<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Image;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public ?int $category_id = null;
    public string $category_name = '';
    public array $categories = [];

    public null|Model|Category $record;

    public function getTitle(): string
    {
        return $this->category_id ? __('common.create_a_child_category') . ' "' . truncateStr($this->category_name) . '"' : __('common.create_main_category');
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
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {
        }

        parent::mount();
    }

    public function form(Form $form): Form
    {
        return parent::form(
            CategoryResource\CategoryResourceForm::form($form, $this->categories, $this->category_id)
        )->columns(1);
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        unset($actions[1]);

        return $actions;
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        $data['category_id'] = $this->category_id;

        $this->record = $this->handleRecordCreation($data);

        $this->record->images()->save(new Image(['values' => $data['images']]));

        $this->redirect(route('filament.admin.resources.categories.edit', ['record' => $this->record->id]));
    }
}
