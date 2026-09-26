<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['book_code', 'title', 'author_id', 'category_id', 'publisher', 'publication_year', 'total_quantity', 'available', 'is_active', 'description', 'cover_image'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowRecords(): HasMany
    {
        return $this->hasMany(BorrowRecord::class);
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->cover_image
            ? asset('storage/'.$this->cover_image)
            : asset('images/default-book-cover.svg');
    }
}
