<?php
/**
 * Clase base para todos los controladores
 */
abstract class Controller
{
    protected $view;
    
    public function __construct()
    {
        $this->view = new View();
    }
    
    /**
     * Renderizar una vista
     */
    protected function render($view, $data = [])
    {
        return $this->view->render($view, $data);
    }
    
    /**
     * Redirigir a otra página
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit();
    }
    
    /**
     * Obtener datos POST
     */
    protected function getPost($key = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? null;
    }
    
    /**
     * Obtener datos GET
     */
    protected function getGet($key = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }
    
    /**
     * Verificar si es una petición POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Verificar si es una petición GET
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Obtener usuario actual
     */
    protected function getCurrentUser()
    {
        $auth = new Auth();
        return $auth->getCurrentUser();
    }
}
