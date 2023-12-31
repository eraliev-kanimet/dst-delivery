<?php

namespace App\Filament\Resources\StoreResource;

use App\Helpers\FilamentHelper;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Collection;

class StoreResourceForm
{
    protected FilamentHelper $helper;

    public function __construct(
        protected array|Collection $users = [],
        protected array            $locales = [],
        protected bool             $isAdmin = true
    )
    {
        $this->helper = new FilamentHelper;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getSchema());
    }

    protected function getSchema(): array
    {
        $schema = [];

        if ($this->isAdmin) {
            $schema[] = $this->helper->select('user_id')
                ->options($this->users)
                ->label(__('common.store_owner'))
                ->required();
        }

        $schema[] = $this->helper->input('name')
            ->label(__('common.name'));

        if (!$this->isAdmin) {
            $schema[] = $this->helper->tabsTextarea('description', $this->locales, false, __('common.description'));
            $schema[] = $this->helper->image('images')
                ->label(__('common.images'))
                ->multiple();
        }

        $schema[] = $this->helper->grid($this->getLocales(), 1);

        return $schema;
    }

    protected function getLocales(): array
    {
        $locales = config('app.locales');

        $schema = [];

        if ($this->isAdmin) {
            $schema = [
                $this->helper->checkbox('locales', $locales)
                    ->label(__('common.locales'))
                    ->reactive()
                    ->required()
                    ->columns(count($locales)),
                $this->helper->radio(
                    'fallback_locale',
                    fn(Get $get) => filterAvailableLocales($get('locales'))
                )->hidden(fn(Get $get) => !count($get('locales')))
                    ->label(__('common.fallback_locale'))
                    ->required(fn(Get $get) => count($get('locales')))
                    ->inline(),
            ];
        }

        return $schema;
    }
}
