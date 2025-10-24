<?php
/**
 * Clase para manejar las vistas
 */
class View
{
    private $viewsPath;
    
    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../views/';
    }
    
    /**
     * Renderizar una vista
     */
    public function render($view, $data = [])
    {
        $viewFile = $this->viewsPath . $view . '.php';
        
        if (!file_exists($viewFile)) {
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
        
        $layoutFile = $this->viewsPath . 'layouts/' . $layout . '.php';
        
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
