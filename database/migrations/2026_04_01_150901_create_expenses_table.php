<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);             // Số tiền (dương)
            $table->string('title')->nullable();           // Tên chi tiêu (nếu có)
            $table->text('note')->nullable();              // Ghi chú thêm
            $table->string('photo_path')->nullable();      // Đường dẫn ảnh
            $table->date('expense_date');                  // Ngày chi tiêu
            $table->timestamps();

            $table->index(['user_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
