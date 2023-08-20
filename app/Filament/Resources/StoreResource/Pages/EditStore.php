<?php

namespace App\Filament\Resources\StoreResource\Pages;

use App\Filament\Resources\StoreResource;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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
    public string|int|null|Model|Store $record;

    public function mount(int|string $record): void
    {
        $this->categories = Category::all()->pluck('name.' . config('app.locale'), 'id');

        if (Auth::user()->hasRole('admin')) {
            $this->users = User::where('role_id', 2)->pluck('name', 'id');
        }

        parent::mount($record);
    }

    public function form(Form $form): Form
    {
        $isAdmin = Auth::user()->hasRole('admin');

        $resourceForm = new StoreResource\StoreResourceForm(
            $this->categories,
            $this->users,
            $this->record->locales,
            $isAdmin
        );

        return parent::form($resourceForm->form($form))->columns($isAdmin ? 2 : 1);
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
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
        ];
    }
}
