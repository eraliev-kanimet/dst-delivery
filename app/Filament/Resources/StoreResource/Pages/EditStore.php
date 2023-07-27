<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Helpers\FilamentHelper;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected User $user;

    /**
     * @var Store
     */
    public $record;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->user = Auth::user();
    }

    protected function form(Form $form): Form
    {
        return $this->user->hasRole('admin') ? StoreResource::form($form) : $this->getFormForm($form);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['description'] = $this->record->info->description;
        $data['images'] = $this->record->info->images;

        return $data;
    }

    protected function getFormForm(Form $form): Form
    {
        $helper = new FilamentHelper;
        $locale = config('app.locale');
        $categories = Category::whereNull('category_id')->get()->pluck("name.$locale", 'id');

        return $form->schema([
            $helper->textInput('name'),
            $helper->tabsTextarea('description', $this->record->locales),
            $helper->image('images')
                ->multiple(),
            $helper->checkbox('categories', $categories)
                ->required()
                ->columns()
        ])->columns(1);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['description'])) {
            $this->record->info()->update([
                'description' => $data['description'],
                'images' => $data['images'],
            ]);
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make()
        ];
    }
}
