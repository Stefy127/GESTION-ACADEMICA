<?php
/**
 * Controlador para gestión de horarios
 */
class HorariosController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('horarios', null);

        $data = [
            'title' => 'Gestión de Horarios',
            'user' => $this->getCurrentUser(),
            'horarios' => $this->getHorarios()
        ];

        return $this->view->renderWithLayout('horarios/index', $data);
    }

    public function create()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Crear Horario',
            'user' => $this->getCurrentUser(),
            'grupos' => $this->getGrupos(),
            'aulas' => $this->getAulas(),
            'docentes' => $this->getDocentes()
        ];

        return $this->view->renderWithLayout('horarios/create', $data);
    }

    public function edit($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Editar Horario',
            'user' => $this->getCurrentUser(),
            'horario' => $this->getHorario($id),
            'grupos' => $this->getGrupos(),
            'aulas' => $this->getAulas(),
            'docentes' => $this->getDocentes()
        ];

        return $this->view->renderWithLayout('horarios/edit', $data);
    }

    private function getHorarios()
    {
        return [
            ['id' => 1, 'dia' => 'Lunes', 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'grupo' => 'A1', 'aula' => 'Aula 101', 'docente' => 'Juan Pérez'],
            ['id' => 2, 'dia' => 'Martes', 'hora_inicio' => '10:00', 'hora_fin' => '12:00', 'grupo' => 'A2', 'aula' => 'Aula 102', 'docente' => 'María González'],
            ['id' => 3, 'dia' => 'Miércoles', 'hora_inicio' => '14:00', 'hora_fin' => '16:00', 'grupo' => 'B1', 'aula' => 'Laboratorio 1', 'docente' => 'Carlos López'],
            ['id' => 4, 'dia' => 'Jueves', 'hora_inicio' => '16:00', 'hora_fin' => '18:00', 'grupo' => 'B2', 'aula' => 'Aula 201', 'docente' => 'Juan Pérez'],
            ['id' => 5, 'dia' => 'Viernes', 'hora_inicio' => '08:00', 'hora_fin' => '10:00', 'grupo' => 'C1', 'aula' => 'Laboratorio 2', 'docente' => 'María González']
        ];
    }

    private function getGrupos()
    {
        return [
            ['id' => 1, 'numero' => 'A1'],
            ['id' => 2, 'numero' => 'A2'],
            ['id' => 3, 'numero' => 'B1'],
            ['id' => 4, 'numero' => 'B2'],
            ['id' => 5, 'numero' => 'C1']
        ];
    }

    private function getAulas()
    {
        return [
            ['id' => 1, 'nombre' => 'Aula 101'],
            ['id' => 2, 'nombre' => 'Aula 102'],
            ['id' => 3, 'nombre' => 'Laboratorio 1'],
            ['id' => 4, 'nombre' => 'Laboratorio 2'],
            ['id' => 5, 'nombre' => 'Aula 201']
        ];
    }

    private function getDocentes()
    {
        return [
            ['id' => 1, 'nombre' => 'Juan Pérez'],
            ['id' => 2, 'nombre' => 'María González'],
            ['id' => 3, 'nombre' => 'Carlos López']
        ];
    }

    private function getHorario($id)
    {
        $horarios = $this->getHorarios();
        foreach ($horarios as $horario) {
            if ($horario['id'] == $id) {
                return $horario;
            }
        }
        return null;
    }
}
