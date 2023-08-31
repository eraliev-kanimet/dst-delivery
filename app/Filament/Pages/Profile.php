<?php


namespace App\Filament\Pages;

use App\Helpers\FilamentHelper;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @property Form $form
 */
class Profile extends Page
{
    use InteractsWithFormActions;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static string $view = 'filament.pages.profile';

    public static function getNavigationLabel(): string
    {
        return __('common.profile');
    }

    public function getTitle(): string
    {
        return __('common.profile');
    }

    public mixed $previousUrl;
    public mixed $name;
    public mixed $email;
    public mixed $password = '';
    public mixed $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;

        $this->previousUrl = url()->previous();
    }

    protected function getFormSchema(): array
    {
        $helper = new FilamentHelper();

        return [
            $helper->grid([
                $helper->input('name')
                    ->label(__('common.name'))
                    ->required(),
                $helper->input('email')
                    ->label(__('common.email'))
                    ->required()
                    ->email()
                    ->unique(ignorable: fn(?User $record): ?User => $record),
                $helper->input('password')
                    ->label(__('common.password'))
                    ->password()
                    ->minLength(8)
                    ->confirmed(),
                $helper->input('password_confirmation')
                    ->label(__('common.password_confirmation'))
                    ->password(),
            ])
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
                ->url($this->previousUrl ?? '/admin')
                ->color('gray')
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data = array_filter($data, fn ($value) => $value != null);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = Auth::user();

        $user->update($data);

        if (isset($data['password'])) {
            $this->redirect('/admin/login');
        }
    }
}
