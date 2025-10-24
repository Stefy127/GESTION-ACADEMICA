<?php
/**
 * Controlador para gestión de aulas
 */
class AulasController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Gestión de Aulas',
            'user' => $this->getCurrentUser(),
            'aulas' => $this->getAulas()
        ];

        return $this->view->renderWithLayout('aulas/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Aula',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('aulas/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Aula',
            'user' => $this->getCurrentUser(),
            'aula' => $this->getAula($id)
        ];

        return $this->view->renderWithLayout('aulas/edit', $data);
    }

    private function getAulas()
    {
        return [
            ['id' => 1, 'nombre' => 'Aula 101', 'capacidad' => 30, 'tipo' => 'Teoría', 'disponible' => true],
            ['id' => 2, 'nombre' => 'Aula 102', 'capacidad' => 25, 'tipo' => 'Teoría', 'disponible' => true],
            ['id' => 3, 'nombre' => 'Laboratorio 1', 'capacidad' => 20, 'tipo' => 'Laboratorio', 'disponible' => false],
            ['id' => 4, 'nombre' => 'Laboratorio 2', 'capacidad' => 20, 'tipo' => 'Laboratorio', 'disponible' => true],
            ['id' => 5, 'nombre' => 'Aula 201', 'capacidad' => 35, 'tipo' => 'Teoría', 'disponible' => true]
        ];
    }

    private function getAula($id)
    {
        $aulas = $this->getAulas();
        foreach ($aulas as $aula) {
            if ($aula['id'] == $id) {
                return $aula;
            }
        }
        return null;
    }
}
