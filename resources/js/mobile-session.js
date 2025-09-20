/**
 * Mobile Session Manager
 * Maneja el cierre automático de sesión en dispositivos móviles
 * cuando el usuario sale de la página
 */

class MobileSessionManager {
    constructor() {
        this.isMobile = this.detectMobile();
        this.isAndroid = /Android/i.test(navigator.userAgent);
        this.isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
        this.isPageVisible = true;
        this.isCameraActive = false;
        this.isFileSelectionActive = false;
        this.isLoginActive = false;
        this.isPostLoginProtection = false;
        this.postLoginTimeout = null;

        // Variables para debouncing mejorado
        this.logCooldown = 10000; // 10 segundos entre logs similares (aumentado)
        this.messageTimestamps = new Map(); // Mapa para rastrear timestamps por mensaje

        if (this.isMobile) {
            this.initMobileSessionHandlers();
            this.detectAppSwitch();
            this.setupCameraAndFileDetection();
            this.setupPostLoginDetection();
        }
    }

    /**
     * Log con debouncing mejorado para evitar spam
     */
    logWithDebounce(message, type = 'info') {
        const now = Date.now();
        const messageKey = message.substring(0, 60); // Usar más caracteres para mejor identificación

        // Verificar si este mensaje específico está en cooldown
        const lastTime = this.messageTimestamps.get(messageKey);
        if (lastTime && (now - lastTime) < this.logCooldown) {
            return; // Silenciar el mensaje
        }

        // Actualizar timestamp para este mensaje específico
        this.messageTimestamps.set(messageKey, now);

        // Limpiar mensajes antiguos del mapa (mantener solo los últimos 50)
        if (this.messageTimestamps.size > 50) {
            const entries = Array.from(this.messageTimestamps.entries());
            entries.sort((a, b) => b[1] - a[1]); // Ordenar por timestamp descendente
            this.messageTimestamps.clear();
            entries.slice(0, 30).forEach(([key, time]) => {
                this.messageTimestamps.set(key, time);
            });
        }

        console.log(message);
    }

    /**
     * Detecta si el dispositivo es móvil
     */
    detectMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
               (window.innerWidth <= 768);
    }

    /**
     * Inicializa los manejadores de eventos para dispositivos móviles
     */
    initMobileSessionHandlers() {
        // Page Visibility API - detecta cuando la página se oculta/muestra
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.handlePageHidden();
            } else {
                this.handlePageVisible();
            }
        });

        // Evento beforeunload - cuando el usuario intenta salir
        window.addEventListener('beforeunload', (event) => {
            this.handleBeforeUnload(event);
        });

        // Evento pagehide - cuando la página se oculta
        window.addEventListener('pagehide', () => {
            this.handlePageHide();
        });

        // Evento blur - cuando la ventana pierde el foco (más sensible)
        window.addEventListener('blur', () => {
            // No hacer logout inmediato si hay actividad de login, cámara, archivos o protección post-login
            if (this.isLoginActive || this.isCameraActive || this.isFileSelectionActive || this.isPostLoginProtection) {
                console.log('🔄 Ventana perdió el foco - logout pausado por actividad activa');
                return;
            }

            // En páginas de login, dar más tiempo antes del logout
            if (this.isOnLoginPage()) {
                console.log('🔄 Ventana perdió el foco en página de login - logout retrasado');
                setTimeout(() => {
                    if (!this.isLoginActive && !this.isCameraActive && !this.isFileSelectionActive && !this.isPostLoginProtection) {
                        console.log('🔄 Logout retrasado ejecutado');
                        this.performMobileLogout();
                    }
                }, this.isAndroid ? 25000 : 15000); // Android: 25s, iOS: 15s (aumentado desde 15s/10s)
            } else {
                // Para páginas normales, dar tiempo adicional en Android
                if (this.isAndroid) {
                    console.log('🔄 Android: Ventana perdió el foco - logout retrasado');
                    setTimeout(() => {
                        if (!this.isLoginActive && !this.isCameraActive && !this.isFileSelectionActive && !this.isPostLoginProtection) {
                            console.log('🔄 Android: Logout retrasado ejecutado');
                            this.performMobileLogout();
                        }
                    }, 10000); // 10 segundos de gracia para Android
                } else {
                    console.log('🔄 Ventana perdió el foco - logout móvil');
                    this.performMobileLogout();
                }
            }
        });

        // Evento focus - cuando la ventana recupera el foco
        window.addEventListener('focus', () => {
            this.handleWindowFocus();
        });

        // Detectar inactividad (reducido a 30 segundos)
        this.setupInactivityTimer();
    }

    /**
     * Configura el timer de inactividad
     */
    setupInactivityTimer() {
        let inactivityTimer;
        const INACTIVITY_TIMEOUT = 30000; // Aumentado de 5 a 15 segundos

        const resetTimer = () => {
            clearTimeout(inactivityTimer);

            // Solo activar el timer si no hay actividad de cámara, selección de archivos, login o protección post-login
            if (!this.isCameraActive && !this.isFileSelectionActive && !this.isLoginActive && !this.isPostLoginProtection) {
                // Timeout más largo durante el login y ajustado por dispositivo
                let timeoutDuration = INACTIVITY_TIMEOUT; // Default

                if (this.isOnLoginPage()) {
                    // Android necesita más tiempo debido al teclado virtual
                    timeoutDuration = this.isAndroid ? 120000 : 90000; // Aumentado: 2min Android, 1.5min otros
                } else if (this.isAndroid) {
                    timeoutDuration = 30000; // Android general más tiempo (aumentado de 8s a 30s)
                }
                inactivityTimer = setTimeout(() => {
                    this.logWithDebounce('Inactividad detectada - logout móvil');
                    this.performMobileLogout();
                }, timeoutDuration);
            } else {
                this.logWithDebounce('Logout pausado - actividad de cámara/archivos/login/post-login detectada');
                // REMOVIDO: resetTimer(); - esto causaba recursión infinita
            }
        };

        // Eventos que resetean el timer
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });

        // Iniciar el timer
        resetTimer();
    }

    /**
     * Configura la detección de actividad de cámara y archivos
     */
    setupCameraAndFileDetection() {
        console.log('📷 Configurando detección de cámara, archivos y login...');

        // Configurar detección de actividad de login
        this.setupLoginDetection();

        // Detectar cuando se abre la cámara o se seleccionan archivos
        document.addEventListener('change', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('📷 Selección de archivos detectada');
                this.isFileSelectionActive = true;

                // Timeout más largo para Android
                const timeout = this.isAndroid ? 45000 : 30000; // 45s para Android, 30s para otros
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('📷 Selección de archivos finalizada');
                }, timeout);
            }
        });

        // Detectar cuando se hace click en inputs de archivo
        document.addEventListener('click', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('📷 Input de archivo activado - protegiendo sesión');
                this.isFileSelectionActive = true;
                this.isCameraActive = true; // Activar también protección de cámara

                // Tiempo extendido para Android (10 minutos vs 5 minutos)
                const timeout = this.isAndroid ? 600000 : 300000; // 10min Android, 5min otros
                console.log(`📷 Protección activada por ${timeout/1000} segundos (${this.isAndroid ? 'Android' : 'iOS/otros'})`);

                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout de selección de archivos');
                }, timeout);
            }
        });

        // Detectar mousedown en inputs de archivo (se activa antes que click)
        document.addEventListener('mousedown', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('📷 Mousedown en input de archivo - activando protección inmediata');
                this.isFileSelectionActive = true;
                this.isCameraActive = true;

                const timeout = this.isAndroid ? 600000 : 300000; // 10min Android, 5min otros
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout mousedown de selección de archivos');
                }, timeout);
            }
        });

        // Detectar touchstart en inputs de archivo (específico para móviles)
        document.addEventListener('touchstart', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('📷 Touchstart en input de archivo - protección móvil activada');
                this.isFileSelectionActive = true;
                this.isCameraActive = true;

                // Android necesita más tiempo debido a los intents del sistema
                const timeout = this.isAndroid ? 600000 : 300000; // 10min Android, 5min otros
                console.log(`📷 Protección touchstart por ${timeout/1000} segundos`);

                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout touchstart de selección de archivos');
                }, timeout);
            }
        });

        // Detectar inputs de archivo con accept="image/*" (específico para fotos)
        document.addEventListener('focus', (event) => {
            if (event.target && event.target.type === 'file' &&
                event.target.accept && event.target.accept.includes('image')) {
                console.log('📷 Input de imagen detectado - protegiendo sesión extendida');
                this.isFileSelectionActive = true;
                this.isCameraActive = true;

                // Tiempo muy extendido para galería de fotos en Android
                const timeout = this.isAndroid ? 900000 : 300000; // 15min Android, 5min otros
                console.log(`📷 Protección de imagen por ${timeout/1000} segundos`);

                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout extendido de selección de imágenes');
                }, timeout);
            }
        });

        // Detectar cualquier input de archivo que reciba focus (general)
        document.addEventListener('focusin', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('📷 Input de archivo enfocado - activando protección preventiva');
                this.isFileSelectionActive = true;
                this.isCameraActive = true;

                const timeout = this.isAndroid ? 600000 : 300000; // 10min Android, 5min otros
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout de focus en input de archivo');
                }, timeout);
            }
        });

        // NUEVO: Detectar específicamente intents de cámara en Android
        if (this.isAndroid) {
            // Detectar cuando se presiona cualquier botón que pueda abrir la cámara
            document.addEventListener('pointerdown', (event) => {
                const target = event.target;
                if (target && (
                    target.type === 'file' ||
                    target.accept?.includes('image') ||
                    target.capture !== undefined ||
                    target.closest('[accept*="image"]') ||
                    target.closest('input[type="file"]')
                )) {
                    console.log('📱 Android: Intent de cámara detectado via pointerdown');
                    this.isFileSelectionActive = true;
                    this.isCameraActive = true;

                    // Protección muy larga para Android
                    setTimeout(() => {
                        this.isFileSelectionActive = false;
                        this.isCameraActive = false;
                        console.log('📱 Android: Timeout de intent de cámara');
                    }, 900000); // 15 minutos
                }
            });

            // Detectar cambios en la visibilidad específicos de Android
            let androidVisibilityTimer;
            document.addEventListener('visibilitychange', () => {
                if (document.hidden && (this.isFileSelectionActive || this.isCameraActive)) {
                    console.log('📱 Android: Página oculta durante actividad de cámara - extendiendo protección');
                    clearTimeout(androidVisibilityTimer);

                    // Extender protección cuando la página se oculta durante uso de cámara
                    androidVisibilityTimer = setTimeout(() => {
                        if (document.hidden) {
                            console.log('📱 Android: Página sigue oculta después de timeout extendido');
                            // No hacer logout automático, mantener protección
                        }
                    }, 1200000); // 20 minutos adicionales
                } else if (!document.hidden) {
                    clearTimeout(androidVisibilityTimer);
                    console.log('📱 Android: Página visible nuevamente');
                }
            });
        }

        // Observer para detectar inputs de archivo creados dinámicamente
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Buscar inputs de archivo en el nodo agregado
                        const fileInputs = node.querySelectorAll ? node.querySelectorAll('input[type="file"]') : [];
                        if (node.type === 'file') {
                            console.log('📷 Input de archivo dinámico detectado');
                            this.isFileSelectionActive = true;
                            this.isCameraActive = true;

                            const timeout = this.isAndroid ? 600000 : 300000;
                            setTimeout(() => {
                                this.isFileSelectionActive = false;
                                this.isCameraActive = false;
                            }, timeout);
                        }
                        fileInputs.forEach(() => {
                            console.log('📷 Input de archivo dinámico encontrado en DOM');
                            this.isFileSelectionActive = true;
                            this.isCameraActive = true;

                            const timeout = this.isAndroid ? 600000 : 300000;
                            setTimeout(() => {
                                this.isFileSelectionActive = false;
                                this.isCameraActive = false;
                            }, timeout);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // NUEVO: Detectar específicamente elementos con capture attribute (cámara directa)
        document.addEventListener('click', (event) => {
            const target = event.target;
            if (target && (target.capture !== undefined || target.getAttribute('capture') !== null)) {
                console.log('📷 Elemento con capture detectado - activando protección de cámara directa');
                this.isFileSelectionActive = true;
                this.isCameraActive = true;

                // Protección muy extendida para cámara directa
                const timeout = this.isAndroid ? 1200000 : 600000; // 20min Android, 10min otros
                console.log(`📷 Protección de cámara directa por ${timeout/1000} segundos`);

                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    this.isCameraActive = false;
                    console.log('📷 Timeout de cámara directa');
                }, timeout);
            }
        });
    }

    /**
     * Maneja cuando la página se oculta (cambio de aplicación)
     */
    handlePageHidden() {
        this.isPageVisible = false;
        console.log('📱 Página oculta detectada - CAMBIO DE APLICACIÓN');

        // SIEMPRE hacer logout cuando se cambia de aplicación
        // Sin importar protecciones activas (cámara, archivos, login, etc.)
        console.log('🚪 Forzando logout por cambio de aplicación en 30 segundos - SIN EXCEPCIONES');

        // Esperar 30 segundos antes de hacer logout forzado
        setTimeout(() => {
            if (!this.isPageVisible) { // Solo si la página sigue oculta
                console.log('⏰ 30 segundos transcurridos - ejecutando logout forzado');
                this.forceLogout(true); // true = ignorar protecciones
            } else {
                console.log('📱 Página visible nuevamente - cancelando logout forzado');
            }
        }, 30000); // 30 segundos
    }

    /**
     * Detecta cambio de aplicación en móviles
     */
    detectAppSwitch() {
        let lastVisibilityChange = Date.now();

        document.addEventListener('visibilitychange', () => {
            const now = Date.now();
            const timeDiff = now - lastVisibilityChange;

            if (document.hidden) {
                console.log('📱 App oculta (cambio de aplicación) - programando logout forzado en 30 segundos');

                // Esperar 30 segundos antes de hacer logout forzado
                setTimeout(() => {
                    if (document.hidden) { // Solo si la página sigue oculta
                        console.log('⏰ 30 segundos transcurridos - ejecutando logout forzado por visibilitychange');
                        this.forceLogout(true); // true = ignorar protecciones
                    } else {
                        console.log('📱 Página visible nuevamente - cancelando logout forzado por visibilitychange');
                    }
                }, 30000); // 30 segundos
            }

            lastVisibilityChange = now;
        });

        // Detectar cuando el usuario cambia de pestaña o app
        window.addEventListener('blur', () => {
            this.logWithDebounce('Ventana perdió foco - verificando logout');
            if (this.isMobile) {
                // No hacer logout si hay actividad de cámara, archivos, login o protección post-login
                if (!this.isCameraActive && !this.isFileSelectionActive && !this.isLoginActive && !this.isPostLoginProtection) {
                    this.logWithDebounce('Logout por pérdida de foco');
                    this.performMobileLogout();
                } else {
                    this.logWithDebounce('Logout pausado - actividad de cámara/archivos/login/post-login detectada');
                }
            }
        });

        // Detectar cuando se minimiza o cambia de app
        window.addEventListener('pagehide', () => {
            this.logWithDebounce('Página oculta - verificando logout');
            // Solo hacer logout si no hay actividad de cámara, archivos, login o protección post-login
            if (!this.isCameraActive && !this.isFileSelectionActive && !this.isLoginActive && !this.isPostLoginProtection) {
                this.logWithDebounce('Logout por página oculta');
                this.performMobileLogout();
            } else {
                this.logWithDebounce('Logout pausado - actividad de cámara/archivos/login/post-login detectada');
            }
        });
    }

    /**
     * Maneja cuando la página se vuelve visible
     */
    handlePageVisible() {
        this.isPageVisible = true;
        this.logWithDebounce('Página visible en dispositivo móvil');

        // Si había actividad de archivos, extender la protección al regresar
        if (this.isFileSelectionActive) {
            this.logWithDebounce('Regresando de galería de fotos - extendiendo protección');

            // Extender protección por 2 minutos más al regresar
            setTimeout(() => {
                if (this.isFileSelectionActive) {
                    this.isFileSelectionActive = false;
                    console.log('Protección extendida finalizada tras regresar de galería');
                }
            }, 120000); // 2 minutos adicionales
        }

        // Si regresamos después de un login exitoso, extender protección
        if (!this.isOnLoginPage() && !this.isPostLoginProtection) {
            // Verificar si acabamos de hacer login (hay token CSRF y no estamos en login)
            if (document.querySelector('meta[name="csrf-token"]')) {
                this.logWithDebounce('🔐 Regreso detectado después de posible login - activando protección');
                this.activatePostLoginProtection();
            }
        }
    }

    /**
     * Maneja el evento beforeunload
     */
    handleBeforeUnload(event) {
        // Realizar logout inmediato en móviles
        this.performMobileLogout();
    }

    /**
     * Maneja el evento pagehide
     */
    handlePageHide() {
        this.performMobileLogout();
    }

    /**
     * Maneja cuando la ventana pierde el foco
     */
    handleWindowBlur() {
        if (!this.isPageVisible) {
            this.handlePageHidden();
        }
    }

    /**
     * Maneja cuando la ventana recupera el foco
     */
    handleWindowFocus() {
        this.handlePageVisible();
    }

    /**
     * Realiza el logout automático para dispositivos móviles
     */
    async performMobileLogout(ignoreProtections = false) {
        // Verificación adicional antes de hacer logout (solo si no se ignoran las protecciones)
        if (!ignoreProtections && (this.isCameraActive || this.isFileSelectionActive || this.isLoginActive || this.isPostLoginProtection)) {
            console.log('🚫 Logout cancelado - protecciones activas:', {
                camera: this.isCameraActive,
                files: this.isFileSelectionActive,
                login: this.isLoginActive,
                postLogin: this.isPostLoginProtection
            });
            return;
        }

        if (ignoreProtections) {
            console.log('⚠️ LOGOUT FORZADO - Ignorando todas las protecciones por cambio de aplicación');
        }

        try {
            console.log('🔴 Realizando logout automático en dispositivo móvil');
            console.log('📊 Estado de protecciones:', {
                camera: this.isCameraActive,
                files: this.isFileSelectionActive,
                login: this.isLoginActive,
                postLogin: this.isPostLoginProtection
            });

            // Enviar petición de logout al servidor
            const response = await fetch('/mobile-logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Logout exitoso:', data.message);

                // Redirigir a la página de login
                window.location.href = '/login';
            } else {
                console.error('Error en logout automático:', response.statusText);
            }
        } catch (error) {
            console.error('Error al realizar logout automático:', error);
            // En caso de error, intentar redirigir a login de todas formas
            window.location.href = '/login';
        }
    }

    /**
     * Detecta si el usuario está en una página de login
     */
    isOnLoginPage() {
        return window.location.pathname.includes('/login') ||
               window.location.pathname.includes('/register') ||
               window.location.pathname.includes('/password') ||
               document.querySelector('form[action*="login"]') !== null;
    }

    /**
     * Configura la detección de actividad de login
     */
    setupLoginDetection() {
        // Detectar campos de login (email, password, celular, etc.)
         const loginFields = document.querySelectorAll('input[type="email"], input[type="password"], input[type="tel"], input[name*="email"], input[name*="password"], input[name*="login"], input[name*="celular"], input[name*="telefono"], input[name*="phone"]');

        loginFields.forEach(field => {
            // Eventos de interacción con campos de login
            ['focus', 'input', 'keydown', 'click'].forEach(eventType => {
                field.addEventListener(eventType, () => {
                    console.log('🔐 Actividad de login detectada - pausando logout automático por 2 minutos');
                    this.isLoginActive = true;

                    // Limpiar timeout anterior
                    if (this.loginTimeout) {
                        clearTimeout(this.loginTimeout);
                    }

                    // Resetear después de 2 minutos
                    this.loginTimeout = setTimeout(() => {
                        console.log('🔐 Timeout de actividad de login - reanudando detección normal');
                        this.isLoginActive = false;
                    }, 120000); // 2 minutos
                });
            });
        });

        // Detectar cuando se envía un formulario de login
        const loginForms = document.querySelectorAll('form[action*="login"], form[wire\\:submit*="login"], form[wire\\:submit*="authenticate"]');
        loginForms.forEach(form => {
            form.addEventListener('submit', () => {
                console.log('🔐 Formulario de login enviado - extendiendo protección por 3 minutos');
                this.isLoginActive = true;

                if (this.loginTimeout) {
                    clearTimeout(this.loginTimeout);
                }

                this.loginTimeout = setTimeout(() => {
                    this.isLoginActive = false;
                }, 180000); // 3 minutos después del envío
            });
        });

        // Observador para detectar campos de login dinámicos
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const newLoginFields = node.querySelectorAll ?
                             node.querySelectorAll('input[type="email"], input[type="password"], input[type="tel"], input[name*="email"], input[name*="password"], input[name*="celular"], input[name*="telefono"], input[name*="phone"]') : [];

                        newLoginFields.forEach(field => {
                            ['focus', 'input', 'keydown', 'click'].forEach(eventType => {
                                field.addEventListener(eventType, () => {
                                    console.log('🔐 Actividad de login detectada en campo dinámico');
                                    this.isLoginActive = true;

                                    if (this.loginTimeout) {
                                        clearTimeout(this.loginTimeout);
                                    }

                                    this.loginTimeout = setTimeout(() => {
                                        this.isLoginActive = false;
                                    }, 120000);
                                });
                            });
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Configura la detección de login exitoso
     */
    setupPostLoginDetection() {
        // Detectar redirecciones después del login
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;

        const checkForSuccessfulLogin = () => {
            // Si estamos saliendo de una página de login y no vamos a otra página de login
            const wasOnLogin = this.isOnLoginPage();

            setTimeout(() => {
                const isNowOnLogin = this.isOnLoginPage();

                if (wasOnLogin && !isNowOnLogin) {
                    console.log('🎉 Login exitoso detectado - activando protección post-login por 3 minutos');
                    this.activatePostLoginProtection();
                }
            }, 100);
        };

        // Interceptar cambios de historial
        history.pushState = function(...args) {
            checkForSuccessfulLogin();
            return originalPushState.apply(this, args);
        };

        history.replaceState = function(...args) {
            checkForSuccessfulLogin();
            return originalReplaceState.apply(this, args);
        };

        // Detectar cambios de URL
        window.addEventListener('popstate', checkForSuccessfulLogin);

        // Detectar formularios de login enviados exitosamente
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (form.matches('form[action*="login"], form[action*="auth"]') ||
                form.querySelector('input[type="email"], input[type="password"]')) {

                console.log('📝 Formulario de login enviado - preparando detección de éxito');

                // Esperar un poco para ver si hay redirección exitosa
                setTimeout(() => {
                    if (!this.isOnLoginPage()) {
                        console.log('🎉 Login exitoso por formulario - activando protección');
                        this.activatePostLoginProtection();
                    }
                }, 2000);
            }
        });

        // Detectar si ya estamos autenticados al cargar la página
        if (!this.isOnLoginPage() && document.querySelector('meta[name="csrf-token"]')) {
            // Si no estamos en login y hay token CSRF, probablemente estamos autenticados
            console.log('🔐 Usuario ya autenticado al cargar - activando protección inicial');
            this.activatePostLoginProtection();
        }
    }

    /**
     * Activa la protección post-login
     */
    activatePostLoginProtection() {
        this.isPostLoginProtection = true;
        console.log('🛡️ Protección post-login ACTIVADA por 5 minutos');

        // Limpiar timeout anterior si existe
        if (this.postLoginTimeout) {
            clearTimeout(this.postLoginTimeout);
            console.log('🔄 Timeout anterior de protección post-login limpiado');
        }

        // Desactivar protección después de 5 minutos
        this.postLoginTimeout = setTimeout(() => {
            console.log('⏰ Protección post-login finalizada después de 5 minutos');
            this.isPostLoginProtection = false;
        }, 300000); // 5 minutos
    }

    /**
     * Método público para forzar logout
     */
    forceLogout(ignoreProtections = false) {
        if (this.isMobile) {
            this.performMobileLogout(ignoreProtections);
        }
    }
}

// Inicializar el gestor de sesión móvil cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.mobileSessionManager = new MobileSessionManager();
    });
} else {
    window.mobileSessionManager = new MobileSessionManager();
}

// Exportar para uso en otros módulos
export default MobileSessionManager;
