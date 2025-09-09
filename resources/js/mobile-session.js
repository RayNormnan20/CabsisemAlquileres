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
        
        if (this.isMobile) {
            this.initMobileSessionHandlers();
            this.detectAppSwitch();
            this.setupCameraAndFileDetection();
        }
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
            // No hacer logout inmediato si hay actividad de login, cámara o archivos
            if (this.isLoginActive || this.isCameraActive || this.isFileSelectionActive) {
                console.log('🔄 Ventana perdió el foco - logout pausado por actividad activa');
                return;
            }
            
            // En páginas de login, dar más tiempo antes del logout
            if (this.isOnLoginPage()) {
                console.log('🔄 Ventana perdió el foco en página de login - logout retrasado');
                setTimeout(() => {
                    if (!this.isLoginActive && !this.isCameraActive && !this.isFileSelectionActive) {
                        console.log('🔄 Logout retrasado ejecutado');
                        this.performMobileLogout();
                    }
                }, this.isAndroid ? 15000 : 10000); // Android necesita más tiempo
            } else {
                console.log('🔄 Ventana perdió el foco - logout móvil');
                this.performMobileLogout();
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
        const INACTIVITY_TIMEOUT = 5000; // 5 segundos
        
        const resetTimer = () => {
            clearTimeout(inactivityTimer);
            
            // Solo activar el timer si no hay actividad de cámara, selección de archivos o login
            if (!this.isCameraActive && !this.isFileSelectionActive && !this.isLoginActive) {
                // Timeout más largo durante el login y ajustado por dispositivo
                let timeoutDuration = INACTIVITY_TIMEOUT; // Default
                
                if (this.isOnLoginPage()) {
                    // Android necesita más tiempo debido al teclado virtual
                    timeoutDuration = this.isAndroid ? 45000 : 30000; // 45s Android, 30s otros
                } else if (this.isAndroid) {
                    timeoutDuration = 8000; // Android general más tiempo
                }
                inactivityTimer = setTimeout(() => {
                    console.log('Inactividad detectada - logout móvil');
                    this.performMobileLogout();
                }, timeoutDuration);
            } else {
                console.log('Logout pausado - actividad de cámara/archivos/login detectada');
                if (this.isCameraActive || this.isFileSelectionActive) {
                    resetTimer(); // Reiniciar el timer
                }
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
                console.log('Selección de archivos detectada');
                this.isFileSelectionActive = true;
                
                // Resetear después de 30 segundos
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Selección de archivos finalizada');
                }, 30000);
            }
        });
        
        // Detectar cuando se hace click en inputs de archivo
        document.addEventListener('click', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Input de archivo activado - protegiendo sesión por 5 minutos');
                this.isFileSelectionActive = true;
                
                // Extender tiempo para acceso a galería de fotos (iOS)
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de selección de archivos');
                }, 300000); // 5 minutos para dar tiempo a navegar en Fototeca
            }
        });
        
        // Detectar mousedown en inputs de archivo (se activa antes que click)
        document.addEventListener('mousedown', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Mousedown en input de archivo - activando protección inmediata');
                this.isFileSelectionActive = true;
                
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout mousedown de selección de archivos');
                }, 300000); // 5 minutos
            }
        });
        
        // Detectar touchstart en inputs de archivo (específico para móviles)
        document.addEventListener('touchstart', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Touchstart en input de archivo - protección móvil activada');
                this.isFileSelectionActive = true;
                
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout touchstart de selección de archivos');
                }, 300000); // 5 minutos
            }
        });
        
        // Detectar inputs de archivo con accept="image/*" (específico para fotos)
        document.addEventListener('focus', (event) => {
            if (event.target && event.target.type === 'file' && 
                event.target.accept && event.target.accept.includes('image')) {
                console.log('Input de imagen detectado - protegiendo sesión extendida por 5 minutos');
                this.isFileSelectionActive = true;
                
                // Tiempo extendido para galería de fotos
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout extendido de selección de imágenes');
                }, 300000); // 5 minutos
            }
        });
        
        // Detectar cualquier input de archivo que reciba focus (general)
        document.addEventListener('focusin', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Input de archivo enfocado - activando protección preventiva');
                this.isFileSelectionActive = true;
                
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de focus en input de archivo');
                }, 300000); // 5 minutos
            }
        });
        
        // Observer para detectar inputs de archivo creados dinámicamente
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Buscar inputs de archivo en el nodo agregado
                        const fileInputs = node.querySelectorAll ? node.querySelectorAll('input[type="file"]') : [];
                        if (node.type === 'file') {
                            console.log('Input de archivo dinámico detectado');
                            this.isFileSelectionActive = true;
                            setTimeout(() => {
                                this.isFileSelectionActive = false;
                            }, 300000);
                        }
                        fileInputs.forEach(() => {
                            console.log('Input de archivo dinámico encontrado en DOM');
                            this.isFileSelectionActive = true;
                            setTimeout(() => {
                                this.isFileSelectionActive = false;
                            }, 300000);
                        });
                    
                    // Resetear después de un tiempo si la cámara sigue activa
                    setTimeout(() => {
                        if (this.isCameraActive) {
                            console.log('Timeout de cámara - reseteando estado');
                            this.isCameraActive = false;
                        }
                    }, 300000); // 5 minutos
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Detectar acceso a la cámara mediante getUserMedia
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            const originalGetUserMedia = navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices);
            
            navigator.mediaDevices.getUserMedia = (constraints) => {
                if (constraints && constraints.video) {
                    console.log('Acceso a cámara detectado');
                    this.isCameraActive = true;
                }
                
                return originalGetUserMedia(constraints).then(stream => {
                    // Detectar cuando se cierra la cámara
                    stream.getTracks().forEach(track => {
                        track.addEventListener('ended', () => {
                            console.log('Cámara cerrada');
                            this.isCameraActive = false;
                        });
                    });
                    
                    return stream;
                }).catch(error => {
                    this.isCameraActive = false;
                    throw error;
                });
            };
        }
        
        // Detectar elementos de captura de imagen (input[capture])
        document.addEventListener('focus', (event) => {
            if (event.target && event.target.hasAttribute && event.target.hasAttribute('capture')) {
                console.log('Elemento de captura activado');
                this.isCameraActive = true;
                
                // Resetear después de 60 segundos
                setTimeout(() => {
                    this.isCameraActive = false;
                    console.log('Timeout de captura de imagen');
                }, 60000);
            }
        });
        
        // Detectar cuando el input de archivo está a punto de activarse (iOS específico)
        document.addEventListener('pointerdown', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Pointer down en input de archivo - protección inmediata iOS');
                this.isFileSelectionActive = true;
                
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de protección pointer down');
                }, 300000); // 5 minutos
            }
        });
        
        // Detectar cuando se va a mostrar el selector de archivos nativo
        document.addEventListener('input', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Input event en archivo - selector nativo activado');
                this.isFileSelectionActive = true;
                
                // Protección preventiva
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de protección preventiva');
                }, 150000); // 2.5 minutos
            }
        });
    }
    
    /**
     * Maneja cuando la página se oculta
     */
    handlePageHidden() {
        this.isPageVisible = false;
        console.log('📱 Página oculta detectada');
        
        // Solo hacer logout si no hay actividad de cámara, archivos o login
        if (!this.isCameraActive && !this.isFileSelectionActive && !this.isLoginActive) {
            // En páginas de login, ser menos agresivo
            if (this.isOnLoginPage()) {
                console.log('🔐 Página oculta en login - logout retrasado');
                setTimeout(() => {
                    if (!this.isLoginActive && !this.isCameraActive && !this.isFileSelectionActive) {
                        console.log('🚪 Logout retrasado por página oculta en login');
                        this.performMobileLogout();
                    }
                }, this.isAndroid ? 20000 : 15000); // Android necesita más tiempo
            } else {
                console.log('🚪 Cerrando sesión por página oculta');
                this.performMobileLogout();
            }
        } else {
            console.log('📷 Logout pausado - hay actividad de cámara/archivos/login');
        }
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
                console.log('App oculta - verificando logout');
                // No hacer logout si hay actividad de cámara o archivos
                if (!this.isCameraActive && !this.isFileSelectionActive) {
                    console.log('Logout por app oculta');
                    this.performMobileLogout();
                } else {
                    console.log('Logout pausado - actividad de cámara/archivos detectada');
                }
            }
            
            lastVisibilityChange = now;
        });
        
        // Detectar cuando el usuario cambia de pestaña o app
        window.addEventListener('blur', () => {
            console.log('Ventana perdió foco - verificando logout');
            if (this.isMobile) {
                // No hacer logout si hay actividad de cámara o archivos
                if (!this.isCameraActive && !this.isFileSelectionActive) {
                    console.log('Logout por pérdida de foco');
                    this.performMobileLogout();
                } else {
                    console.log('Logout pausado - actividad de cámara/archivos detectada');
                }
            }
        });
        
        // Detectar cuando se minimiza o cambia de app
        window.addEventListener('pagehide', () => {
            console.log('Página oculta - verificando logout');
            // No hacer logout si hay actividad de cámara o archivos
            if (!this.isCameraActive && !this.isFileSelectionActive) {
                console.log('Logout por página oculta');
                this.performMobileLogout();
            } else {
                console.log('Logout pausado - actividad de cámara/archivos detectada');
            }
        });
    }
    
    /**
     * Maneja cuando la página se vuelve visible
     */
    handlePageVisible() {
        this.isPageVisible = true;
        console.log('Página visible en dispositivo móvil');
        
        // Si había actividad de archivos, extender la protección al regresar
        if (this.isFileSelectionActive) {
            console.log('Regresando de galería de fotos - extendiendo protección');
            
            // Extender protección por 2 minutos más al regresar
            setTimeout(() => {
                if (this.isFileSelectionActive) {
                    this.isFileSelectionActive = false;
                    console.log('Protección extendida finalizada tras regresar de galería');
                }
            }, 120000); // 2 minutos adicionales
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
    async performMobileLogout() {
        try {
            console.log('Realizando logout automático en dispositivo móvil');
            
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
     * Método público para forzar logout
     */
    forceLogout() {
        if (this.isMobile) {
            this.performMobileLogout();
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