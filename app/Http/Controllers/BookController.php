<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with(['author', 'category'])->where('is_active', true);
        $query->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
            $term = '%'.$request->string('q')->trim().'%';
            $sub->where('title', 'like', $term)->orWhere('book_code', 'like', $term);
        }))->when($request->filled('author_id'), fn ($q) => $q->where('author_id', $request->integer('author_id')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')));

        return view('books.index', [
            'books' => $query->latest()->paginate(12)->withQueryString(),
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $query = Book::with(['author', 'category'])
            ->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $sub->where('title', 'like', $term)->orWhere('book_code', 'like', $term);
            }))
            ->when($request->filled('author_id'), fn ($q) => $q->where('author_id', $request->integer('author_id')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when(in_array($request->status, ['active', 'hidden'], true), fn ($q) => $q->where('is_active', $request->status === 'active'));

        return view('books.admin-index', [
            'books' => $query->latest()->paginate(15)->withQueryString(),
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Book $book): View
    {
        abort_if(! $book->is_active && ! request()->user()?->isAdmin(), 404);

        return view('books.show', compact('book'));
    }

    public function create(): View
    {
        return view('books.form', ['book' => new Book, 'authors' => Author::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['cover_image']);
        $coverPath = $request->file('cover_image')?->store('book-covers', 'public');
        $data['cover_image'] = $coverPath;
        $data['available'] = $data['total_quantity'];
        $data['is_active'] = true;
        try {
            $book = Book::create($data);
        } catch (Throwable $exception) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }
            throw $exception;
        }

        return redirect()->route('books.admin.index')->with('success', 'Đã thêm sách.');
    }

    public function edit(Book $book): View
    {
        return view('books.form', ['book' => $book, 'authors' => Author::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $newCoverPath = $request->file('cover_image')?->store('book-covers', 'public');
        $oldCoverPath = $book->cover_image;
        try {
            DB::transaction(function () use ($request, $book, $newCoverPath) {
                $book = Book::lockForUpdate()->findOrFail($book->id);
                $borrowed = $book->borrowRecords()->whereIn('status', ['borrowing', 'overdue'])->count();
                $total = $request->integer('total_quantity');
                throw_if($total < $borrowed, ValidationException::withMessages(['total_quantity' => "Không thể giảm dưới {$borrowed} bản đang được mượn."]));
                $data = $request->validated();
                unset($data['cover_image']);
                $data['available'] = $total - $borrowed;
                if ($newCoverPath) {
                    $data['cover_image'] = $newCoverPath;
                }
                $book->update($data);
            });
        } catch (Throwable $exception) {
            if ($newCoverPath) {
                Storage::disk('public')->delete($newCoverPath);
            }
            throw $exception;
        }
        if ($newCoverPath && $oldCoverPath) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return redirect()->route('books.admin.index')->with('success', 'Đã cập nhật sách.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->update(['is_active' => false]);

        return redirect()->route('books.admin.index')->with('success', 'Đã ẩn sách, lịch sử vẫn được bảo toàn.');
    }

    public function toggleStatus(Book $book): RedirectResponse
    {
        $book->update(['is_active' => ! $book->is_active]);

        return back()->with('success', $book->is_active ? 'Đã hiện lại sách.' : 'Đã ẩn sách.');
    }
}
