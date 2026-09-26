<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            return User::create(array_merge($request->validated(), [
                'user_code' => User::nextCode('user'), 'role' => 'user', 'status' => 'active',
            ]));
        });
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('books.index')->with('success', 'Đăng ký tài khoản thành công.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])->onlyInput('email');
        }
        if (! $user->isActive()) {
            return back()->withErrors(['email' => 'Tài khoản đã bị khóa hoặc vô hiệu hóa.'])->onlyInput('email');
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended($user->isAdmin() ? route('statistics.index') : route('books.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('books.index')->with('success', 'Đã đăng xuất.');
    }
}
