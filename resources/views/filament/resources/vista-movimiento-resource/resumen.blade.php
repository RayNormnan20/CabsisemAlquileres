<div class="mt-4 p-4 bg-white shadow sm:rounded-lg border">
    <div class="grid grid-cols-3 gap-4 text-center text-sm font-semibold">
        <div>
            <div class="text-gray-700">Total Ingresos</div>
            <div class="text-2xl text-green-600">S/ {{ number_format($totalIngresos, 2) }}</div>
        </div>
        <div>
            <div class="text-gray-700">Total Gastos</div>
            <div class="text-2xl text-red-600">S/ {{ number_format($totalGastos, 2) }}</div>
        </div>
        <div>
            <div class="text-gray-700">Total Neto</div>
            @php
                $totalNeto = $totalIngresos - $totalGastos;
                $colorClase = $totalNeto >= 0 ? 'text-blue-600' : 'text-orange-600';
            @endphp
            <div class="text-2xl {{ $colorClase }}">S/ {{ number_format($totalNeto, 2) }}</div>
        </div>
    </div>
</div>
