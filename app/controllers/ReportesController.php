<?php
/**
 * Controlador para reportes
 */
class ReportesController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reportes del Sistema',
            'user' => $this->getCurrentUser(),
            'reportes' => $this->getReportesDisponibles()
        ];

        return $this->view->renderWithLayout('reportes/index', $data);
    }

    public function asistencia()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reporte de Asistencia',
            'user' => $this->getCurrentUser(),
            'datos' => $this->getDatosAsistencia()
        ];

        return $this->view->renderWithLayout('reportes/asistencia', $data);
    }

    public function horarios()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reporte de Horarios',
            'user' => $this->getCurrentUser(),
            'datos' => $this->getDatosHorarios()
        ];

        return $this->view->renderWithLayout('reportes/horarios', $data);
    }

    private function getReportesDisponibles()
    {
        return [
            ['id' => 1, 'nombre' => 'Reporte de Asistencia', 'descripcion' => 'Estadísticas de asistencia por docente y período', 'icono' => 'bi-check-circle'],
            ['id' => 2, 'nombre' => 'Reporte de Horarios', 'descripcion' => 'Distribución de horarios y ocupación de aulas', 'icono' => 'bi-calendar-week'],
            ['id' => 3, 'nombre' => 'Reporte de Docentes', 'descripcion' => 'Información de docentes y carga horaria', 'icono' => 'bi-person-badge'],
            ['id' => 4, 'nombre' => 'Reporte de Aulas', 'descripcion' => 'Uso y disponibilidad de aulas', 'icono' => 'bi-building']
        ];
    }

    private function getDatosAsistencia()
    {
        return [
            'total_clases' => 150,
            'asistencias' => 142,
            'ausencias' => 8,
            'porcentaje_asistencia' => 94.7,
            'por_docente' => [
                ['docente' => 'Juan Pérez', 'asistencias' => 45, 'ausencias' => 2, 'porcentaje' => 95.7],
                ['docente' => 'María González', 'asistencias' => 48, 'ausencias' => 1, 'porcentaje' => 98.0],
                ['docente' => 'Carlos López', 'asistencias' => 49, 'ausencias' => 5, 'porcentaje' => 90.7]
            ]
        ];
    }

    private function getDatosHorarios()
    {
        return [
            'total_horarios' => 25,
            'horarios_activos' => 23,
            'aulas_ocupadas' => 15,
            'aulas_disponibles' => 5,
            'por_dia' => [
                ['dia' => 'Lunes', 'horarios' => 5, 'ocupacion' => 100],
                ['dia' => 'Martes', 'horarios' => 4, 'ocupacion' => 80],
                ['dia' => 'Miércoles', 'horarios' => 5, 'ocupacion' => 100],
                ['dia' => 'Jueves', 'horarios' => 4, 'ocupacion' => 80],
                ['dia' => 'Viernes', 'horarios' => 5, 'ocupacion' => 100]
            ]
        ];
    }
}
