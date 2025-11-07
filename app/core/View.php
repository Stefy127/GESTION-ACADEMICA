<?php
/**
 * Clase para manejar las vistas
 */
class View
{
    private $basePath;
    
    // Mapeo de módulos a paquetes
    private $moduleMap = [
        'auth' => 'gestion_usuarios',
        'usuarios' => 'gestion_usuarios',
        'docentes' => 'gestion_usuarios',
        'materias' => 'gestion_academica',
        'grupos' => 'gestion_academica',
        'horarios' => 'gestion_academica',
        'aulas' => 'gestion_academica',
        'asistencia' => 'asistencia',
        'reportes' => 'reportes',
        'dashboard' => 'dashboard',
        'bitacora' => 'dashboard',
        'carga-masiva' => 'carga_masiva',
        'carga_masiva' => 'carga_masiva',
        'home' => 'home'
    ];
    
    public function __construct()
    {
        $this->basePath = __DIR__ . '/../';
    }
    
    /**
     * Encontrar la ruta de una vista
     */
    private function findViewPath($view)
    {
        // Dividir la ruta de la vista (ej: "usuarios/index" o "dashboard/index")
        $parts = explode('/', $view);
        $module = $parts[0];
        
        // Buscar en el paquete correspondiente
        if (isset($this->moduleMap[$module])) {
            $package = $this->moduleMap[$module];
            $viewPath = $this->basePath . $package . '/views/' . $view . '.php';
            if (file_exists($viewPath)) {
                return $viewPath;
            }
        }
        
        // Buscar en shared para layouts y errors
        $sharedPath = $this->basePath . 'shared/views/' . $view . '.php';
        if (file_exists($sharedPath)) {
            return $sharedPath;
        }
        
        // Fallback: buscar en todos los paquetes
        $packages = ['gestion_usuarios', 'gestion_academica', 'asistencia', 'reportes', 'dashboard', 'carga_masiva', 'home', 'shared'];
        foreach ($packages as $package) {
            $viewPath = $this->basePath . $package . '/views/' . $view . '.php';
            if (file_exists($viewPath)) {
                return $viewPath;
            }
        }
        
        return null;
    }
    
    /**
     * Renderizar una vista
     */
    public function render($view, $data = [])
    {
        $viewFile = $this->findViewPath($view);
        
        if (!$viewFile) {
            throw new Exception("La vista '{$view}' no existe");
        }
        
        // Extraer las variables para que estén disponibles en la vista
        extract($data);
        
        // Capturar el contenido de la vista
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Renderizar una vista con layout
     */
    public function renderWithLayout($view, $data = [], $layout = 'layout')
    {
        $content = $this->render($view, $data);
        
        $layoutFile = $this->basePath . 'shared/views/layouts/' . $layout . '.php';
        
        if (!file_exists($layoutFile)) {
            throw new Exception("El layout '{$layout}' no existe");
        }
        
        // Extraer las variables para el layout
        extract($data);
        
        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }
}
