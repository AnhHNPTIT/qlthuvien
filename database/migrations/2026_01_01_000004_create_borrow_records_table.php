<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['borrowing', 'returned', 'overdue'])->default('borrowing')->index();
            $table->unsignedBigInteger('fine_amount')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'book_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_records');
    }
};
