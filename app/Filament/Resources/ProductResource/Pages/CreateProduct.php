<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProductResource\ProductResourceForm;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductContent;
use App\Models\Store;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /**
     * @var Product
     */
    public $record;

    public int $store_id = 0;
    public array $locales = [];
    public array $categories = [];

    public function mount(): void
    {
        try {
            $store_id = request()->get('store_id', 'store');

            $store = Store::find($store_id);

            if (is_null($store)) {
                abort(404);
            } else {
                $this->store_id = $store->id;

                $locale = config('app.locale');

                $this->categories = Category::whereIn('id', $store->categories)
                    ->get()
                    ->pluck("name.$locale", 'id')
                    ->toArray();

                $this->locales = $store->locales;
            }
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface) {}

        parent::mount();
    }

    protected function form(Form $form): Form
    {
        $productForm = ProductResourceForm::create();

        $productForm->setEdit(false);
        $productForm->setCategories($this->categories);
        $productForm->setLocales($this->locales);

        return $form->schema($productForm->form())->columns(1);
    }

    public function create(bool $another = false): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        $data['store_id'] = $this->store_id;

        $this->record = $this->handleRecordCreation($data);

        $this->record->images()->save(new Image(['values' => $data['images']]));

        foreach ($this->locales as $locale) {
            ProductContent::create([
                'product_id' => $this->record->id,
                'locale' => $locale,
                'name' => $data['name'][$locale],
                'description' => $data['description'][$locale],
            ]);
        }

        $this->getCreatedNotification()?->send();

        $this->redirect($this->getRedirectUrl());
    }
}
