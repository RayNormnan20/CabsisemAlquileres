<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CalcLoginController extends Controller
{
    public function checkLoginExists(Request $request)
    {
        $login = $request->input('login');
        $exists = false;
        if ($login) {
            $exists = User::where('celular', $login)->orWhere('email', $login)->exists();
        }
        return response()->json(['exists' => $exists]);
    }
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

                // Inicializar ruta seleccionada en sesión tras autenticación
                $this->initializeSelectedRouteForUser($request, $user);
                // Limpiar el marcador de tipo de logout tras login exitoso
                $request->session()->forget('last_logout_type');

                // Redirigir a la última URL si viene en return_to
                $returnTo = $request->input('return_to');
                if (!$returnTo) {
                    $returnTo = $request->session()->pull('return_to');
                }
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

                // Inicializar ruta seleccionada en sesión tras autenticación
                $this->initializeSelectedRouteForUser($request, $user);
                // Limpiar el marcador de tipo de logout tras login exitoso
                $request->session()->forget('last_logout_type');

                // Redirigir a la última URL si viene en return_to
                $returnTo = $request->input('return_to');
                if (!$returnTo) {
                    $returnTo = $request->session()->pull('return_to');
                }
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
        // MODO SOLO CONTRASEÑA: si está activo en .env, no requiere celular
        // Mantiene el comportamiento original comentado más abajo.
        if (env('LOGIN_PASSWORD_ONLY')) {
            return false;
        }

        // Estado de login diario y tipo de último logout
        $storedPhone = $request->session()->get('daily_login_phone');
        $storedDate = $request->session()->get('daily_login_date');
        $lastLogoutType = $request->session()->get('last_logout_type'); // 'manual' | 'auto' | null
        $today = Carbon::today()->toDateString();

        // Primera vez del día: requiere celular + contraseña
        if (!$storedPhone || !$storedDate || $storedDate !== $today) {
            return true;
        }

        // Si el último cierre fue manual, exigir celular aunque sea el mismo día
        if ($lastLogoutType === 'manual') {
            return true;
        }

        // Logout automático dentro del mismo día: solo contraseña
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
        if (!$returnTo) {
            $returnTo = $request->session()->get('return_to');
        }

        return view('auth.calc-login', [
            'needsPhone' => $needsPhone,
            'storedPhone' => $request->session()->get('daily_login_phone'),
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * Inicializa la ruta seleccionada en sesión para el usuario autenticado.
     */
    private function initializeSelectedRouteForUser(Request $request, User $user): void
    {
        // Evitar reusar rutas de sesiones anteriores; establecer según el usuario actual
        $ruta = null;

        if (!empty($user->last_selected_ruta_id)) {
            $ruta = Ruta::where('id_ruta', $user->last_selected_ruta_id)->first();
        }

        if (!$ruta) {
            $ruta = $user->rutas()->where('activa', true)->first();
        }

        if (!$ruta) {
            $ruta = Ruta::where('activa', true)->first();
        }

        $rutaId = $ruta ? (int) $ruta->id_ruta : null;
        $rutaName = $ruta ? ($ruta->nombre_completo ?? $ruta->nombre ?? 'Ruta') : 'Ruta';

        $request->session()->put('selected_ruta_id', $rutaId);
        $request->session()->put('selected_ruta_name', $rutaName);

        // Opcional: marcar inicialización para coherencia con EnsureSelectedRoute
        $request->session()->put('selected_route_initialized_for_user', $user->id);
    }
}
