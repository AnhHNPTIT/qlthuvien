<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowRecord extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'book_id', 'borrow_date', 'due_date', 'return_date', 'status', 'fine_amount', 'is_paid'];

    protected function casts(): array
    {
        return ['borrow_date' => 'date', 'due_date' => 'date', 'return_date' => 'date', 'is_paid' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
