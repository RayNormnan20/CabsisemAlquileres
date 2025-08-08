<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use App\Models\Oficina;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RutaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            $rutas = Ruta::with(['oficina', 'usuario'])->get();
        } elseif ($user->hasAnyRole(['Administrador', 'Encargado de oficina'])) {
            $rutas = Ruta::where('id_oficina', $user->id_oficina)
                        ->with(['oficina', 'usuario'])
                        ->get();
        } else {
            $rutas = $user->rutas()->with(['oficina', 'usuario'])->get();
        }

        return view('rutas.index', compact('rutas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('manage-rutas');

        $oficinas = Oficina::all();
        $usuarios = User::role(['Cobrador'])->get();

        return view('rutas.create', compact('oficinas', 'usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-rutas');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:ruta,codigo',
            'id_oficina' => 'required|exists:oficina,id_oficina',
            'id_usuario' => 'required|exists:users,id',
            // ... otros campos según tu formulario
        ]);

        $ruta = Ruta::create($validated);

        return redirect()->route('rutas.show', $ruta)->with('success', 'Ruta creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ruta $ruta)
    {
        $this->authorize('access-ruta', $ruta);

        $ruta->load(['clientes', 'creditos', 'usuario', 'oficina']);

        return view('rutas.show', compact('ruta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ruta $ruta)
    {
        $this->authorize('manage-rutas');
        $this->authorize('update', $ruta);

        $oficinas = Oficina::all();
        $usuarios = User::role(['Cobrador'])->get();

        return view('rutas.edit', compact('ruta', 'oficinas', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ruta $ruta)
    {
        $this->authorize('manage-rutas');
        $this->authorize('update', $ruta);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:ruta,codigo,'.$ruta->id_ruta.',id_ruta',
            'id_oficina' => 'required|exists:oficina,id_oficina',
            'id_usuario' => 'required|exists:users,id',
            // ... otros campos
        ]);

        $ruta->update($validated);

        return redirect()->route('rutas.show', $ruta)->with('success', 'Ruta actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruta $ruta)
    {
        $this->authorize('manage-rutas');
        $this->authorize('delete', $ruta);

        $ruta->delete();

        return redirect()->route('rutas.index')->with('success', 'Ruta eliminada correctamente');
    }

    /**
     * Métodos adicionales para funcionalidades específicas
     */
    public function clientes(Ruta $ruta)
    {
        $this->authorize('access-ruta', $ruta);

        $clientes = $ruta->clientes()->withCount('creditos')->get();

        return view('rutas.clientes', compact('ruta', 'clientes'));
    }

    public function storePayment(Request $request, Ruta $ruta)
    {
        $this->authorize('collect-payments');
        $this->authorize('access-ruta', $ruta);

        // Lógica para registrar abonos
    }

    public function reportes(Ruta $ruta)
    {
        $this->authorize('view-ruta-reports');
        $this->authorize('access-ruta', $ruta);


    }
}