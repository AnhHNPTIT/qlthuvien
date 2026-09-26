<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        return view('authors.index', ['authors' => Author::withCount('books')->orderBy('name')->paginate(10)]);
    }

    public function create(): View
    {
        return view('authors.form', ['author' => new Author]);
    }

    public function store(Request $request): RedirectResponse
    {
        Author::create($request->validate(['name' => ['required', 'max:255'], 'biography' => ['nullable', 'string']]));

        return redirect()->route('authors.index')->with('success', 'Đã thêm tác giả.');
    }

    public function edit(Author $author): View
    {
        return view('authors.form', compact('author'));
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $author->update($request->validate(['name' => ['required', 'max:255'], 'biography' => ['nullable', 'string']]));

        return redirect()->route('authors.index')->with('success', 'Đã cập nhật tác giả.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->books()->exists()) {
            return back()->with('error', 'Không thể xóa tác giả đang được sách tham chiếu.');
        }
        $author->delete();

        return back()->with('success', 'Đã xóa tác giả.');
    }
}
