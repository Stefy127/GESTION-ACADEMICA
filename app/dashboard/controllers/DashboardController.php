<?php
/**
 * Controlador del Dashboard
 */
class DashboardController extends Controller
{
    private $middleware;
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->middleware = new Middleware();
        $this->db = Database::getInstance();
    }
    
    /**
     * Dashboard principal
     */
    public function index()
    {
        $this->middleware->requireAuth();
        
        $user = $this->middleware->getCurrentUser();
        
        // Registrar acceso al dashboard
        ActivityLogger::logView('dashboard', null);
        
        $stats = $this->getDashboardStats($user);
        
        // Si es docente, obtener clases y horarios
        $clases = [];
        $horarios = [];
        if ($user['rol'] === 'docente') {
            $clases = $this->getClasesDocente($user['id']);
            $horarios = $this->getHorariosDocente($user['id']);
        }
        
        // Verificar si necesita cambiar contraseña
        $needsPasswordChange = isset($user['needs_password_change']) && $user['needs_password_change'];
        
        $data = [
            'title' => 'Dashboard',
            'user' => $user,
            'stats' => $stats,
            'clases' => $clases,
            'horarios' => $horarios,
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];
        
        // Agregar flag de cambio de contraseña al usuario para la vista
        if ($needsPasswordChange) {
            $data['user']['needs_password_change'] = true;
        }
        
        return $this->view->renderWithLayout('dashboard/index', $data);
    }
    
    
    /**
     * Obtener estadísticas del dashboard
     */
    private function getDashboardStats($user)
    {
        $stats = [];
        
        switch ($user['rol']) {
            case 'administrador':
                $stats = $this->getAdminStats();
                break;
            case 'coordinador':
                $stats = $this->getCoordinatorStats();
                break;
            case 'docente':
                $stats = $this->getTeacherStats($user['id']);
                break;
            case 'autoridad':
                $stats = $this->getAuthorityStats();
                break;
        }
        
        return $stats;
    }
    
    /**
     * Estadísticas para administrador
     */
    private function getAdminStats()
    {
        $stats = [];
        
        // Total de usuarios
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_usuarios'] = $result[0]['total'];
        
        // Total de docentes
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Total de materias
        $sql = "SELECT COUNT(*) as total FROM materias WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_materias'] = $result[0]['total'];
        
        // Total de grupos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Total de aulas
        $sql = "SELECT COUNT(*) as total FROM aulas WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_aulas'] = $result[0]['total'];
        
        // Horarios activos
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['horarios_activos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para coordinador
     */
    private function getCoordinatorStats()
    {
        $stats = [];
        
        // Docentes activos
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Materias activas
        $sql = "SELECT COUNT(*) as total FROM materias WHERE activa = true";
        $result = $this->db->query($sql);
        $stats['total_materias'] = $result[0]['total'];
        
        // Grupos activos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Horarios activos
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['horarios_activos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para docente
     */
    private function getTeacherStats($userId)
    {
        $stats = [];
        
        // Grupos asignados
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE docente_id = :user_id AND activo = true";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['grupos_asignados'] = $result[0]['total'];
        
        // Horarios asignados
        $sql = "SELECT COUNT(*) as total FROM horarios WHERE docente_id = :user_id AND activo = true";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['horarios_asignados'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                WHERE docente_id = :user_id AND fecha = CURRENT_DATE";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        // Asistencia de la semana
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                WHERE docente_id = :user_id AND fecha >= CURRENT_DATE - INTERVAL '7 days'";
        $result = $this->db->query($sql, ['user_id' => $userId]);
        $stats['asistencia_semana'] = $result[0]['total'];
        
        return $stats;
    }
    
    /**
     * Estadísticas para autoridad
     */
    private function getAuthorityStats()
    {
        $stats = [];
        
        // Total de docentes
        $sql = "SELECT COUNT(*) as total FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE r.nombre = 'docente' AND u.activo = true";
        $result = $this->db->query($sql);
        $stats['total_docentes'] = $result[0]['total'];
        
        // Total de grupos
        $sql = "SELECT COUNT(*) as total FROM grupos WHERE activo = true";
        $result = $this->db->query($sql);
        $stats['total_grupos'] = $result[0]['total'];
        
        // Asistencia del día
        $sql = "SELECT COUNT(*) as total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
        $result = $this->db->query($sql);
        $stats['asistencia_hoy'] = $result[0]['total'];
        
        // Porcentaje de asistencia del mes
        $sql = "SELECT 
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as clases_presentes
                FROM asistencia_docente 
                WHERE fecha >= DATE_TRUNC('month', CURRENT_DATE)";
        $result = $this->db->query($sql);
        
        if ($result[0]['total_clases'] > 0) {
            $stats['porcentaje_asistencia'] = round(
                ($result[0]['clases_presentes'] / $result[0]['total_clases']) * 100, 2
            );
        } else {
            $stats['porcentaje_asistencia'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Obtener datos para gráficos
     */
    public function getChartData()
    {
        $this->middleware->requireAuth();
        
        $user = $this->middleware->getCurrentUser();
        $chartType = $this->getGet('type');
        
        $data = [];
        
        switch ($chartType) {
            case 'asistencia_mensual':
                $data = $this->getAsistenciaMensualData($user);
                break;
            case 'horarios_por_dia':
                $data = $this->getHorariosPorDiaData($user);
                break;
            case 'aulas_ocupacion':
                $data = $this->getAulasOcupacionData();
                break;
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Datos de asistencia mensual con porcentajes
     */
    private function getAsistenciaMensualData($user)
    {
        // Obtener datos agregados de los últimos 30 días
        $sql = "SELECT 
                    COALESCE(COUNT(*), 0) as total_clases,
                    COALESCE(SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END), 0) as clases_presentes,
                    COALESCE(SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END), 0) as clases_tardanza,
                    COALESCE(SUM(CASE WHEN estado = 'asistido_tarde' THEN 1 ELSE 0 END), 0) as clases_asistido_tarde,
                    COALESCE(SUM(CASE WHEN estado = 'incumplido' THEN 1 ELSE 0 END), 0) as clases_incumplido
                FROM asistencia_docente 
                WHERE fecha >= CURRENT_DATE - INTERVAL '30 days'";
        
        if ($user['rol'] === 'docente') {
            $sql .= " AND docente_id = :user_id";
            $result = $this->db->query($sql, ['user_id' => $user['id']]);
        } else {
            $result = $this->db->query($sql);
        }
        
        // Manejar valores NULL de PostgreSQL
        $total = (int)($result[0]['total_clases'] ?? 0);
        $presentes = (int)($result[0]['clases_presentes'] ?? 0);
        $tardanzas = (int)($result[0]['clases_tardanza'] ?? 0);
        $asistidoTarde = (int)($result[0]['clases_asistido_tarde'] ?? 0);
        $incumplidos = (int)($result[0]['clases_incumplido'] ?? 0);
        
        // Log para debugging
        error_log("Asistencia Mensual - Total: $total, Presentes: $presentes, Tardanzas: $tardanzas, Asistido Tarde: $asistidoTarde, Incumplidos: $incumplidos");
        
        // Calcular porcentajes
        $porcentajePresente = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
        $porcentajeTardanza = $total > 0 ? round(($tardanzas / $total) * 100, 1) : 0;
        $porcentajeAsistidoTarde = $total > 0 ? round(($asistidoTarde / $total) * 100, 1) : 0;
        $porcentajeIncumplido = $total > 0 ? round(($incumplidos / $total) * 100, 1) : 0;
        
        // Datos para gráfico por día
        $sqlDiario = "SELECT 
                    DATE_TRUNC('day', fecha) as dia,
                    COALESCE(COUNT(*), 0) as total_clases,
                    COALESCE(SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END), 0) as clases_presentes,
                    COALESCE(SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END), 0) as clases_tardanza,
                    COALESCE(SUM(CASE WHEN estado = 'asistido_tarde' THEN 1 ELSE 0 END), 0) as clases_asistido_tarde,
                    COALESCE(SUM(CASE WHEN estado = 'incumplido' THEN 1 ELSE 0 END), 0) as clases_incumplido
                FROM asistencia_docente 
                WHERE fecha >= CURRENT_DATE - INTERVAL '30 days'";
        
        if ($user['rol'] === 'docente') {
            $sqlDiario .= " AND docente_id = :user_id";
            $resultDiario = $this->db->query($sqlDiario, ['user_id' => $user['id']]);
        } else {
            $resultDiario = $this->db->query($sqlDiario);
        }
        
        // Log para debugging
        error_log("Asistencia Mensual - SQL ejecutado, resultados: " . count($resultDiario) . " filas");
        
        $labels = [];
        $datasets = [
            'total' => [],
            'presentes' => []
        ];
        
        foreach ($resultDiario as $row) {
            // PostgreSQL devuelve DATE_TRUNC como string, convertir a DateTime
            $diaStr = $row['dia'];
            if (is_string($diaStr)) {
                // Intentar diferentes formatos de fecha
                $dia = DateTime::createFromFormat('Y-m-d', substr($diaStr, 0, 10));
                if (!$dia) {
                    $dia = DateTime::createFromFormat('Y-m-d H:i:s', substr($diaStr, 0, 19));
                }
                if (!$dia) {
                    $dia = new DateTime($diaStr);
                }
            } else {
                $dia = new DateTime();
            }
            if ($dia) {
                $labels[] = $dia->format('d/m');
                $datasets['total'][] = (int)($row['total_clases'] ?? 0);
                $datasets['presentes'][] = (int)($row['clases_presentes'] ?? 0);
            }
        }
        
        // Log para debugging
        error_log("Asistencia Mensual Diaria - Labels: " . count($labels) . ", Total: " . count($datasets['total']));
        
        // Asegurar que siempre haya al menos un label y dato para el gráfico
        if (empty($labels)) {
            $labels = [];
            $datasets['total'] = [];
            $datasets['presentes'] = [];
        }
        
        $response = [
            'labels' => $labels,
            'datasets' => $datasets,
            'resumen' => [
                'total' => (int)$total,
                'presentes' => (int)$presentes,
                'tardanzas' => (int)$tardanzas,
                'asistido_tarde' => (int)$asistidoTarde,
                'incumplidos' => (int)$incumplidos,
                'porcentajes' => [
                    'presente' => (float)$porcentajePresente,
                    'tardanza' => (float)$porcentajeTardanza,
                    'asistido_tarde' => (float)$porcentajeAsistidoTarde,
                    'incumplido' => (float)$porcentajeIncumplido
                ]
            ]
        ];
        
        // Log para debugging
        error_log("Asistencia Mensual Response: " . json_encode($response['resumen']));
        
        return $response;
    }
    
    /**
     * Obtener datos de asistencia para reporte (método anterior simplificado)
     */
    private function getAsistenciaMensualDataOld($user)
    {
        $sql = "SELECT 
                    DATE_TRUNC('day', fecha) as dia,
                    COUNT(*) as total_clases,
                    SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as clases_presentes
                FROM asistencia_docente 
                WHERE fecha >= CURRENT_DATE - INTERVAL '30 days'";
        
        if ($user['rol'] === 'docente') {
            $sql .= " AND docente_id = :user_id";
            $result = $this->db->query($sql, ['user_id' => $user['id']]);
        } else {
            $result = $this->db->query($sql);
        }
        
        $labels = [];
        $datasets = [
            'total' => [],
            'presentes' => []
        ];
        
        foreach ($result as $row) {
            $labels[] = date('d/m', strtotime($row['dia']));
            $datasets['total'][] = (int)$row['total_clases'];
            $datasets['presentes'][] = (int)$row['clases_presentes'];
        }
        
        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }
    
    /**
     * Datos de horarios por día
     */
    private function getHorariosPorDiaData($user)
    {
        $sql = "SELECT 
                    CASE dia_semana
                        WHEN 1 THEN 'Lunes'
                        WHEN 2 THEN 'Martes'
                        WHEN 3 THEN 'Miércoles'
                        WHEN 4 THEN 'Jueves'
                        WHEN 5 THEN 'Viernes'
                        WHEN 6 THEN 'Sábado'
                        WHEN 7 THEN 'Domingo'
                    END as dia,
                    COUNT(*) as total_horarios
                FROM horarios 
                WHERE activo = true";
        
        if ($user['rol'] === 'docente') {
            $sql .= " AND docente_id = :user_id";
            $params = ['user_id' => $user['id']];
        } else {
            $params = [];
        }
        
        $sql .= " GROUP BY dia_semana ORDER BY dia_semana";
        
        $result = $this->db->query($sql, $params);
        
        $labels = [];
        $data = [];
        
        foreach ($result as $row) {
            $labels[] = $row['dia'];
            $data[] = (int)$row['total_horarios'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Datos de ocupación de aulas
     */
    private function getAulasOcupacionData()
    {
        $sql = "SELECT 
                    a.nombre,
                    COUNT(h.id) as horarios_asignados,
                    a.capacidad
                FROM aulas a
                LEFT JOIN horarios h ON a.id = h.aula_id AND h.activo = true
                WHERE a.activa = true
                GROUP BY a.id, a.nombre, a.capacidad
                ORDER BY a.nombre";
        
        $result = $this->db->query($sql);
        
        $labels = [];
        $data = [];
        
        foreach ($result as $row) {
            $labels[] = $row['nombre'];
            $data[] = (int)$row['horarios_asignados'];
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Obtener clases asignadas al docente
     */
    private function getClasesDocente($docenteId)
    {
        try {
            $sql = "SELECT DISTINCT
                        g.id as grupo_id,
                        g.numero as grupo_numero,
                        g.semestre,
                        g.turno,
                        m.id as materia_id,
                        m.codigo as materia_codigo,
                        m.nombre as materia_nombre,
                        m.descripcion as materia_descripcion,
                        COUNT(DISTINCT h.id) as total_horarios
                    FROM grupos g
                    INNER JOIN materias m ON g.materia_id = m.id
                    LEFT JOIN horarios h ON g.id = h.grupo_id AND h.activo = true
                    WHERE g.docente_id = :docente_id AND g.activo = true
                    GROUP BY g.id, g.numero, g.semestre, g.turno, m.id, m.codigo, m.nombre, m.descripcion
                    ORDER BY m.nombre, g.numero";
            
            return $this->db->query($sql, ['docente_id' => $docenteId]);
        } catch (Exception $e) {
            error_log("Error getting clases docente: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener horarios del docente
     */
    private function getHorariosDocente($docenteId)
    {
        try {
            $sql = "SELECT 
                        h.id,
                        h.dia_semana,
                        h.hora_inicio,
                        h.hora_fin,
                        CASE h.dia_semana
                            WHEN 1 THEN 'Lunes'
                            WHEN 2 THEN 'Martes'
                            WHEN 3 THEN 'Miércoles'
                            WHEN 4 THEN 'Jueves'
                            WHEN 5 THEN 'Viernes'
                            WHEN 6 THEN 'Sábado'
                            WHEN 7 THEN 'Domingo'
                        END as dia_nombre,
                        g.numero as grupo_numero,
                        g.semestre,
                        m.codigo as materia_codigo,
                        m.nombre as materia_nombre,
                        a.nombre as aula_nombre,
                        a.codigo as aula_codigo,
                        -- Verificar si ya se registró asistencia hoy
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM asistencia_docente ad 
                                WHERE ad.horario_id = h.id 
                                AND ad.fecha = CURRENT_DATE
                                AND ad.docente_id = :docente_id
                            ) THEN true
                            ELSE false
                        END as asistencia_registrada_hoy,
                        -- Verificar disponibilidad para marcar
                        -- Ventana: 20 minutos antes del inicio hasta 10 minutos después del inicio
                        CASE 
                            WHEN h.dia_semana = CASE EXTRACT(DOW FROM CURRENT_DATE)
                                WHEN 0 THEN 7  -- Domingo en PostgreSQL es 0, en nuestro esquema es 7
                                ELSE EXTRACT(DOW FROM CURRENT_DATE)
                            END
                            AND CURRENT_TIME >= (h.hora_inicio::time - INTERVAL '20 minutes')::time
                            AND CURRENT_TIME <= (h.hora_inicio::time + INTERVAL '10 minutes')::time
                            AND NOT EXISTS (
                                SELECT 1 FROM asistencia_docente ad 
                                WHERE ad.horario_id = h.id 
                                AND ad.fecha = CURRENT_DATE
                                AND ad.docente_id = :docente_id
                            )
                            THEN true
                            ELSE false
                        END as puede_marcar
                    FROM horarios h
                    INNER JOIN grupos g ON h.grupo_id = g.id
                    INNER JOIN materias m ON g.materia_id = m.id
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    WHERE h.docente_id = :docente_id AND h.activo = true
                    ORDER BY h.dia_semana, h.hora_inicio";
            
            return $this->db->query($sql, ['docente_id' => $docenteId]);
        } catch (Exception $e) {
            error_log("Error getting horarios docente: " . $e->getMessage());
            return [];
        }
    }
}
