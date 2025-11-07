<?php
/**
 * Controlador principal de la aplicación
 */
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Gestión Académica',
            'message' => 'Bienvenido al sistema de gestión académica'
        ];
        
        return $this->view->renderWithLayout('home/index', $data);
    }
    
    public function about()
    {
        $data = [
            'title' => 'Acerca de',
            'message' => 'Sistema de gestión académica desarrollado con PHP y PostgreSQL'
        ];
        
        return $this->view->renderWithLayout('home/about', $data);
    }
}
