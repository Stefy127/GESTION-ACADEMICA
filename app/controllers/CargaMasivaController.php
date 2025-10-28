<?php
/**
 * Controlador para carga masiva
 */
class CargaMasivaController extends Controller
{
    public function index()
    {
        if (!Middleware::checkRole(['administrador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('carga_masiva', null);

        $data = [
            'title' => 'Carga Masiva de Datos',
            'user' => $this->getCurrentUser(),
            'tipos_carga' => $this->getTiposCarga()
        ];

        return $this->view->renderWithLayout('carga-masiva/index', $data);
    }

    public function procesar()
    {
        if (!Middleware::checkRole(['administrador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Procesar Archivo',
            'user' => $this->getCurrentUser()
        ];

        return $this->view->renderWithLayout('carga-masiva/procesar', $data);
    }

    private function getTiposCarga()
    {
        return [
            ['id' => 1, 'nombre' => 'Docentes', 'descripcion' => 'Cargar lista de docentes desde Excel/CSV', 'icono' => 'bi-person-badge'],
            ['id' => 2, 'nombre' => 'Materias', 'descripcion' => 'Cargar materias y sus códigos', 'icono' => 'bi-book'],
            ['id' => 3, 'nombre' => 'Grupos', 'descripcion' => 'Cargar grupos y asignaciones', 'icono' => 'bi-collection'],
            ['id' => 4, 'nombre' => 'Aulas', 'descripcion' => 'Cargar información de aulas', 'icono' => 'bi-building'],
            ['id' => 5, 'nombre' => 'Horarios', 'descripcion' => 'Cargar horarios completos', 'icono' => 'bi-calendar-week']
        ];
    }
}
