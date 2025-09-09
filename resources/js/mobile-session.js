/**
 * Mobile Session Manager
 * Maneja el cierre automático de sesión en dispositivos móviles
 * cuando el usuario sale de la página
 */

class MobileSessionManager {
    constructor() {
        this.isMobile = this.detectMobile();
        this.isPageVisible = true;
        this.logoutTimer = null;
        this.logoutDelay = 5000; // 5 segundos de delay antes del logout
        
        if (this.isMobile) {
            this.initMobileSessionHandlers();
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
        
        // Evento blur - cuando la ventana pierde el foco
        window.addEventListener('blur', () => {
            this.handleWindowBlur();
        });
        
        // Evento focus - cuando la ventana recupera el foco
        window.addEventListener('focus', () => {
            this.handleWindowFocus();
        });
    }
    
    /**
     * Maneja cuando la página se oculta
     */
    handlePageHidden() {
        this.isPageVisible = false;
        console.log('Página oculta en dispositivo móvil - iniciando timer de logout');
        
        // Iniciar timer para logout automático
        this.logoutTimer = setTimeout(() => {
            this.performMobileLogout();
        }, this.logoutDelay);
    }
    
    /**
     * Maneja cuando la página se vuelve visible
     */
    handlePageVisible() {
        this.isPageVisible = true;
        console.log('Página visible en dispositivo móvil - cancelando timer de logout');
        
        // Cancelar timer de logout si existe
        if (this.logoutTimer) {
            clearTimeout(this.logoutTimer);
            this.logoutTimer = null;
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