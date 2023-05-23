<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    function showLoginForm()
    {
        return view('login');
    }

    function showDashboard()
    {
        return view('dashboard');
    }

    function login(Request $request)
    {
        $validatedData = $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        if ($validatedData['login'] == env('ADMIN_LOGIN') && (password_verify($validatedData['password'], env('ADMIN_PASSWORD')))) {
            session()->put('isAuth', true);
            return redirect()->intended(route('show-dashboard'));
        } else {
            session()->put('isAuth', false);
            return redirect()->back()->withErrors(['login' => 'Неверный логин или пароль!']);
        }
    }

    function logout()
    {
        try {
            session()->put('isAuth', false);
            return redirect()->intended(route('show-login-form'));
        } catch (\Throwable $e) {
            return view('errors', ['errors' => $e->getMessage()]);
        }
    }
}
