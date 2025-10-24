<?php
/**
 * Controlador para gestión de asistencia
 */
class AsistenciaController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Control de Asistencia',
            'user' => $this->getCurrentUser(),
            'asistencias' => $this->getAsistencias()
        ];

        return $this->view->renderWithLayout('asistencia/index', $data);
    }

    public function registrar()
    {
        $data = [
            'title' => 'Registrar Asistencia',
            'user' => $this->getCurrentUser(),
            'horarios' => $this->getHorariosUsuario()
        ];

        return $this->view->renderWithLayout('asistencia/registrar', $data);
    }

    public function reportes()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reportes de Asistencia',
            'user' => $this->getCurrentUser(),
            'reportes' => $this->getReportes()
        ];

        return $this->view->renderWithLayout('asistencia/reportes', $data);
    }

    private function getAsistencias()
    {
        return [
            ['id' => 1, 'fecha' => '2025-10-23', 'hora' => '08:00', 'docente' => 'Juan Pérez', 'materia' => 'Matemáticas', 'grupo' => 'A1', 'asistio' => true],
            ['id' => 2, 'fecha' => '2025-10-23', 'hora' => '10:00', 'docente' => 'María González', 'materia' => 'Física', 'grupo' => 'A2', 'asistio' => true],
            ['id' => 3, 'fecha' => '2025-10-23', 'hora' => '14:00', 'docente' => 'Carlos López', 'materia' => 'Química', 'grupo' => 'B1', 'asistio' => false]
        ];
    }

    private function getHorariosUsuario()
    {
        $user = $this->getCurrentUser();
        
        if ($user['rol'] === 'docente') {
            return [
                ['id' => 1, 'dia' => 'Lunes', 'hora' => '08:00-10:00', 'materia' => 'Matemáticas', 'grupo' => 'A1'],
                ['id' => 2, 'dia' => 'Miércoles', 'hora' => '14:00-16:00', 'materia' => 'Física', 'grupo' => 'A2']
            ];
        }
        
        return [];
    }

    private function getReportes()
    {
        return [
            ['docente' => 'Juan Pérez', 'total_clases' => 40, 'asistencias' => 38, 'ausencias' => 2, 'porcentaje' => 95.0],
            ['docente' => 'María González', 'total_clases' => 40, 'asistencias' => 40, 'ausencias' => 0, 'porcentaje' => 100.0],
            ['docente' => 'Carlos López', 'total_clases' => 40, 'asistencias' => 37, 'ausencias' => 3, 'porcentaje' => 92.5]
        ];
    }
}
