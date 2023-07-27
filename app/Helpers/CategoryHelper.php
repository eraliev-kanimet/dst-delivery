<?php

namespace App\Helpers;

use App\Models\Category;

class CategoryHelper
{
    public function getCategories(array $categories, string $locale): array
    {
        $categories = Category::whereIn('id', $categories)
            ->with(['categories:id,name,category_id'])
            ->get(['id', 'name', 'category_id']);

        $array = [];

        foreach ($categories as $category) {
            $array = array_replace_recursive($array, $this->getCategoryArray($category, $locale));
        }

        return $array;
    }

    protected function getCategoryArray(Category $category, string $locale): array
    {
        $array[$category->id] = $category->name[$locale];

        foreach ($category->categories as $childCategory) {
            $array = array_replace_recursive($array, $this->getCategoryArray($childCategory, $locale));
        }

        return $array;
    }

    public static function new(): CategoryHelper
    {
        return new self;
    }
}
