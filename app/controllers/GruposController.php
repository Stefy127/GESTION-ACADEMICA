<?php
/**
 * Controlador para gestión de grupos
 */
class GruposController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Gestión de Grupos',
            'user' => $this->getCurrentUser(),
            'grupos' => $this->getGrupos()
        ];

        return $this->view->renderWithLayout('grupos/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Grupo',
            'user' => $this->getCurrentUser(),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('grupos/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Grupo',
            'user' => $this->getCurrentUser(),
            'grupo' => $this->getGrupo($id),
            'materias' => $this->getMaterias()
        ];

        return $this->view->renderWithLayout('grupos/edit', $data);
    }

    private function getGrupos()
    {
        return [
            ['id' => 1, 'numero' => 'A1', 'semestre' => 'Primero', 'turno' => 'Mañana', 'materia' => 'Matemáticas'],
            ['id' => 2, 'numero' => 'A2', 'semestre' => 'Primero', 'turno' => 'Tarde', 'materia' => 'Física'],
            ['id' => 3, 'numero' => 'B1', 'semestre' => 'Segundo', 'turno' => 'Mañana', 'materia' => 'Química'],
            ['id' => 4, 'numero' => 'B2', 'semestre' => 'Segundo', 'turno' => 'Tarde', 'materia' => 'Programación'],
            ['id' => 5, 'numero' => 'C1', 'semestre' => 'Tercero', 'turno' => 'Mañana', 'materia' => 'Bases de Datos']
        ];
    }

    private function getMaterias()
    {
        return [
            ['id' => 1, 'nombre' => 'Matemáticas'],
            ['id' => 2, 'nombre' => 'Física'],
            ['id' => 3, 'nombre' => 'Química'],
            ['id' => 4, 'nombre' => 'Programación'],
            ['id' => 5, 'nombre' => 'Bases de Datos']
        ];
    }

    private function getGrupo($id)
    {
        $grupos = $this->getGrupos();
        foreach ($grupos as $grupo) {
            if ($grupo['id'] == $id) {
                return $grupo;
            }
        }
        return null;
    }
}
