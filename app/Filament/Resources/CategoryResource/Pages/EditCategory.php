<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Helpers\FilamentHelper;
use App\Models\Category;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    /**
     * @var Category
     */
    public $record;

    public int $category_id = 0;

    public array|Collection $categories = [];

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $locale = config('app.locale');

        $this->categories = Category::whereCategoryId($this->record->category?->category_id)
            ->whereNot('id', $this->record->id)
            ->get()
            ->pluck("name.$locale", 'id');

        $this->category_id = (int)$this->record->category_id ?? 0;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function form(Form $form): Form
    {
        $helper = new FilamentHelper;

        $locales = array_keys(config('app.locales'));

        return $form
            ->schema([
                $helper->select('category_id')
                    ->options($this->categories)
                    ->label('Category')
                    ->nullable(is_null($this->record->category_id))
                    ->hidden(is_null($this->record->category_id)),
                $helper->tabsTextInput('name', $locales),
                $helper->tabsTextarea('description', $locales),
                $helper->image('images')->multiple()
            ])->columns(1);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->images->values;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['images'])) {
            $this->record->images()->update([
                'values' => $data['images']
            ]);
        }

        return $data;
    }

    public function afterSave(): void
    {
        if ($this->record->category) {
            if ((int)$this->record->category_id != $this->category_id) {
                $category = Category::find($this->record->category_id);

                $category?->updateChildren();
            }
        }

        redirect()->route('filament.resources.categories.edit', ['record' => $this->record->id]);
    }
}
