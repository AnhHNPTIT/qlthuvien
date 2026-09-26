<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index', ['categories' => Category::withCount('books')->orderBy('name')->paginate(10)]);
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Đã thêm thể loại.');
    }

    public function edit(Category $category): View
    {
        return view('categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('categories.index')->with('success', 'Đã cập nhật thể loại.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return back()->with('error', 'Không thể xóa thể loại đang được sách tham chiếu.');
        }
        $category->delete();

        return back()->with('success', 'Đã xóa thể loại.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate(['name' => ['required', 'max:255', Rule::unique('categories')->ignore($category)], 'description' => ['nullable', 'string']]);
    }
}
