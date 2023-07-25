<?php

namespace App\Helpers;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class FilamentFormHelper
{
    public function tabs(array $tabs): Tabs
    {
        return Tabs::make('')->tabs($tabs);
    }

    public function tab(string $header, array $schema): Tabs\Tab
    {
        return Tabs\Tab::make($header)->schema($schema);
    }

    public function textInput(string $model): TextInput
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

    public function numericInput(string $model): TextInput
    {
        return TextInput::make($model)
            ->numeric();
    }

    public function image(string $model): FileUpload
    {
        return FileUpload::make($model)->image();
    }

    public function grid(array $schema, array|int $columns = 2): Grid
    {
        return Grid::make($columns)->schema($schema);
    }

    public function markdown(string $model): MarkdownEditor
    {
        return MarkdownEditor::make($model);
    }

    public function radio(string $model, array|callable $options = []): Radio
    {
        return Radio::make($model)->options($options);
    }

    public function checkbox(string $model, array $options = []): CheckboxList
    {
        return CheckboxList::make($model)->options($options);
    }

    public function tabsTextarea(string $model, array $locales): Tabs
    {
        $tabs = [];

        foreach (filterAvailableLocales($locales) as $locale => $name) {
            $tabs[] = $this->tab($name, [
                $this->textarea("$model.$locale")->label(ucfirst($model))
            ]);
        }

        return $this->tabs($tabs);
    }
}
