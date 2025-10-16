@php
    $groups = [];
    // Mapeo canónico para unificar singular/plural y variantes comunes
    $canonicalMap = [
        'usuarios' => 'Usuario',
        'usuario' => 'Usuario',
        'rutas' => 'Ruta',
        'ruta' => 'Ruta',
        'roles' => 'Rol',
        'rols' => 'Rol',
        'rol' => 'Rol',
        'movimientos' => 'Movimiento',
        'movimiento' => 'Movimiento',
        'oficinas' => 'Oficina',
        'oficina' => 'Oficina',
        'clientes' => 'Cliente',
        'cliente' => 'Cliente',
        // Concepto(s)
        'conceptos' => 'Concepto',
        'concepto' => 'Concepto',
        // Edificio / Edificios
        'edificio' => 'Edificio',
        'edificios' => 'Edificio',
        // Estado Departamento -> Departamento
        'estado departamento' => 'Departamento',
        'estados departamento' => 'Departamento',
        'estado de departamento' => 'Departamento',
        'estados de departamento' => 'Departamento',
        'estado departamentos' => 'Departamento',
        'estados departamentos' => 'Departamento',
        // Yape Cliente / Yape Clientes
        'yape cliente' => 'Yape Cliente',
        'yape clientes' => 'Yape Cliente',
        'yapeclientes' => 'Yape Cliente',
        'yapecliente' => 'Yape Cliente',
        // Día no laborable(s)
        'dia no laborable' => 'Día No Laborable',
        'dia no laborables' => 'Día No Laborable',
        // Alquiler / Alquileres (y posibles typos)
        'alquileres' => 'Alquiler',
        'alquilers' => 'Alquiler',
        'alquiler' => 'Alquiler',
        'departamentos' => 'Departamento',
        'departamento' => 'Departamento',
        'creditos' => 'Créditos',
        'créditos' => 'Créditos',
        'liquidaciones' => 'Liquidaciones',
        'liquidacion' => 'Liquidaciones',
        'segundo recorridos' => 'Segundo Recorrido',
        'segundo recorrido' => 'Segundo Recorrido',
    ];

    foreach ($permissions as $perm) {
        $name = $perm->name;
        // Detectar el "módulo" quitando el verbo inicial
        $subject = preg_replace('/^(Crear|Listar|Ver|Eliminar|Actualizar|Manage|Import|View|List)\s+/iu', '', $name);
        $subject = trim($subject);

        // Normalizaciones específicas
        $subject = str_ireplace(['Cliente Alquileres', 'Cliente Alquilers', 'Cliente Alquiler'], 'Cliente Alquiler', $subject);
        // Homogeneizar Alquileres/Alquilers a Alquiler
        $subject = preg_replace('/\bAlquileres\b/iu', 'Alquiler', $subject);
        $subject = preg_replace('/\bAlquilers\b/iu', 'Alquiler', $subject);
        // Homogeneizar YapeCliente(s)
        $subject = preg_replace('/\bYapeClientes\b/iu', 'Yape Clientes', $subject);
        $subject = preg_replace('/\bYapeCliente\b/iu', 'Yape Cliente', $subject);
        $subject = str_ireplace(['Concepto Abonos', 'Concepto Abono'], 'Concepto Abono', $subject);

        $keyLower = mb_strtolower($subject);

        // Reglas para agrupar varios listados bajo "Permisos Tablas"
        $tablesGroupPatterns = [
            // Clientes por renovar
            '/clientes\s+por\s+renov/iu',
            // Segundo recorrido(s)
            '/segundo\s+recorr/iu',
            // Usuarios que abonaron a yape(s)
            '/usuarios?.*abonan?.*yape/iu',
            // Yape(s) control de entregas, incluyendo "Yape Clientes Control de Entregas"
            '/yapes?\s+control\s+entreg/iu',
            '/yape\s*clientes?.*control\s*de\s*entregas?/iu',
            // Yapes totales del día
            '/yapes?\s+totales?\s+del\s+d[ií]a/iu',
        ];

        $canonical = $canonicalMap[$keyLower] ?? $subject;
        foreach ($tablesGroupPatterns as $pattern) {
            if (preg_match($pattern, $keyLower)) {
                $canonical = 'Permisos Tablas';
                break;
            }
        }

        $groups[$canonical] = $groups[$canonical] ?? [];
        $groups[$canonical][] = $perm;
    }
    ksort($groups);
    // Abrir por defecto los grupos que ya tienen permisos seleccionados
    $selectedIds = collect((array) data_get($this, 'data.permissions'))
        ->map(fn($v) => (int) $v)
        ->all();
    $openInit = [];
    foreach ($groups as $group => $items) {
        foreach ($items as $perm) {
            if (in_array($perm->id, $selectedIds, true)) {
                $openInit[\Illuminate\Support\Str::slug($group, '-')] = true;
                break;
            }
        }
    }
@endphp

<div wire:ignore x-data="{
        open: @js($openInit),
        perms: @entangle('data.permissions'),
        selectGroup(ids){
            const s = new Set(this.perms.map(v => parseInt(v)));
            ids.forEach(id => s.add(parseInt(id)));
            this.perms = Array.from(s);
        },
        clearGroup(ids){
            const remove = new Set(ids.map(id => parseInt(id)));
            this.perms = this.perms.filter(id => !remove.has(parseInt(id)));
        },
        groupAllSelected(ids){
            const s = new Set(this.perms.map(v => parseInt(v)));
            return ids.every(id => s.has(parseInt(id)));
        }
    }"
    x-init="perms = Array.isArray(perms) ? perms.map(v=>parseInt(v)) : []"
    class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @foreach ($groups as $group => $items)
        @php
            $groupKey = \Illuminate\Support\Str::slug($group, '-');
            $groupIds = collect($items)->pluck('id')->map(fn($v) => (int) $v)->all();
        @endphp
        <div class="rounded-lg border border-gray-200 overflow-hidden shadow-sm bg-white">
            <div class="w-full flex items-center justify-between px-4 py-2 bg-gray-50">
                <button type="button"
                    @click="open['{{ $groupKey }}'] = !open['{{ $groupKey }}']"
                    class="flex items-center gap-2 hover:text-gray-900">
                    <span class="text-base font-bold text-gray-900">{{ $group }}</span>
                    <svg x-show="!open['{{ $groupKey }}']" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    <svg x-show="open['{{ $groupKey }}']" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                </button>
                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer" @click.stop>
                    <input type="checkbox"
                           :checked="groupAllSelected(@js($groupIds))"
                           @change="($event.target.checked) ? selectGroup(@js($groupIds)) : clearGroup(@js($groupIds))"
                           class="fi-input-checkbox rounded text-primary-600 focus:ring-primary-500">
                </label>
            </div>

            <div x-show="open['{{ $groupKey }}']" x-collapse class="px-3 py-2 bg-white">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-1">
                    @foreach ($items as $perm)
                        <label class="flex items-center space-x-2 py-1" wire:key="perm-{{ $perm->id }}">
                            <input type="checkbox" value="{{ $perm->id }}" name="permissions[]" x-model.number="perms" @checked(in_array($perm->id, (array) data_get($this, 'data.permissions')))
                                    class="fi-input-checkbox rounded text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-gray-700 select-none">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
@php $showSave = ($this instanceof \Filament\Resources\Pages\EditRecord) || ($this instanceof \Filament\Resources\Pages\CreateRecord); @endphp
@if ($showSave)
@endif
