<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'book_code' => ['required', 'max:30', Rule::unique('books')->ignore($book)],
            'title' => ['required', 'max:255'],
            'author_id' => ['required', 'exists:authors,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'publisher' => ['nullable', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(date('Y') + 1)],
            'total_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_code.unique' => 'Mã sách đã tồn tại.',
            'total_quantity.min' => 'Số lượng sách không được âm.',
            'cover_image.image' => 'Ảnh bìa phải là một tệp hình ảnh.',
            'cover_image.mimes' => 'Ảnh bìa chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
            'cover_image.max' => 'Ảnh bìa không được lớn hơn 2 MB.',
        ];
    }
}
