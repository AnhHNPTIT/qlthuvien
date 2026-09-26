@extends('layouts.app')

@section('title', 'Quản lý sách')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Quản lý sách</h1>
        <p class="text-muted mb-0">Quản lý thông tin và trạng thái hiển thị của sách.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('books.create') }}">+ Thêm sách</a>
</div>

<form class="card p-3 mb-3" method="GET" action="{{ route('books.admin.index') }}">
    <div class="row g-2">
        <div class="col-lg-3">
            <label class="form-label" for="q">Từ khóa</label>
            <input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Tên sách hoặc mã sách">
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="author_id">Tác giả</label>
            <select class="form-select" id="author_id" name="author_id">
                <option value="">Tất cả tác giả</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}" @selected(request('author_id') == $author->id)>{{ $author->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="category_id">Thể loại</label>
            <select class="form-select" id="category_id" name="category_id">
                <option value="">Tất cả thể loại</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <label class="form-label" for="status">Trạng thái</label>
            <select class="form-select" id="status" name="status">
                <option value="">Tất cả trạng thái</option>
                <option value="active" @selected(request('status') === 'active')>Đang hiển thị</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Đã ẩn</option>
            </select>
        </div>
        <div class="col-lg-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary flex-fill" type="submit">Tìm kiếm</button>
            <a class="btn btn-outline-secondary flex-fill" href="{{ route('books.admin.index') }}">Bỏ lọc</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Ảnh bìa</th><th>Mã sách</th><th>Tên sách</th><th>Tác giả</th><th>Thể loại</th><th>Còn/Tổng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
            @forelse($books as $book)
                <tr>
                    <td><img src="{{ $book->cover_url }}" class="admin-book-cover" alt="Ảnh bìa {{ $book->title }}"></td>
                    <td><strong>{{ $book->book_code }}</strong></td>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->author->name }}</td>
                    <td>{{ $book->category->name }}</td>
                    <td>{{ $book->available }}/{{ $book->total_quantity }}</td>
                    <td><span class="badge {{ $book->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $book->is_active ? 'Đang hiển thị' : 'Đã ẩn' }}</span></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('books.edit', $book) }}">Sửa</a>
                        <form class="d-inline" method="POST" action="{{ route('books.toggle-status', $book) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $book->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $book->is_active ? 'Ẩn sách' : 'Hiện lại sách' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-5">Không tìm thấy sách phù hợp.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $books->links() }}</div>
@endsection
