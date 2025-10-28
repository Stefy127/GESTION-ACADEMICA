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
        
        // Buscar ruta exacta
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            $this->callHandler($handler);
        } else {
            // Buscar rutas con parámetros dinámicos
            $matchedRoute = $this->matchDynamicRoute($path, $method);
            if ($matchedRoute) {
                $this->callHandler($matchedRoute['handler'], $matchedRoute['params']);
            } else {
                $this->handleNotFound();
            }
        }
    }
    
    private function matchDynamicRoute($path, $method)
    {
        foreach ($this->routes[$method] as $route => $handler) {
            // Convertir {id} a patrón regex
            $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                // Extraer nombres de parámetros de la ruta
                preg_match_all('/\{(\w+)\}/', $route, $paramNames);
                $params = [];
                foreach ($paramNames[1] as $index => $name) {
                    $params[$name] = $matches[$index + 1] ?? null;
                }
                
                return ['handler' => $handler, 'params' => $params];
            }
        }
        return null;
    }
    
    /**
     * Llamar al manejador de la ruta
     */
    private function callHandler($handler, $params = [])
    {
        if (is_string($handler)) {
            // Formato: "Controller@method"
            list($controllerName, $method) = explode('@', $handler);
            $controllerClass = $controllerName . 'Controller';
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $method)) {
                    // Si hay parámetros, pasarlos al método
                    if (!empty($params)) {
                        $reflection = new ReflectionMethod($controller, $method);
                        $reflectionParams = $reflection->getParameters();
                        
                        if (!empty($reflectionParams)) {
                            $paramValues = [];
                            foreach ($reflectionParams as $param) {
                                $paramName = $param->getName();
                                $paramValues[] = $params[$paramName] ?? null;
                            }
                            echo call_user_func_array([$controller, $method], $paramValues);
                        } else {
                            echo $controller->$method();
                        }
                    } else {
                        echo $controller->$method();
                    }
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
