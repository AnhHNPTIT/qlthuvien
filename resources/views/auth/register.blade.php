@extends('layouts.app') @section('title','Đăng ký')
@section('content')<div class="row justify-content-center"><div class="col-md-7"><div class="card p-4"><h1 class="h4 mb-4">Đăng ký độc giả</h1>
<form method="POST" action="{{ route('register.store') }}">@csrf <div class="row">
 @foreach(['name'=>'Họ và tên','email'=>'Email','phone'=>'Số điện thoại','address'=>'Địa chỉ'] as $field=>$label)<div class="col-md-6 mb-3"><label class="form-label">{{ $label }}</label><input class="form-control @error($field) is-invalid @enderror" type="{{ $field==='email'?'email':'text' }}" name="{{ $field }}" value="{{ old($field) }}" {{ in_array($field,['name','email'])?'required':'' }}>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>@endforeach
 <div class="col-md-6 mb-3"><label class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" required></div><div class="col-md-6 mb-3"><label class="form-label">Nhập lại mật khẩu</label><input class="form-control" type="password" name="password_confirmation" required></div>
 </div><button class="btn btn-primary">Tạo tài khoản</button> <a class="btn btn-link" href="{{ route('login') }}">Đã có tài khoản</a></form>
</div></div></div>@endsection
