<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Thực phẩm', 'icon' => 'food', 'color' => '#FF5722'],
            ['name' => 'Di chuyển', 'icon' => 'transport', 'color' => '#2196F3'],
            ['name' => 'Mua sắm', 'icon' => 'shopping', 'color' => '#9C27B0'],
            ['name' => 'Giải trí', 'icon' => 'entertainment', 'color' => '#FF9800'],
            ['name' => 'Hóa đơn', 'icon' => 'bills', 'color' => '#F44336'],
            ['name' => 'Sức khỏe', 'icon' => 'health', 'color' => '#4CAF50'],
            ['name' => 'Khác', 'icon' => 'other', 'color' => '#757575'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
