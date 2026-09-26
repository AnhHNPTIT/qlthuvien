@extends('layouts.app') @section('title','Đăng nhập')
@section('content')<div class="row justify-content-center"><div class="col-md-5 col-lg-4"><div class="card p-4"><h1 class="h4 mb-4">Đăng nhập</h1>
<form method="POST" action="{{ route('login.store') }}">@csrf
 <div class="mb-3"><label class="form-label">Email</label><input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
 <div class="mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required></div>
 <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" value="1" id="remember"><label for="remember">Ghi nhớ đăng nhập</label></div>
 <button class="btn btn-primary w-100">Đăng nhập</button></form><p class="mt-3 mb-0 text-center">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
</div></div></div>@endsection
