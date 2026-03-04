/**
 * Sistema de Logging Estandarizado para Sparta Ledger
 * 
 * Proporciona niveles de logging estructurados para el frontend
 * Reemplaza console.log/warn/error no estandarizados
 * 
 * @author Sparta Ledger Team
 * @version 1.0.0
 * @date 2026-03-04
 */

const Logger = (() => {
    'use strict';

    // Niveles de logging
    const LEVELS = {
        DEBUG: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3,
        OFF: 99
    };

    // Configuración por defecto
    let currentLevel = LEVELS.INFO; // En producción cambiar a WARN o ERROR
    let enabled = true;

    /**
     * Detecta si estamos en ambiente de desarrollo
     */
    const isDevelopment = () => {
        return window.location.hostname === 'localhost' || 
               window.location.hostname === '127.0.0.1' ||
               window.location.hostname.includes('dev') ||
               window.location.hostname.includes('test');
    };

    /**
     * Formatea el mensaje con timestamp y nivel
     */
    const formatMessage = (level, message, context = null) => {
        const timestamp = new Date().toISOString();
        const prefix = `[${timestamp}] [${level}]`;
        
        if (context) {
            return `${prefix} ${message} | Context:`;
        }
        return `${prefix} ${message}`;
    };

    /**
     * Verifica si el nivel de log debe mostrarse
     */
    const shouldLog = (level) => {
        return enabled && LEVELS[level] >= currentLevel;
    };

    /**
     * Log de nivel DEBUG - Para depuración detallada
     * Solo se muestra en desarrollo
     */
    const debug = (message, context = null) => {
        if (!shouldLog('DEBUG')) return;
        
        console.log(formatMessage('DEBUG', message), context || '');
        if (context) console.log(context);
    };

    /**
     * Log de nivel INFO - Información general
     */
    const info = (message, context = null) => {
        if (!shouldLog('INFO')) return;
        
        console.info(formatMessage('INFO', message), context || '');
        if (context) console.info(context);
    };

    /**
     * Log de nivel WARN - Advertencias
     */
    const warn = (message, context = null) => {
        if (!shouldLog('WARN')) return;
        
        console.warn(formatMessage('WARN', message), context || '');
        if (context) console.warn(context);
    };

    /**
     * Log de nivel ERROR - Errores críticos
     */
    const error = (message, context = null) => {
        if (!shouldLog('ERROR')) return;
        
        console.error(formatMessage('ERROR', message), context || '');
        if (context) console.error(context);
    };

    /**
     * Configura el nivel mínimo de logging
     */
    const setLevel = (level) => {
        if (LEVELS.hasOwnProperty(level)) {
            currentLevel = LEVELS[level];
            info(`Logger: Nivel establecido a ${level}`);
        } else {
            warn(`Logger: Nivel inválido "${level}"`);
        }
    };

    /**
     * Habilita o deshabilita el logging
     */
    const setEnabled = (value) => {
        enabled = Boolean(value);
    };

    /**
     * Obtiene la configuración actual
     */
    const getConfig = () => {
        return {
            level: Object.keys(LEVELS).find(key => LEVELS[key] === currentLevel),
            enabled: enabled,
            isDevelopment: isDevelopment()
        };
    };

    // Auto-configuración según ambiente
    if (isDevelopment()) {
        currentLevel = LEVELS.DEBUG;
        console.log('%c🔧 Logger inicializado en modo DESARROLLO', 'color: #4CAF50; font-weight: bold');
    } else {
        currentLevel = LEVELS.WARN;
    }

    // API Pública
    return {
        debug,
        info,
        warn,
        error,
        setLevel,
        setEnabled,
        getConfig,
        LEVELS
    };
})();

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.Logger = Logger;
}

// Soporte para módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Logger;
}
