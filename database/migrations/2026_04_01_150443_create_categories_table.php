<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();   // emoji icon: 🍜, 🛒, 🎮...
            $table->string('color')->nullable();  // hex color: #FF6B6B
            $table->timestamps();
        });

        // Seed categories mặc định
        DB::table('categories')->insert([
            ['name' => 'Ăn uống',     'icon' => '🍜', 'color' => '#FF6B6B', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Di chuyển',   'icon' => '🚗', 'color' => '#FF9800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mua sắm',     'icon' => '🛒', 'color' => '#2196F3', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Giải trí',    'icon' => '🎮', 'color' => '#9C27B0', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sức khỏe',    'icon' => '💊', 'color' => '#4CAF50', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hóa đơn',     'icon' => '📄', 'color' => '#607D8B', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Khác',        'icon' => '📦', 'color' => '#64748B', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
