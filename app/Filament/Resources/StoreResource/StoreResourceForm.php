<?php

namespace App\Filament\Resources\StoreResource;

use App\Helpers\FilamentHelper;
use App\Models\Category;
use Closure;
use Filament\Resources\Form;
use Illuminate\Support\Collection;

class StoreResourceForm
{
    protected FilamentHelper $helper;

    public function __construct(
        protected array|Collection $categories = [],
        protected array|Collection $users = [],
        protected array            $locales = [],
        protected bool             $isAdmin = true
    )
    {
        $this->helper = new FilamentHelper;
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->getSchema())->columns($this->isAdmin ? 2 : 1);
    }

    protected function getSchema(): array
    {
        $schema = [];

        if ($this->isAdmin) {
            $schema[] = $this->helper->select('user_id')
                ->options($this->users)
                ->label('Store Owner')
                ->required();
        }

        $schema[] = $this->helper->input('name');

        if (!$this->isAdmin) {
            $schema[] = $this->helper->tabsTextarea('description', $this->locales);
            $schema[] = $this->helper->image('images')->multiple();
        }

        $schema[] = $this->helper->grid($this->getLocalesAndCategories(), 1);

        return $schema;
    }

    protected function getLocalesAndCategories(): array
    {
        $locales = config('app.locales');

        $schema = [];

        if ($this->isAdmin) {
            $schema = [
                $this->helper->checkbox('locales', $locales)
                    ->reactive()
                    ->required()
                    ->columns(count($locales)),
                $this->helper->radio(
                    'fallback_locale',
                    fn(Closure $get) => filterAvailableLocales($get('locales'))
                )->hidden(fn(Closure $get) => !count($get('locales')))
                    ->required(fn(Closure $get) => count($get('locales')))
                    ->inline(),
            ];
        }

        $schema[] = $this->helper->checkbox('categories', $this->categories)
            ->required()
            ->columns()
            ->dehydrateStateUsing(function ($state) {
                $array = [];

                foreach ($state as $value) {
                    $array[] = (int)$value;
                }

                return Category::whereIn('id', $array)
                    ->pluck('id')
                    ->toArray();
            });

        return $schema;
    }
}
