import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Configuración dinámica basada en el entorno
const isProduction = window.location.protocol === 'https:';
const wsHost = import.meta.env.VITE_PUSHER_HOST || (isProduction ? 'cabsisem.net.pe' : '127.0.0.1');
const wsPort = import.meta.env.VITE_PUSHER_PORT || (isProduction ? 6001 : 6001);
const scheme = import.meta.env.VITE_PUSHER_SCHEME || (isProduction ? 'https' : 'http');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: wsHost,
    wsPort: scheme === 'https' ? 443 : wsPort,
    wssPort: scheme === 'https' ? 443 : wsPort,
    forceTLS: scheme === 'https',
    encrypted: isProduction,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    }
});
