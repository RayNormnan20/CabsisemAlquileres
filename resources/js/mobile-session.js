/**
 * Mobile Session Manager
 * Maneja el cierre automático de sesión en dispositivos móviles
 * cuando el usuario sale de la página
 */

class MobileSessionManager {
    constructor() {
        this.isMobile = this.detectMobile();
        this.isPageVisible = true;
        this.isCameraActive = false;
        this.isFileSelectionActive = false;
        
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
            console.log('Ventana perdió foco - logout móvil');
            this.performMobileLogout();
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
            inactivityTimer = setTimeout(() => {
                console.log('Inactividad detectada - logout móvil');
                // No hacer logout si hay actividad de cámara o archivos
                if (!this.isCameraActive && !this.isFileSelectionActive) {
                    this.performMobileLogout();
                } else {
                    console.log('Logout pausado - actividad de cámara/archivos detectada');
                    resetTimer(); // Reiniciar el timer
                }
            }, INACTIVITY_TIMEOUT);
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
                console.log('Input de archivo activado - protegiendo sesión por 2 minutos');
                this.isFileSelectionActive = true;
                
                // Extender tiempo para acceso a galería de fotos (iOS)
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de selección de archivos');
                }, 120000); // 2 minutos para dar tiempo a navegar en Fototeca
            }
        });
        
        // Detectar inputs de archivo con accept="image/*" (específico para fotos)
        document.addEventListener('focus', (event) => {
            if (event.target && event.target.type === 'file' && 
                event.target.accept && event.target.accept.includes('image')) {
                console.log('Input de imagen detectado - protegiendo sesión extendida');
                this.isFileSelectionActive = true;
                
                // Tiempo extendido para galería de fotos
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout extendido de selección de imágenes');
                }, 180000); // 3 minutos
            }
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
        
        // Detectar cuando se va a abrir la galería de fotos en iOS
        document.addEventListener('touchstart', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Preparando acceso a galería iOS - protegiendo sesión');
                this.isFileSelectionActive = true;
                
                // Protección extendida para iOS que abre apps nativas
                setTimeout(() => {
                    this.isFileSelectionActive = false;
                    console.log('Timeout de protección iOS');
                }, 240000); // 4 minutos para iOS
            }
        });
        
        // Detectar antes de que se abra el selector de archivos
        document.addEventListener('mousedown', (event) => {
            if (event.target && event.target.type === 'file') {
                console.log('Iniciando selección de archivo - activando protección');
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
        console.log('Página oculta en dispositivo móvil');
        
        // No hacer logout si hay actividad de cámara o archivos
        if (!this.isCameraActive && !this.isFileSelectionActive) {
            console.log('Logout inmediato');
            this.performMobileLogout();
        } else {
            console.log('Logout pausado - actividad de cámara/archivos detectada');
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