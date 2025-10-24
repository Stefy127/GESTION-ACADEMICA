<?php
/**
 * Router simple para la aplicación
 */
class Router
{
    private $routes = [];
    
    /**
     * Agregar una ruta GET
     */
    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }
    
    /**
     * Agregar una ruta POST
     */
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }
    
    /**
     * Ejecutar el router
     */
    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover la barra final si existe
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }
        
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            $this->callHandler($handler);
        } else {
            $this->handleNotFound();
        }
    }
    
    /**
     * Llamar al manejador de la ruta
     */
    private function callHandler($handler)
    {
        if (is_string($handler)) {
            // Formato: "Controller@method"
            list($controllerName, $method) = explode('@', $handler);
            $controllerClass = $controllerName . 'Controller';
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    echo $controller->$method();
                } else {
                    $this->handleNotFound();
                }
            } else {
                $this->handleNotFound();
            }
        } elseif (is_callable($handler)) {
            echo $handler();
        } else {
            $this->handleNotFound();
        }
    }
    
    /**
     * Manejar rutas no encontradas
     */
    private function handleNotFound()
    {
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
    }
}
