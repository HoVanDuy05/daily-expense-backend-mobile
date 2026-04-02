<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->string('avatar')->nullable();          // URL ảnh đại diện
            $table->string('currency')->default('VND');    // Đơn vị tiền tệ
            $table->boolean('notify_push')->default(true); // Bật thông báo
            $table->boolean('biometric')->default(false);  // Khoá sinh trắc
            $table->string('theme')->default('light');     // light | dark
            $table->string('language')->default('vi');     // vi | en
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
