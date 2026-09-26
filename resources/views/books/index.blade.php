@extends('layouts.app')

@section('title', 'Kho sách')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Khám phá kho sách</h1>
        <p class="text-muted mb-0">Tìm cuốn sách phù hợp và mượn trực tuyến.</p>
    </div>
    @auth
        @if(auth()->user()->isAdmin())
            <a class="btn btn-primary" href="{{ route('books.admin.index') }}">Quản lý sách</a>
        @endif
    @endauth
</div>

<form class="card p-3 mb-4" method="GET" action="{{ route('books.index') }}">
    <div class="row g-2">
        <div class="col-lg-4">
            <label class="form-label" for="q">Từ khóa</label>
            <input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Tên sách hoặc mã sách">
        </div>
        <div class="col-md-4 col-lg-3">
            <label class="form-label" for="author_id">Tác giả</label>
            <select class="form-select" id="author_id" name="author_id">
                <option value="">Tất cả tác giả</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}" @selected(request('author_id') == $author->id)>{{ $author->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-3">
            <label class="form-label" for="category_id">Thể loại</label>
            <select class="form-select" id="category_id" name="category_id">
                <option value="">Tất cả thể loại</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2 d-flex align-items-end gap-2">
            <button class="btn btn-primary flex-fill" type="submit">Tìm kiếm</button>
            <a class="btn btn-outline-secondary flex-fill" href="{{ route('books.index') }}">Bỏ lọc</a>
        </div>
    </div>
</form>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
    @forelse($books as $book)
        <div class="col">
            <article class="card h-100 overflow-hidden book-card">
                <a href="{{ route('books.show', $book) }}" class="book-cover-wrap">
                    <img src="{{ $book->cover_url }}" class="book-cover" alt="Ảnh bìa {{ $book->title }}">
                </a>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="badge text-bg-primary">{{ $book->book_code }}</span>
                        <span class="badge {{ $book->available > 0 ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $book->available > 0 ? 'Còn '.$book->available.' bản' : 'Hết sách' }}
                        </span>
                    </div>
                    <h2 class="h5 card-title">{{ $book->title }}</h2>
                    <p class="small text-muted mb-1">{{ $book->author->name }}</p>
                    <p class="small mb-3">{{ $book->category->name }}</p>
                    <a class="btn btn-outline-primary mt-auto" href="{{ route('books.show', $book) }}">Xem chi tiết</a>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12 w-100">
            <div class="card w-100 p-5 text-center text-muted">Không tìm thấy sách phù hợp.</div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $books->links() }}</div>
@endsection
