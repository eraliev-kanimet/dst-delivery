<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    public string|int|null|Model|Category $record;

    public int $category_id = 0;

    public array|Collection $categories = [];

    public function mount(int|string $record): void
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

    public function form(Form $form): Form
    {
        return parent::form(
            CategoryResource\CategoryResourceForm::form(
                $form,
                $this->categories,
                $this->record->category_id,
                false
            )
        )->columns(1);
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

        redirect()->route('filament.admin.resources.categories.edit', ['record' => $this->record->id]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
        ];
    }
}
