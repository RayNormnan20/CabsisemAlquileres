<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CalcLoginController extends Controller
{
    public function authenticate(Request $request)
    {
        // Validar si necesita celular o solo contraseña
        $needsPhone = $this->needsPhoneValidation($request);

        if ($needsPhone) {
            // Validar celular y contraseña
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
                // Actualizar el timestamp del último login con celular
                $user = Auth::user();
                $user->update(['last_phone_login' => now()]);

                // Almacenar el celular y fecha en sesión para logins posteriores del día
                if (!str_contains($login, '@')) {
                    $request->session()->put('daily_login_phone', $login);
                    $request->session()->put('daily_login_date', Carbon::today()->toDateString());
                }

                $request->session()->regenerate();

                // Redirigir a la última URL si viene en return_to
                $returnTo = $request->input('return_to');
                if ($returnTo && Str::startsWith($returnTo, '/') && !preg_match('/^https?:/i', $returnTo)) {
                    return redirect($returnTo);
                }

                return redirect()->intended('/');
            }

            return back()
                ->withErrors(['login' => 'Credenciales inválidas'])
                ->withInput(['login' => $login]);
        } else {
            // Solo validar contraseña
            $request->validate([
                'password' => ['required', 'string'],
            ]);

            $password = $request->input('password');

            // Buscar cualquier usuario que tenga esta contraseña válida
            $user = $this->findUserByPassword($password);

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                // Redirigir a la última URL si viene en return_to
                $returnTo = $request->input('return_to');
                if ($returnTo && Str::startsWith($returnTo, '/') && !preg_match('/^https?:/i', $returnTo)) {
                    return redirect($returnTo);
                }

                return redirect()->intended('/');
            }

            return back()
                ->withErrors(['password' => 'Contraseña incorrecta'])
                ->withInput();
        }
    }

    /**
     * Determinar si necesita validación de celular
     */
    private function needsPhoneValidation(Request $request): bool
    {
        // Verificar si hay información de login diario en sesión
        $storedPhone = $request->session()->get('daily_login_phone');
        $storedDate = $request->session()->get('daily_login_date');
        $today = Carbon::today()->toDateString();

        // Si no hay celular almacenado o la fecha no es de hoy, necesita celular
        if (!$storedPhone || !$storedDate || $storedDate !== $today) {
            return true;
        }

        // Si ya hizo login con celular hoy, solo necesita contraseña
        return false;
    }

    /**
     * Obtener usuario de la sesión o por celular almacenado
     */
    private function getUserFromSession(Request $request): ?User
    {
        // Intentar obtener el usuario del celular almacenado en sesión
        $storedPhone = $request->session()->get('daily_login_phone');
        if ($storedPhone) {
            return User::where('celular', $storedPhone)->first();
        }

        // Como fallback, intentar obtener el usuario de la sesión actual
        if (Auth::check()) {
            return Auth::user();
        }

        return null;
    }

    /**
     * Buscar cualquier usuario que tenga la contraseña válida
     */
    private function findUserByPassword(string $password): ?User
    {
        // Obtener todos los usuarios activos
        $users = User::all();

        // Buscar el primer usuario que tenga esta contraseña válida
        foreach ($users as $user) {
            if (Hash::check($password, $user->password)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Mostrar la vista de login
     */
    public function showLoginForm(Request $request)
    {
        $needsPhone = $this->needsPhoneValidation($request);
        $returnTo = $request->query('return_to');

        return view('auth.calc-login', [
            'needsPhone' => $needsPhone,
            'storedPhone' => $request->session()->get('daily_login_phone'),
            'returnTo' => $returnTo,
        ]);
    }
}
