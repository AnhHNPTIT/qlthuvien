@extends('layouts.app')

@section('title', $book->exists ? 'Sửa sách' : 'Thêm sách')

@section('content')
<div class="card p-4">
    <h1 class="h3 mb-2">{{ $book->exists ? 'Chỉnh sửa sách' : 'Thêm sách mới' }}</h1>
    @if($book->exists)<p class="text-muted"></p>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ $book->exists ? route('books.update', $book) : route('books.store') }}">
        @csrf
        @if($book->exists) @method('PUT') @endif
        <div class="row">
            @foreach(['book_code' => 'Mã sách', 'title' => 'Tên sách', 'publisher' => 'Nhà xuất bản', 'publication_year' => 'Năm xuất bản', 'total_quantity' => 'Tổng số lượng'] as $field => $label)
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input class="form-control @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ in_array($field, ['publication_year', 'total_quantity']) ? 'number' : 'text' }}" value="{{ old($field, $book->$field) }}" {{ in_array($field, ['book_code', 'title', 'total_quantity']) ? 'required' : '' }}>
                </div>
            @endforeach
            <div class="col-md-6 mb-3">
                <label class="form-label" for="author_id">Tác giả</label>
                <select class="form-select" id="author_id" name="author_id" required>
                    <option value="">Chọn tác giả</option>
                    @foreach($authors as $author)<option value="{{ $author->id }}" @selected(old('author_id', $book->author_id) == $author->id)>{{ $author->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="category_id">Thể loại</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Chọn thể loại</option>
                    @foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $book->category_id) == $category->id)>{{ $category->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label" for="cover_image">Ảnh bìa</label>
                <input class="form-control @error('cover_image') is-invalid @enderror" id="cover_image" name="cover_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <div class="form-text">JPG, PNG hoặc WEBP; tối đa 2 MB.</div>
            </div>
            <div class="col-md-4 mb-3 text-center">
                <img id="cover-preview" src="{{ $book->cover_url }}" class="form-cover-preview" alt="Xem trước ảnh bìa">
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $book->description) }}</textarea>
            </div>
        </div>
        <button class="btn btn-primary">Lưu</button>
        <a class="btn btn-outline-secondary" href="{{ route('books.admin.index') }}">Hủy</a>
    </form>
</div>
<script>
document.getElementById('cover_image').addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (file) document.getElementById('cover-preview').src = URL.createObjectURL(file);
});
</script>
@endsection
