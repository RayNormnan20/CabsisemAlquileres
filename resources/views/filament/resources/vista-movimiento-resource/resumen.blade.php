<div class="mt-4 p-4 bg-white shadow sm:rounded-lg border">
    <div class="grid grid-cols-3 gap-4 text-sm">
        <div>
            <div class="font-medium text-gray-700">Total Ingresos</div>
            <div class="text-green-600">S/ {{ number_format($totalIngresos, 2) }}</div>
        </div>
        <div>
            <div class="font-medium text-gray-700">Total Gastos</div>
            <div class="text-red-600">S/ {{ number_format($totalGastos, 2) }}</div>
        </div>
    </div>
</div>
