<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    public array|Collection $categories = [];
    public array|Collection $users = [];

    /**
     * @var Store
     */
    public $record;

    public function mount($record): void
    {
        $this->categories = Category::all()->pluck('name.' . config('app.locale'), 'id');

        if (Auth::user()->hasRole('admin')) {
            $this->users = User::where('role_id', 2)->pluck('name', 'id');
        }

        parent::mount($record);
    }

    protected function form(Form $form): Form
    {
        $resourceForm = new StoreResource\StoreResourceForm(
            $this->categories,
            $this->users,
            $this->record->locales,
            Auth::user()->hasRole('admin')
        );

        return $resourceForm->form($form);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['description'] = $this->record->info->description;
        $data['images'] = $this->record->info->images;

        return $data;
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
