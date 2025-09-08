<div class="mt-8 space-y-6">
    <!-- TABLA DE PRÉSTAMOS REGULARES -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="bg-yellow-400 px-4 py-2">
            <h2 class="text-center font-bold text-black text-lg">PLANILLA RECAUDADOR</h2>
        </div>
        
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">PRÉSTAMOS</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">N°</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Fecha</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">U. Abono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cliente</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Teléfono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Crédito</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Abonos</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Saldo</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cuota</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Atraso (Días)</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($creditosRegulares as $index => $credito)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} dark:{{ $index % 2 == 0 ? 'bg-gray-800' : 'bg-gray-700' }}">
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->fecha_credito ? $credito->fecha_credito->format('d-M-y') : '-' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->ultima_fecha_pago ? \Carbon\Carbon::parse($credito->ultima_fecha_pago)->format('d-M-y') : 'Nunca' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->cliente_completo }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->telefono }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_credito, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->total_abonos, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->saldo_actual, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_cuota, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-center">{{ $credito->dias_atraso > 0 ? $credito->dias_atraso : 0 }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- Fila de totales para préstamos regulares -->
                    <tr class="bg-yellow-100 dark:bg-yellow-900 font-bold">
                        <td colspan="5" class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">TOTALES:</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['credito'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['abonos'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['saldo'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesRegulares['cuota'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- TABLA DE ADICIONALES -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">ADICIONALES</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">N°</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Fecha</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">U. Abono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cliente</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Teléfono</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Crédito</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Abonos</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Saldo</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Cuota</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border border-gray-300">Atraso (Días)</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($creditosAdicionales as $index => $credito)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} dark:{{ $index % 2 == 0 ? 'bg-gray-800' : 'bg-gray-700' }}">
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->fecha_credito ? $credito->fecha_credito->format('d-M-y') : '-' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->ultima_fecha_pago ? \Carbon\Carbon::parse($credito->ultima_fecha_pago)->format('d-M-y') : 'Nunca' }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->cliente_completo }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300">{{ $credito->telefono }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->valor_credito, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->total_abonos, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->saldo_actual, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($credito->porcentaje_interes, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-center">{{ $credito->dias_atraso > 0 ? $credito->dias_atraso : 0 }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- Fila de totales para adicionales -->
                    <tr class="bg-yellow-100 dark:bg-yellow-900 font-bold">
                        <td colspan="5" class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">TOTALES:</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['credito'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['abonos'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['saldo'], 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300 text-right">S/ {{ number_format($totalesAdicionales['cuota'] ?? 0, 2) }}</td>
                        <td class="px-2 py-2 text-sm text-gray-900 dark:text-gray-100 border border-gray-300"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- TOTAL GENERAL -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="bg-green-100 dark:bg-green-900 p-4 rounded border-2 border-green-400">
            <h3 class="text-lg font-bold text-center text-gray-800 dark:text-gray-200 mb-2">TOTAL GENERAL</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Crédito Total</span>
                    <span class="block text-lg font-bold text-gray-900 dark:text-gray-100">S/ {{ number_format($totalGeneral['credito'], 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Abonos Total</span>
                    <span class="block text-lg font-bold text-green-600 dark:text-green-400">S/ {{ number_format($totalGeneral['abonos'], 2) }}</span>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Total</span>
                    <span class="block text-lg font-bold text-red-600 dark:text-red-400">S/ {{ number_format($totalGeneral['saldo'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>