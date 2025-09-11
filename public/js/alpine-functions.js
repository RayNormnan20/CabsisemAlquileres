// Funciones globales para Alpine.js

// Función para el componente de rango de fechas
function dateRangePicker() {
    return {
        init() {
            // Inicialización del componente de rango de fechas
            console.log('DateRangePicker inicializado');
        }
    }
}

// Función de inicialización genérica
function init() {
    console.log('Componente Alpine.js inicializado');
}

// Hacer las funciones disponibles globalmente
window.dateRangePicker = dateRangePicker;
window.init = init;

// Función para notificaciones de Filament compatibles
window.showFilamentNotification = function(type, title, body) {
    if (typeof window.filament !== 'undefined' && window.filament.notify) {
        window.filament.notify({
            title: title,
            body: body,
            status: type,
            duration: 4000
        });
    } else if (typeof $filament !== 'undefined' && $filament.notify) {
        $filament.notify(type, body);
    } else {
        console.log(`${type.toUpperCase()}: ${title} - ${body}`);
    }
};

console.log('Alpine.js functions loaded successfully');