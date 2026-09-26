<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Quản lý thư viện')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f5f7fb}.navbar-brand{font-weight:700}.sidebar .nav-link{color:#334155;border-radius:.5rem}.sidebar .nav-link:hover{background:#e9ecef}.card{border:0;box-shadow:0 2px 12px rgba(15,23,42,.06)}.table>:not(caption)>*>*{vertical-align:middle}.book-cover-wrap{display:block;background:#e9eef5;aspect-ratio:3/4;overflow:hidden}.book-cover{width:100%;height:100%;object-fit:cover;transition:transform .25s}.book-card:hover .book-cover{transform:scale(1.03)}.admin-book-cover{width:54px;height:72px;object-fit:cover;border-radius:.35rem}.form-cover-preview{width:120px;height:160px;object-fit:cover;border-radius:.5rem;border:1px solid #dee2e6}.detail-book-cover{width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:.5rem}</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
 <div class="container-fluid"><a class="navbar-brand" href="{{ route('books.index') }}">📚 Thư viện</a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="nav"><div class="ms-auto d-flex align-items-center gap-2 text-white">
   @auth <span>{{ auth()->user()->name }} ({{ auth()->user()->user_code }})</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-light">Đăng xuất</button></form>
   @else <a class="btn btn-sm btn-outline-light" href="{{ route('login') }}">Đăng nhập</a><a class="btn btn-sm btn-light" href="{{ route('register') }}">Đăng ký</a> @endauth
  </div></div>
 </div>
</nav>
<div class="container-fluid"><div class="row">
 @auth
 <aside class="col-lg-2 bg-white min-vh-100 border-end p-3 sidebar">
  <nav class="nav flex-column gap-1">
   @if(auth()->user()->role === 'user')<a class="nav-link" href="{{ route('books.index') }}">Danh sách sách</a><a class="nav-link" href="{{ route('history.index') }}">Lịch sử mượn</a>@else
   <hr><small class="text-uppercase text-muted px-3">Quản trị</small>
   <a class="nav-link" href="{{ route('statistics.index') }}">Dashboard</a><a class="nav-link" href="{{ route('books.admin.index') }}">Quản lý sách</a>
   <a class="nav-link" href="{{ route('authors.index') }}">Tác giả</a><a class="nav-link" href="{{ route('categories.index') }}">Thể loại</a>
   <a class="nav-link" href="{{ route('users.index') }}">Người dùng</a><a class="nav-link" href="{{ route('borrow.index') }}">Mượn – trả</a>@endif
  </nav>
 </aside>
 @endauth
 <main class="{{ auth()->check() ? 'col-lg-10' : 'col-12' }} p-3 p-lg-4">
  @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
  @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
  @if($errors->any())<div class="alert alert-danger"><strong>Vui lòng kiểm tra lại:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  @yield('content')
 </main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
