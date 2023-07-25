<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $level = 1;

            $category1 = $this->category($level);

            $level = 2;

            $category2 = $this->category($level, $category1->id);

            $level = 3;

            $this->category($level, $category2->id);
        }
    }

    protected function category(int $level = 1, ?int $category_id = null): Category
    {
        $name = ttt(fake()->sentence(4) . ' ' . $level);

        return Category::updateOrCreate([
            'name' => $name,
            'category_id' => $category_id,
        ], [
            'name' => $name,
            'category_id' => $category_id,
            'description' => ttt(fake()->paragraph)
        ]);
    }
}
