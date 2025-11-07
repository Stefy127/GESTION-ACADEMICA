<?php
/**
 * Controlador para reportes
 */
class ReportesController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        // Registrar acceso al módulo
        ActivityLogger::logView('reportes', null);

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

        $fechaInicio = $this->getGet('fecha_inicio') ?: date('Y-m-01');
        $fechaFin = $this->getGet('fecha_fin') ?: date('Y-m-t');
        $docenteId = $this->getGet('docente_id');

        $data = [
            'title' => 'Reporte de Asistencia',
            'user' => $this->getCurrentUser(),
            'datos' => $this->getDatosAsistencia($fechaInicio, $fechaFin, $docenteId),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'docente_id' => $docenteId,
            'docentes' => $this->getDocentes()
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

    public function docentes()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reporte de Docentes',
            'user' => $this->getCurrentUser(),
            'datos' => $this->getDatosDocentes()
        ];

        return $this->view->renderWithLayout('reportes/docentes', $data);
    }

    public function aulas()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }

        $data = [
            'title' => 'Reporte de Aulas',
            'user' => $this->getCurrentUser(),
            'datos' => $this->getDatosAulas()
        ];

        return $this->view->renderWithLayout('reportes/aulas', $data);
    }

    // Métodos de exportación
    public function exportarAsistencia()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $formato = $this->getGet('formato') ?: 'pdf';
        $fechaInicio = $this->getGet('fecha_inicio') ?: date('Y-m-01');
        $fechaFin = $this->getGet('fecha_fin') ?: date('Y-m-t');
        $docenteId = $this->getGet('docente_id');

        $datos = $this->getDatosAsistencia($fechaInicio, $fechaFin, $docenteId);

        if ($formato === 'xlsx') {
            $this->exportarAsistenciaXLSX($datos, $fechaInicio, $fechaFin);
        } else {
            $this->exportarAsistenciaPDF($datos, $fechaInicio, $fechaFin);
        }
    }

    public function exportarHorarios()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $formato = $this->getGet('formato') ?: 'pdf';
        $datos = $this->getDatosHorarios();

        if ($formato === 'xlsx') {
            $this->exportarHorariosXLSX($datos);
        } else {
            $this->exportarHorariosPDF($datos);
        }
    }

    public function exportarDocentes()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $formato = $this->getGet('formato') ?: 'pdf';
        $datos = $this->getDatosDocentes();

        if ($formato === 'xlsx') {
            $this->exportarDocentesXLSX($datos);
        } else {
            $this->exportarDocentesPDF($datos);
        }
    }

    public function exportarAulas()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador', 'autoridad'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $formato = $this->getGet('formato') ?: 'pdf';
        $datos = $this->getDatosAulas();

        if ($formato === 'xlsx') {
            $this->exportarAulasXLSX($datos);
        } else {
            $this->exportarAulasPDF($datos);
        }
    }

    // Métodos privados para obtener datos
    private function getReportesDisponibles()
    {
        return [
            ['id' => 1, 'nombre' => 'Reporte de Asistencia', 'descripcion' => 'Estadísticas de asistencia por docente y período', 'icono' => 'bi-check-circle', 'ruta' => 'asistencia'],
            ['id' => 2, 'nombre' => 'Reporte de Horarios', 'descripcion' => 'Distribución de horarios y ocupación de aulas', 'icono' => 'bi-calendar-week', 'ruta' => 'horarios'],
            ['id' => 3, 'nombre' => 'Reporte de Docentes', 'descripcion' => 'Información de docentes y carga horaria', 'icono' => 'bi-person-badge', 'ruta' => 'docentes'],
            ['id' => 4, 'nombre' => 'Reporte de Aulas', 'descripcion' => 'Uso y disponibilidad de aulas', 'icono' => 'bi-building', 'ruta' => 'aulas']
        ];
    }

    private function getDatosAsistencia($fechaInicio = null, $fechaFin = null, $docenteId = null)
    {
        $fechaInicio = $fechaInicio ?: date('Y-m-01');
        $fechaFin = $fechaFin ?: date('Y-m-t');

        $whereDocente = $docenteId ? "AND ad.docente_id = :docente_id" : "";

        // Obtener estadísticas generales
        $sql = "SELECT 
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN ad.estado = 'presente' THEN 1 ELSE 0 END) as asistencias,
                    SUM(CASE WHEN ad.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
                    SUM(CASE WHEN ad.estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
                    SUM(CASE WHEN ad.estado = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM asistencia_docente ad
                WHERE ad.fecha BETWEEN :fecha_inicio AND :fecha_fin $whereDocente";

        $params = [':fecha_inicio' => $fechaInicio, ':fecha_fin' => $fechaFin];
        if ($docenteId) {
            $params[':docente_id'] = $docenteId;
        }

        $stats = $this->db->query($sql, $params);
        $stats = $stats[0] ?? ['total_clases' => 0, 'asistencias' => 0, 'ausencias' => 0, 'tardanzas' => 0, 'justificados' => 0];

        $totalClases = (int)$stats['total_clases'];
        $asistencias = (int)$stats['asistencias'];
        $ausencias = (int)$stats['ausencias'];
        $porcentajeAsistencia = $totalClases > 0 ? round(($asistencias / $totalClases) * 100, 2) : 0;

        // Obtener datos por docente
        $sql = "SELECT 
                    u.id,
                    u.nombre || ' ' || u.apellido as docente,
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN ad.estado = 'presente' THEN 1 ELSE 0 END) as asistencias,
                    SUM(CASE WHEN ad.estado = 'ausente' THEN 1 ELSE 0 END) as ausencias,
                    SUM(CASE WHEN ad.estado = 'tardanza' THEN 1 ELSE 0 END) as tardanzas,
                    SUM(CASE WHEN ad.estado = 'justificado' THEN 1 ELSE 0 END) as justificados
                FROM asistencia_docente ad
                INNER JOIN usuarios u ON ad.docente_id = u.id
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE r.nombre = 'docente' 
                    AND ad.fecha BETWEEN :fecha_inicio AND :fecha_fin
                    " . ($docenteId ? "AND ad.docente_id = :docente_id" : "") . "
                GROUP BY u.id, u.nombre, u.apellido
                ORDER BY u.apellido, u.nombre";

        $porDocente = $this->db->query($sql, $params);
        
        foreach ($porDocente as &$docente) {
            $total = (int)$docente['total_clases'];
            $asist = (int)$docente['asistencias'];
            $docente['porcentaje'] = $total > 0 ? round(($asist / $total) * 100, 2) : 0;
        }

        return [
            'total_clases' => $totalClases,
            'asistencias' => $asistencias,
            'ausencias' => $ausencias,
            'tardanzas' => (int)$stats['tardanzas'],
            'justificados' => (int)$stats['justificados'],
            'porcentaje_asistencia' => $porcentajeAsistencia,
            'por_docente' => $porDocente
        ];
    }

    private function getDatosHorarios()
    {
        // Obtener estadísticas generales
        $sql = "SELECT 
                    COUNT(*) as total_horarios,
                    SUM(CASE WHEN h.activo = true THEN 1 ELSE 0 END) as horarios_activos,
                    COUNT(DISTINCT h.aula_id) as aulas_ocupadas
                FROM horarios h
                WHERE h.activo = true";

        $stats = $this->db->query($sql);
        $stats = $stats[0] ?? ['total_horarios' => 0, 'horarios_activos' => 0, 'aulas_ocupadas' => 0];

        $totalAulas = $this->db->query("SELECT COUNT(*) as total FROM aulas WHERE activa = true")[0]['total'] ?? 0;
        $aulasDisponibles = $totalAulas - (int)$stats['aulas_ocupadas'];

        // Obtener datos por día
        $sql = "SELECT 
                    h.dia_semana,
                    CASE h.dia_semana
                        WHEN 1 THEN 'Lunes'
                        WHEN 2 THEN 'Martes'
                        WHEN 3 THEN 'Miércoles'
                        WHEN 4 THEN 'Jueves'
                        WHEN 5 THEN 'Viernes'
                        WHEN 6 THEN 'Sábado'
                        WHEN 7 THEN 'Domingo'
                    END as dia,
                    COUNT(*) as horarios,
                    COUNT(DISTINCT h.aula_id) as aulas_ocupadas
                FROM horarios h
                WHERE h.activo = true
                GROUP BY h.dia_semana
                ORDER BY h.dia_semana";

        $porDia = $this->db->query($sql);
        
        foreach ($porDia as &$dia) {
            $horarios = (int)$dia['horarios'];
            $ocupacion = $totalAulas > 0 ? round((($dia['aulas_ocupadas'] / $totalAulas) * 100), 2) : 0;
            $dia['ocupacion'] = $ocupacion;
        }

        // Obtener distribución de aulas
        $sql = "SELECT 
                    a.id,
                    a.nombre,
                    a.codigo,
                    a.capacidad,
                    COUNT(h.id) as horarios_asignados
                FROM aulas a
                LEFT JOIN horarios h ON a.id = h.aula_id AND h.activo = true
                WHERE a.activa = true
                GROUP BY a.id, a.nombre, a.codigo, a.capacidad
                ORDER BY a.nombre";

        $distribucionAulas = $this->db->query($sql);

        return [
            'total_horarios' => (int)$stats['total_horarios'],
            'horarios_activos' => (int)$stats['horarios_activos'],
            'aulas_ocupadas' => (int)$stats['aulas_ocupadas'],
            'aulas_disponibles' => $aulasDisponibles,
            'total_aulas' => (int)$totalAulas,
            'por_dia' => $porDia,
            'distribucion_aulas' => $distribucionAulas
        ];
    }

    private function getDatosDocentes()
    {
        // Obtener todos los docentes con su información
        $sql = "SELECT 
                    u.id,
                    u.ci,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    di.titulo_profesional,
                    di.especialidad,
                    di.departamento,
                    di.anos_experiencia,
                    di.grado_academico,
                    di.universidad_egresado,
                    di.categoria,
                    di.dedicacion,
                    COUNT(DISTINCT h.id) as total_horarios,
                    COUNT(DISTINCT g.id) as total_grupos
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                LEFT JOIN docentes_info di ON u.id = di.usuario_id
                LEFT JOIN horarios h ON u.id = h.docente_id AND h.activo = true
                LEFT JOIN grupos g ON u.id = g.docente_id AND g.activo = true
                WHERE r.nombre = 'docente' AND u.activo = true
                GROUP BY u.id, u.ci, u.nombre, u.apellido, u.email, u.telefono,
                         di.titulo_profesional, di.especialidad, di.departamento,
                         di.anos_experiencia, di.grado_academico, di.universidad_egresado,
                         di.categoria, di.dedicacion
                ORDER BY u.apellido, u.nombre";

        $docentes = $this->db->query($sql);

        // Calcular carga horaria total por docente
        $sql = "SELECT 
                    h.docente_id,
                    COUNT(*) as horas_semanales
                FROM horarios h
                WHERE h.activo = true
                GROUP BY h.docente_id";

        $cargaHoraria = $this->db->query($sql);
        $cargaMap = [];
        foreach ($cargaHoraria as $carga) {
            $cargaMap[$carga['docente_id']] = (int)$carga['horas_semanales'];
        }

        foreach ($docentes as &$docente) {
            $docente['carga_horaria'] = $cargaMap[$docente['id']] ?? 0;
        }

        return [
            'total_docentes' => count($docentes),
            'docentes' => $docentes
        ];
    }

    private function getDatosAulas()
    {
        // Obtener todas las aulas con su uso
        $sql = "SELECT 
                    a.id,
                    a.nombre,
                    a.codigo,
                    a.capacidad,
                    a.tipo,
                    a.ubicacion,
                    COUNT(DISTINCT h.id) as horarios_asignados,
                    COUNT(DISTINCT h.dia_semana) as dias_ocupados,
                    COUNT(DISTINCT h.docente_id) as docentes_asignados
                FROM aulas a
                LEFT JOIN horarios h ON a.id = h.aula_id AND h.activo = true
                WHERE a.activa = true
                GROUP BY a.id, a.nombre, a.codigo, a.capacidad, a.tipo, a.ubicacion
                ORDER BY a.nombre";

        $aulas = $this->db->query($sql);

        $totalAulas = count($aulas);
        $aulasOcupadas = 0;
        $aulasDisponibles = 0;

        foreach ($aulas as &$aula) {
            $horariosAsignados = (int)$aula['horarios_asignados'];
            $aula['ocupada'] = $horariosAsignados > 0;
            $aula['uso_porcentaje'] = $horariosAsignados > 0 ? 100 : 0;
            
            if ($horariosAsignados > 0) {
                $aulasOcupadas++;
            } else {
                $aulasDisponibles++;
            }
        }

        return [
            'total_aulas' => $totalAulas,
            'aulas_ocupadas' => $aulasOcupadas,
            'aulas_disponibles' => $aulasDisponibles,
            'aulas' => $aulas
        ];
    }

    private function getDocentes()
    {
        $sql = "SELECT u.id, u.nombre || ' ' || u.apellido as nombre
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE r.nombre = 'docente' AND u.activo = true
                ORDER BY u.apellido, u.nombre";
        return $this->db->query($sql);
    }

    // Métodos de exportación PDF
    private function exportarAsistenciaPDF($datos, $fechaInicio, $fechaFin)
    {
        $html = $this->generarHTMLAsistencia($datos, $fechaInicio, $fechaFin);
        $this->descargarPDF('reporte-asistencia-' . date('Y-m-d') . '.pdf', $html);
    }

    private function exportarHorariosPDF($datos)
    {
        $html = $this->generarHTMLHorarios($datos);
        $this->descargarPDF('reporte-horarios-' . date('Y-m-d') . '.pdf', $html);
    }

    private function exportarDocentesPDF($datos)
    {
        $html = $this->generarHTMLDocentes($datos);
        $this->descargarPDF('reporte-docentes-' . date('Y-m-d') . '.pdf', $html);
    }

    private function exportarAulasPDF($datos)
    {
        $html = $this->generarHTMLAulas($datos);
        $this->descargarPDF('reporte-aulas-' . date('Y-m-d') . '.pdf', $html);
    }

    // Métodos de exportación XLSX (CSV)
    private function exportarAsistenciaXLSX($datos, $fechaInicio, $fechaFin)
    {
        $csv = [];
        
        // Encabezados de columnas
        $csv[] = "Docente,Total Clases,Asistencias,Ausencias,Tardanzas,Justificados,Porcentaje Asistencia";
        
        // Datos por docente
        foreach ($datos['por_docente'] as $docente) {
            $docenteNombre = $this->limpiarCSV($docente['docente']);
            $csv[] = sprintf('"%s",%d,%d,%d,%d,%d,%.2f',
                $docenteNombre,
                $docente['total_clases'],
                $docente['asistencias'],
                $docente['ausencias'],
                $docente['tardanzas'] ?? 0,
                $docente['justificados'] ?? 0,
                $docente['porcentaje']
            );
        }

        $this->descargarCSV('reporte-asistencia-' . date('Y-m-d') . '.csv', implode("\n", $csv));
    }

    private function exportarHorariosXLSX($datos)
    {
        $csv = [];
        
        // Encabezados de columnas - Ocupación por Día
        $csv[] = "Día,Horarios,Aulas Ocupadas,Ocupación (%)";
        
        // Datos por día
        foreach ($datos['por_dia'] as $dia) {
            $diaNombre = $this->limpiarCSV($dia['dia']);
            $csv[] = sprintf('"%s",%d,%d,%.2f',
                $diaNombre,
                $dia['horarios'],
                $dia['aulas_ocupadas'],
                $dia['ocupacion']
            );
        }

        // Línea en blanco para separar secciones
        $csv[] = "";
        
        // Encabezados de columnas - Distribución de Aulas
        $csv[] = "Aula,Código,Capacidad,Horarios Asignados";
        
        // Datos de aulas
        foreach ($datos['distribucion_aulas'] as $aula) {
            $aulaNombre = $this->limpiarCSV($aula['nombre']);
            $aulaCodigo = $this->limpiarCSV($aula['codigo']);
            $csv[] = sprintf('"%s","%s",%d,%d',
                $aulaNombre,
                $aulaCodigo,
                $aula['capacidad'],
                $aula['horarios_asignados']
            );
        }

        $this->descargarCSV('reporte-horarios-' . date('Y-m-d') . '.csv', implode("\n", $csv));
    }

    private function exportarDocentesXLSX($datos)
    {
        $csv = [];
        
        // Encabezados de columnas
        $csv[] = "CI,Nombre,Apellido,Email,Teléfono,Título Profesional,Especialidad,Departamento,Años Experiencia,Grado Académico,Universidad,Categoría,Dedicación,Total Horarios,Total Grupos,Carga Horaria";
        
        // Datos de docentes
        foreach ($datos['docentes'] as $docente) {
            $csv[] = sprintf('"%s","%s","%s","%s","%s","%s","%s","%s",%d,"%s","%s","%s","%s",%d,%d,%d',
                $this->limpiarCSV($docente['ci'] ?? ''),
                $this->limpiarCSV($docente['nombre'] ?? ''),
                $this->limpiarCSV($docente['apellido'] ?? ''),
                $this->limpiarCSV($docente['email'] ?? ''),
                $this->limpiarCSV($docente['telefono'] ?? ''),
                $this->limpiarCSV($docente['titulo_profesional'] ?? ''),
                $this->limpiarCSV($docente['especialidad'] ?? ''),
                $this->limpiarCSV($docente['departamento'] ?? ''),
                $docente['anos_experiencia'] ?? 0,
                $this->limpiarCSV($docente['grado_academico'] ?? ''),
                $this->limpiarCSV($docente['universidad_egresado'] ?? ''),
                $this->limpiarCSV($docente['categoria'] ?? ''),
                $this->limpiarCSV($docente['dedicacion'] ?? ''),
                $docente['total_horarios'] ?? 0,
                $docente['total_grupos'] ?? 0,
                $docente['carga_horaria'] ?? 0
            );
        }

        $this->descargarCSV('reporte-docentes-' . date('Y-m-d') . '.csv', implode("\n", $csv));
    }

    private function exportarAulasXLSX($datos)
    {
        $csv = [];
        
        // Encabezados de columnas
        $csv[] = "Nombre,Código,Capacidad,Tipo,Ubicación,Horarios Asignados,Días Ocupados,Docentes Asignados,Estado";
        
        // Datos de aulas
        foreach ($datos['aulas'] as $aula) {
            $csv[] = sprintf('"%s","%s",%d,"%s","%s",%d,%d,%d,"%s"',
                $this->limpiarCSV($aula['nombre']),
                $this->limpiarCSV($aula['codigo']),
                $aula['capacidad'],
                $this->limpiarCSV($aula['tipo'] ?? ''),
                $this->limpiarCSV($aula['ubicacion'] ?? ''),
                $aula['horarios_asignados'],
                $aula['dias_ocupados'],
                $aula['docentes_asignados'],
                $aula['ocupada'] ? 'Ocupada' : 'Disponible'
            );
        }

        $this->descargarCSV('reporte-aulas-' . date('Y-m-d') . '.csv', implode("\n", $csv));
    }

    // Métodos auxiliares para generar HTML y descargar
    private function generarHTMLAsistencia($datos, $fechaInicio, $fechaFin)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de Asistencia</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;margin-top:20px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#4CAF50;color:white;}</style></head><body>';
        $html .= '<h1>Reporte de Asistencia</h1>';
        $html .= '<p><strong>Período:</strong> ' . $fechaInicio . ' - ' . $fechaFin . '</p>';
        $html .= '<h2>Resumen General</h2>';
        $html .= '<table><tr><th>Métrica</th><th>Valor</th></tr>';
        $html .= '<tr><td>Total Clases</td><td>' . $datos['total_clases'] . '</td></tr>';
        $html .= '<tr><td>Asistencias</td><td>' . $datos['asistencias'] . '</td></tr>';
        $html .= '<tr><td>Ausencias</td><td>' . $datos['ausencias'] . '</td></tr>';
        $html .= '<tr><td>Tardanzas</td><td>' . $datos['tardanzas'] . '</td></tr>';
        $html .= '<tr><td>Justificados</td><td>' . $datos['justificados'] . '</td></tr>';
        $html .= '<tr><td>Porcentaje Asistencia</td><td>' . $datos['porcentaje_asistencia'] . '%</td></tr>';
        $html .= '</table>';
        $html .= '<h2>Detalle por Docente</h2>';
        $html .= '<table><tr><th>Docente</th><th>Total Clases</th><th>Asistencias</th><th>Ausencias</th><th>Tardanzas</th><th>Justificados</th><th>Porcentaje</th></tr>';
        foreach ($datos['por_docente'] as $docente) {
            $html .= '<tr><td>' . htmlspecialchars($docente['docente']) . '</td>';
            $html .= '<td>' . $docente['total_clases'] . '</td>';
            $html .= '<td>' . $docente['asistencias'] . '</td>';
            $html .= '<td>' . $docente['ausencias'] . '</td>';
            $html .= '<td>' . ($docente['tardanzas'] ?? 0) . '</td>';
            $html .= '<td>' . ($docente['justificados'] ?? 0) . '</td>';
            $html .= '<td>' . $docente['porcentaje'] . '%</td></tr>';
        }
        $html .= '</table></body></html>';
        return $html;
    }

    private function generarHTMLHorarios($datos)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de Horarios</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;margin-top:20px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#2196F3;color:white;}</style></head><body>';
        $html .= '<h1>Reporte de Horarios</h1>';
        $html .= '<h2>Resumen General</h2>';
        $html .= '<table><tr><th>Métrica</th><th>Valor</th></tr>';
        $html .= '<tr><td>Total Horarios</td><td>' . $datos['total_horarios'] . '</td></tr>';
        $html .= '<tr><td>Horarios Activos</td><td>' . $datos['horarios_activos'] . '</td></tr>';
        $html .= '<tr><td>Aulas Ocupadas</td><td>' . $datos['aulas_ocupadas'] . '</td></tr>';
        $html .= '<tr><td>Aulas Disponibles</td><td>' . $datos['aulas_disponibles'] . '</td></tr>';
        $html .= '</table>';
        $html .= '<h2>Ocupación por Día</h2>';
        $html .= '<table><tr><th>Día</th><th>Horarios</th><th>Aulas Ocupadas</th><th>Ocupación (%)</th></tr>';
        foreach ($datos['por_dia'] as $dia) {
            $html .= '<tr><td>' . $dia['dia'] . '</td>';
            $html .= '<td>' . $dia['horarios'] . '</td>';
            $html .= '<td>' . $dia['aulas_ocupadas'] . '</td>';
            $html .= '<td>' . $dia['ocupacion'] . '%</td></tr>';
        }
        $html .= '</table>';
        $html .= '<h2>Distribución de Aulas</h2>';
        $html .= '<table><tr><th>Aula</th><th>Código</th><th>Capacidad</th><th>Horarios Asignados</th></tr>';
        foreach ($datos['distribucion_aulas'] as $aula) {
            $html .= '<tr><td>' . htmlspecialchars($aula['nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($aula['codigo']) . '</td>';
            $html .= '<td>' . $aula['capacidad'] . '</td>';
            $html .= '<td>' . $aula['horarios_asignados'] . '</td></tr>';
        }
        $html .= '</table></body></html>';
        return $html;
    }

    private function generarHTMLDocentes($datos)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de Docentes</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;margin-top:20px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#FF9800;color:white;}</style></head><body>';
        $html .= '<h1>Reporte de Docentes</h1>';
        $html .= '<p><strong>Total Docentes:</strong> ' . $datos['total_docentes'] . '</p>';
        $html .= '<table><tr><th>CI</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Título</th><th>Especialidad</th><th>Departamento</th><th>Años Exp.</th><th>Total Horarios</th><th>Total Grupos</th><th>Carga Horaria</th></tr>';
        foreach ($datos['docentes'] as $docente) {
            $html .= '<tr><td>' . htmlspecialchars($docente['ci'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['nombre'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['apellido'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['email'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['titulo_profesional'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['especialidad'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($docente['departamento'] ?? '') . '</td>';
            $html .= '<td>' . ($docente['anos_experiencia'] ?? 0) . '</td>';
            $html .= '<td>' . ($docente['total_horarios'] ?? 0) . '</td>';
            $html .= '<td>' . ($docente['total_grupos'] ?? 0) . '</td>';
            $html .= '<td>' . ($docente['carga_horaria'] ?? 0) . '</td></tr>';
        }
        $html .= '</table></body></html>';
        return $html;
    }

    private function generarHTMLAulas($datos)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte de Aulas</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;margin-top:20px;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background-color:#9C27B0;color:white;}</style></head><body>';
        $html .= '<h1>Reporte de Aulas</h1>';
        $html .= '<h2>Resumen General</h2>';
        $html .= '<table><tr><th>Métrica</th><th>Valor</th></tr>';
        $html .= '<tr><td>Total Aulas</td><td>' . $datos['total_aulas'] . '</td></tr>';
        $html .= '<tr><td>Aulas Ocupadas</td><td>' . $datos['aulas_ocupadas'] . '</td></tr>';
        $html .= '<tr><td>Aulas Disponibles</td><td>' . $datos['aulas_disponibles'] . '</td></tr>';
        $html .= '</table>';
        $html .= '<h2>Detalle de Aulas</h2>';
        $html .= '<table><tr><th>Nombre</th><th>Código</th><th>Capacidad</th><th>Tipo</th><th>Ubicación</th><th>Horarios</th><th>Días Ocupados</th><th>Docentes</th><th>Estado</th></tr>';
        foreach ($datos['aulas'] as $aula) {
            $html .= '<tr><td>' . htmlspecialchars($aula['nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($aula['codigo']) . '</td>';
            $html .= '<td>' . $aula['capacidad'] . '</td>';
            $html .= '<td>' . htmlspecialchars($aula['tipo'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($aula['ubicacion'] ?? '') . '</td>';
            $html .= '<td>' . $aula['horarios_asignados'] . '</td>';
            $html .= '<td>' . $aula['dias_ocupados'] . '</td>';
            $html .= '<td>' . $aula['docentes_asignados'] . '</td>';
            $html .= '<td>' . ($aula['ocupada'] ? 'Ocupada' : 'Disponible') . '</td></tr>';
        }
        $html .= '</table></body></html>';
        return $html;
    }

    private function descargarPDF($nombreArchivo, $html)
    {
        // Generar PDF usando TCPDF si está disponible, sino mostrar HTML para imprimir
        if (class_exists('TCPDF')) {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('Sistema de Gestión Académica');
            $pdf->SetAuthor('Sistema de Gestión Académica');
            $pdf->SetTitle($nombreArchivo);
            $pdf->SetSubject('Reporte del Sistema');
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output($nombreArchivo, 'D');
            exit;
        } else {
            // Si TCPDF no está disponible, enviar HTML para imprimir como PDF
            // Esto permite al usuario usar "Guardar como PDF" del navegador
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            echo '<script>
                window.onload = function() {
                    window.print();
                };
            </script>';
            exit;
        }
    }

    private function descargarCSV($nombreArchivo, $contenido)
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Agregar BOM para UTF-8 (Excel)
        echo "\xEF\xBB\xBF";
        echo $contenido;
        exit;
    }

    /**
     * Limpiar texto para CSV (remover caracteres problemáticos)
     */
    private function limpiarCSV($texto)
    {
        if (empty($texto)) {
            return '';
        }
        
        // Remover saltos de línea y caracteres problemáticos
        $texto = str_replace(["\r\n", "\r", "\n"], " ", $texto);
        $texto = str_replace('"', '""', $texto); // Escapar comillas dobles
        $texto = trim($texto);
        
        return $texto;
    }
}
