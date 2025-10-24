<?php
/**
 * Controlador para gestión de docentes
 */
class DocentesController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Gestión de Docentes',
            'user' => $this->getCurrentUser(),
            'docentes' => $this->getDocentes()
        ];

        return $this->view->renderWithLayout('docentes/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Registrar Docente',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('docentes/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Docente',
            'user' => $this->getCurrentUser(),
            'docente' => $this->getDocente($id)
        ];

        return $this->view->renderWithLayout('docentes/edit', $data);
    }

    private function getDocentes()
    {
        return [
            ['id' => 1, 'nombre' => 'Juan', 'apellido' => 'Pérez', 'email' => 'juan.perez@universidad.edu', 'ci' => '12345678', 'telefono' => '555-0001'],
            ['id' => 2, 'nombre' => 'María', 'apellido' => 'González', 'email' => 'maria.gonzalez@universidad.edu', 'ci' => '87654321', 'telefono' => '555-0002'],
            ['id' => 3, 'nombre' => 'Carlos', 'apellido' => 'López', 'email' => 'carlos.lopez@universidad.edu', 'ci' => '11223344', 'telefono' => '555-0003']
        ];
    }

    private function getDocente($id)
    {
        $docentes = $this->getDocentes();
        foreach ($docentes as $docente) {
            if ($docente['id'] == $id) {
                return $docente;
            }
        }
        return null;
    }
}
