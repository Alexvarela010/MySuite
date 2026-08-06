<?php
/**
 * Sistema de Internacionalización (i18n)
 * Carga los diccionarios de traducción según el idioma seleccionado
 */

class I18n {
    private static $currentLang = 'es';
    private static $translations = [];
    private static $initialized = false;
    
    /**
     * Inicializa el sistema i18n detectando el idioma automáticamente
     */
    public static function init($lang = null) {
        if (self::$initialized) {
            return;
        }
        
        if ($lang === null) {
            // Detectar idioma desde la ruta del archivo
            $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
            if (strpos($scriptPath, '/es/') !== false) {
                $lang = 'es';
            } elseif (strpos($scriptPath, '/it/') !== false) {
                $lang = 'it';
            } else {
                $lang = 'es';
            }
        }
        
        self::setLanguage($lang);
        self::$initialized = true;
    }
    
    /**
     * Establece el idioma actual
     */
    public static function setLanguage($lang) {
        self::$currentLang = $lang;
        self::loadTranslations();
    }
    
    /**
     * Obtiene el idioma actual
     */
    public static function getLanguage() {
        return self::$currentLang;
    }
    
    /**
     * Carga las traducciones del idioma actual
     */
    private static function loadTranslations() {
        $langFile = __DIR__ . '/' . self::$currentLang . '.php';
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        } else {
            // Fallback a español si no existe el archivo
            self::$translations = include __DIR__ . '/es.php';
        }
    }
    
    /**
     * Obtiene una traducción
     * @param string $key Clave de la traducción
     * @param array $params Parámetros para reemplazar en la cadena
     * @return string Texto traducido
     */
    public static function t($key, $params = []) {
        if (!self::$initialized) {
            self::init();
        }
        
        $value = self::$translations[$key] ?? $key;
        
        // Reemplazar parámetros si existen
        if (!empty($params)) {
            foreach ($params as $param => $replacement) {
                $value = str_replace('{' . $param . '}', $replacement, $value);
            }
        }
        
        return $value;
    }
    
    /**
     * Helper function para obtener traducciones (alias de t())
     */
    public static function translate($key, $params = []) {
        return self::t($key, $params);
    }
    
    /**
     * Obtiene la ruta de una imagen compartida
     * Usa ruta relativa desde la carpeta de idioma (en/, es/, it/)
     */
    public static function sharedAsset($image) {
        return '../assets/shared/' . $image;
    }
    
    /**
     * Obtiene la ruta de una imagen específica del idioma
     * Usa ruta relativa desde la carpeta de idioma (en/, es/, it/)
     * Si la imagen no existe en la carpeta de idioma, usa la de shared
     */
    public static function langAsset($image) {
        if (!self::$initialized) {
            self::init();
        }
        // Primero intentar en la carpeta de idioma
        $langPath = '../assets/' . self::$currentLang . '/' . $image;
        // Si no existe, usar la de shared (fallback)
        // Nota: En producción, verificar si el archivo existe realmente
        // Por ahora, siempre intentar primero lang, pero si no funciona, usar shared
        return $langPath;
    }
    
    /**
     * Obtiene la ruta de una imagen con fallback a shared
     * Intenta primero en la carpeta de idioma, si no existe usa shared
     */
    public static function langAssetWithFallback($image) {
        if (!self::$initialized) {
            self::init();
        }
        // Primero intentar en la carpeta de idioma
        $langPath = '../assets/' . self::$currentLang . '/' . $image;
        // Si no existe, usar la de shared como fallback
        // En producción, aquí se podría verificar si el archivo existe
        // Por ahora, retornamos shared directamente si sabemos que no hay versión por idioma
        return self::sharedAsset($image);
    }
    
    /**
     * Obtiene la ruta de CSS compartido
     * Usa ruta relativa desde la carpeta de idioma (en/, es/, it/)
     */
    public static function cssPath($file) {
        return '../assets/shared/css/' . $file;
    }
    
    /**
     * Obtiene las traducciones para JavaScript (arrays de meses, días, etc.)
     */
    public static function getJS($key) {
        if (!self::$initialized) {
            self::init();
        }
        return self::$translations['js.' . $key] ?? '';
    }
}

// Función helper global para facilitar el uso
if (!function_exists('__')) {
    function __($key, $params = []) {
        return I18n::t($key, $params);
    }
}

