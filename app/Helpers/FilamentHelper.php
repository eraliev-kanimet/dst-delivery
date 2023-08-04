<?php

namespace App\Helpers;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Collection;

class FilamentHelper
{
    public function tabs(array $tabs): Tabs
    {
        return Tabs::make('')->tabs($tabs);
    }

    public function tab(string $header, array $schema): Tabs\Tab
    {
        return Tabs\Tab::make($header)->schema($schema);
    }

    public function input(string $model): TextInput
    {
        return TextInput::make($model);
    }

    public function textarea(string $model): Textarea
    {
        return Textarea::make($model)->notRegex('/.(<script|<style>).+/i');
    }

    public function repeater(string $model, array $schema): Repeater
    {
        return Repeater::make($model)->schema($schema);
    }

    public function fieldset(string $header, array $schema, int $columns = 1): Fieldset
    {
        return Fieldset::make($header)->schema($schema)->columns($columns);
    }

    public function tags(string $model): TagsInput
    {
        return TagsInput::make($model);
    }

    public function toggle(string $model): Toggle
    {
        return Toggle::make($model);
    }

    public function select(string $model): Select
    {
        return Select::make($model);
    }

    public function image(string $model): FileUpload
    {
        return FileUpload::make($model)->image();
    }

    public function grid(array $schema, array|int $columns = 2): Grid
    {
        return Grid::make($columns)->schema($schema);
    }

    public function radio(string $model, array|callable $options = []): Radio
    {
        return Radio::make($model)->options($options);
    }

    public function checkbox(string $model, array|Collection $options = []): CheckboxList
    {
        return CheckboxList::make($model)->options($options);
    }

    public function tabsInput(string $model, array $locales, bool $required = false, ?string $label = null): Tabs
    {
        $tabs = [];

        foreach (filterAvailableLocales($locales) as $locale => $name) {
            $tabs[] = $this->tab($name, [
                $this->input("$model.$locale")
                    ->label($label ?? ucfirst($model))
                    ->required($required)
            ]);
        }

        return $this->tabs($tabs);
    }

    public function tabsTextarea(string $model, array $locales, bool $required = false): Tabs
    {
        $tabs = [];

        foreach (filterAvailableLocales($locales) as $locale => $name) {
            $tabs[] = $this->tab($name, [
                $this->textarea("$model.$locale")
                    ->label(ucfirst($model))
                    ->required($required)
            ]);
        }

        return $this->tabs($tabs);
    }

    public function keyValue(string $model): KeyValue
    {
        return KeyValue::make($model);
    }
}
