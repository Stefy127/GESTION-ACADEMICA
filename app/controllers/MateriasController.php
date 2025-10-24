<?php
/**
 * Controlador para gestión de materias
 */
class MateriasController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Gestión de Materias',
            'user' => $this->getCurrentUser(),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('materias/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Materia',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('materias/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Materia',
            'user' => $this->getCurrentUser(),
            'materia' => $this->getMateria($id)
        ];

        return $this->view->renderWithLayout('materias/edit', $data);
    }

    private function getMaterias()
    {
        return [
            ['id' => 1, 'nombre' => 'Matemáticas', 'codigo' => 'MAT101', 'nivel' => 'Básico', 'carga_horaria' => 4],
            ['id' => 2, 'nombre' => 'Física', 'codigo' => 'FIS101', 'nivel' => 'Básico', 'carga_horaria' => 3],
            ['id' => 3, 'nombre' => 'Química', 'codigo' => 'QUI101', 'nivel' => 'Básico', 'carga_horaria' => 3],
            ['id' => 4, 'nombre' => 'Programación', 'codigo' => 'PROG101', 'nivel' => 'Intermedio', 'carga_horaria' => 5],
            ['id' => 5, 'nombre' => 'Bases de Datos', 'codigo' => 'BD101', 'nivel' => 'Intermedio', 'carga_horaria' => 4]
        ];
    }

    private function getMateria($id)
    {
        $materias = $this->getMaterias();
        foreach ($materias as $materia) {
            if ($materia['id'] == $id) {
                return $materia;
            }
        }
        return null;
    }
}
