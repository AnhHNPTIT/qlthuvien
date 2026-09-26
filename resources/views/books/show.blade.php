@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="row g-4">
    <div class="col-md-4 col-xl-3">
        <div class="card p-3"><img src="{{ $book->cover_url }}" class="detail-book-cover" alt="Ảnh bìa {{ $book->title }}"></div>
    </div>
    <div class="col-md-8 col-xl-7">
        <div class="card p-4 h-100">
            <div class="d-flex justify-content-between gap-3">
                <div><span class="badge text-bg-primary mb-2">{{ $book->book_code }}</span><h1 class="h3">{{ $book->title }}</h1></div>
                <span class="badge align-self-start {{ $book->available ? 'text-bg-success' : 'text-bg-danger' }}">{{ $book->available ? 'Còn sách' : 'Hết sách' }}</span>
            </div><hr>
            <dl class="row">
                <dt class="col-sm-3">Tác giả</dt><dd class="col-sm-9">{{ $book->author->name }}</dd>
                <dt class="col-sm-3">Thể loại</dt><dd class="col-sm-9">{{ $book->category->name }}</dd>
                <dt class="col-sm-3">Nhà xuất bản</dt><dd class="col-sm-9">{{ $book->publisher ?: '—' }}</dd>
                <dt class="col-sm-3">Năm xuất bản</dt><dd class="col-sm-9">{{ $book->publication_year ?: '—' }}</dd>
                <dt class="col-sm-3">Số lượng</dt><dd class="col-sm-9">Còn {{ $book->available }} / {{ $book->total_quantity }} bản</dd>
                <dt class="col-sm-3">Mô tả</dt><dd class="col-sm-9">{{ $book->description ?: 'Chưa có mô tả.' }}</dd>
            </dl>
            <div class="mt-auto">
                <a class="btn btn-outline-secondary" href="{{ route('books.index') }}">Quay lại</a>
                @auth
                    @if(auth()->user()->role === 'user' && $book->is_active)
                        <form class="d-inline" method="POST" action="{{ route('borrow.store', $book) }}" onsubmit="return confirm('Xác nhận mượn sách này?')">@csrf<button class="btn btn-primary" @disabled($book->available < 1)>Mượn sách</button></form>
                    @elseif(auth()->user()->isAdmin())
                        <a class="btn btn-primary" href="{{ route('books.edit', $book) }}">Chỉnh sửa</a>
                    @endif
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">Đăng nhập để mượn</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
