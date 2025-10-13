<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalcLoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        // Determinar el campo correcto según el formato
        // Si contiene "@" asumimos email, si no, usamos "celular"
        $credentials = [
            (str_contains($login, '@') ? 'email' : 'celular') => $login,
            'password' => $password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()
            ->withErrors(['login' => 'Credenciales inválidas'])
            ->withInput(['login' => $login]);
    }
}