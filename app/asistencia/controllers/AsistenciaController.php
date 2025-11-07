<?php
class AsistenciaController extends Controller
{
    private $db;
    private $middleware;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->middleware = new Middleware();
        // Asegurar que la zona horaria esté configurada para Bolivia
        if (date_default_timezone_get() !== 'America/La_Paz') {
            date_default_timezone_set('America/La_Paz');
        }
    }
    public function index()
    {
        $this->middleware->requireAuth();
        // Registrar acceso al módulo
        ActivityLogger::logView('asistencia', null);
        
        $user = $this->middleware->getCurrentUser();
        
        // Si es docente, verificar y registrar automáticamente incumplidos
        if ($user['rol'] === 'docente') {
            $this->verificarYRegistrarIncumplidos($user['id']);
        }
        $asistencias = $this->getAsistencias($user);
        $asistenciasHoy = $this->getAsistenciasDia($user);
        $ausencias = $this->getAusencias($user);
        $incumplimientos = $this->getIncumplimientosDisponibles($user);

        $data = [
            'title' => 'Control de Asistencia',
            'user' => $user,
            'asistencias' => $asistencias,
            'asistenciasHoy' => $asistenciasHoy,
            'horarios' => $this->getHorariosUsuario($user),
            'esDocente' => $user['rol'] === 'docente',
            'ausencias' => $ausencias,
            'incumplimientos' => $incumplimientos,
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];
        return $this->view->renderWithLayout('asistencia/index', $data);
    }

    public function registrar()
    {
        // Registrar acceso al módulo
        ActivityLogger::logView('asistencia/registrar', null);
        
        $user = $this->getCurrentUser();
        $data = [
            'title' => 'Registrar Asistencia',
            'user' => $user,
            'horarios' => $this->getHorariosUsuario($user),
            'esDocente' => $user['rol'] === 'docente'
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
    
    public function processRegistro()
    {
        header('Content-Type: application/json');
        
        $user = $this->getCurrentUser();
        
        if ($user['rol'] !== 'docente') {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción'
            ]);
            exit;
        }
        
        if (!isset($_POST['horario_id']) || empty($_POST['horario_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Horario no especificado'
            ]);
            exit;
        }
        
        $horarioId = intval($_POST['horario_id']);
        
        // Obtener horario desde la BD
        $horario = $this->getHorarioById($horarioId, $user['id']);
        
        if (!$horario) {
            echo json_encode([
                'success' => false,
                'message' => 'Horario no encontrado o no tienes permiso para acceder a él'
            ]);
            exit;
        }
        
        // Verificar si ya se registró asistencia hoy
        if ($this->yaRegistroAsistencia($horarioId, $user['id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya se registró asistencia para este horario hoy'
            ]);
            exit;
        }
        
        // Verificar si está dentro de la ventana permitida o dentro del tiempo de clase
    // Asegurar zona horaria de Bolivia
    $timezone = new DateTimeZone('America/La_Paz');
        $horaActual = new DateTime('now', $timezone);
        $fechaActual = $horaActual->format('Y-m-d');
        $diaActual = (int)$horaActual->format('N');
        
        // Normalizar formato de hora
        $horaInicioStr = $horario['hora_inicio'];
        if (strlen($horaInicioStr) == 5) $horaInicioStr .= ':00';
        $horaFinStr = $horario['hora_fin'];
        if (strlen($horaFinStr) == 5) $horaFinStr .= ':00';
        
        // Crear DateTime con fecha actual
        $horaInicio = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaInicioStr);
        if (!$horaInicio) {
            $horaInicio = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaInicioStr, 0, 5));
        }
        $horaFin = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaFinStr);
        if (!$horaFin) {
            $horaFin = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaFinStr, 0, 5));
        }
        
        // Verificar que es el día correcto
        if ($horario['dia_semana'] != $diaActual) {
            echo json_encode([
                'success' => false,
                'message' => 'No es el día programado para este horario'
            ]);
            exit;
        }
        
        // Verificar que está dentro del tiempo de clase
        if (!$horaInicio instanceof DateTime || !$horaFin instanceof DateTime) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar el horario'
            ]);
            exit;
        }
        
        // Calcular ventana de marcación
        $ventanaInicio = clone $horaInicio;
        $ventanaInicio->modify('-20 minutes');
        $ventanaFin = clone $horaInicio;
        $ventanaFin->modify('+10 minutes');
        
        // Permitir marcar si está dentro de la ventana o dentro del tiempo de clase
        if ($horaActual < $ventanaInicio) {
            echo json_encode([
                'success' => false,
                'message' => 'Aún no es el momento de marcar asistencia. Disponible a las ' . $ventanaInicio->format('H:i')
            ]);
            exit;
        }
        
        if ($horaActual > $horaFin) {
            echo json_encode([
                'success' => false,
                'message' => 'El tiempo de la clase ya pasó. No se puede marcar asistencia.'
            ]);
            exit;
        }
        
        $resultado = $this->registrarAsistencia($user, $horario);
        echo json_encode($resultado);
        exit;
    }
    
    private function registrarAsistencia($docente, $horario)
    {
        try {
            // Asegurar zona horaria de Bolivia
            $timezone = new DateTimeZone('America/La_Paz');
            $horaMarcacion = new DateTime('now', $timezone);
            $fechaActual = $horaMarcacion->format('Y-m-d');
            $horaMarcacionTime = $horaMarcacion->format('H:i:s');
            
            // Normalizar formato de hora
            $horaInicioStr = $horario['hora_inicio'];
            if (strlen($horaInicioStr) == 5) {
                $horaInicioStr .= ':00';
            }
            $horaFinStr = $horario['hora_fin'];
            if (strlen($horaFinStr) == 5) {
                $horaFinStr .= ':00';
            }
            
            // Crear DateTime con fecha actual y zona horaria de Bolivia
            $timezone = new DateTimeZone('America/La_Paz');
            $horaProgramada = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaInicioStr, $timezone);
            if (!$horaProgramada) {
                $horaProgramada = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaInicioStr, 0, 5), $timezone);
            }
            
            $horaFinClase = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaFinStr, $timezone);
            if (!$horaFinClase) {
                $horaFinClase = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaFinStr, 0, 5), $timezone);
            }
            
            $horaMarcacionObj = new DateTime($horaMarcacion->format('Y-m-d H:i:s'), $timezone);
            
            // Calcular ventana de marcación: 20 minutos antes hasta 10 minutos después del inicio
            $ventanaInicio = clone $horaProgramada;
            $ventanaInicio->modify('-20 minutes');
            
            $ventanaFin = clone $horaProgramada;
            $ventanaFin->modify('+10 minutes');
            
            // Determinar estado según la hora de marcación
        $estado = 'presente';
        $mensaje = 'Asistencia registrada exitosamente';
            $tiempoMarcacion = null;
            
            if ($horaMarcacionObj >= $ventanaInicio && $horaMarcacionObj <= $horaProgramada) {
                // Marcó antes del inicio (dentro de la ventana)
                $estado = 'presente';
                $mensaje = 'Asistencia registrada exitosamente';
            } elseif ($horaMarcacionObj > $horaProgramada && $horaMarcacionObj <= $ventanaFin) {
                // Marcó después del inicio pero dentro de la ventana (10 minutos después)
                $diferencia = $horaMarcacionObj->diff($horaProgramada);
            $minutosTarde = $diferencia->i + ($diferencia->h * 60);
            $estado = 'tardanza';
                $mensaje = 'Llegada tardía registrada. Llegaste ' . $minutosTarde . ' minutos tarde.';
                $tiempoMarcacion = $minutosTarde . ' minutes';
            } elseif ($horaMarcacionObj > $ventanaFin && $horaMarcacionObj <= $horaFinClase) {
                // Marcó después de la ventana pero dentro del tiempo de clase
                $diferencia = $horaMarcacionObj->diff($horaProgramada);
                $minutosTarde = $diferencia->i + ($diferencia->h * 60);
                $estado = 'asistido_tarde';
                $mensaje = 'Asistencia registrada fuera de la ventana permitida. Llegaste ' . $minutosTarde . ' minutos tarde.';
                $tiempoMarcacion = $minutosTarde . ' minutes';
            } else {
                // Fuera del tiempo de clase (no debería pasar si se valida antes)
                $estado = 'incumplido';
                $mensaje = 'Asistencia no registrada dentro del tiempo permitido';
            }
            
            // Verificar qué columnas existen
            $checkColumns = "SELECT column_name 
                           FROM information_schema.columns 
                           WHERE table_name = 'asistencia_docente' 
                           AND column_name IN ('hora_marcacion', 'tiempo_marcacion')";
            $existingColumns = $this->db->query($checkColumns);
            $columnNames = array_column($existingColumns, 'column_name');
            $hasHoraMarcacion = in_array('hora_marcacion', $columnNames);
            $hasTiempoMarcacion = in_array('tiempo_marcacion', $columnNames);
            
            // Construir SQL dinámicamente según las columnas disponibles
            $columns = ['docente_id', 'horario_id', 'fecha', 'hora_inicio', 'hora_fin', 'estado'];
            $values = [':docente_id', ':horario_id', ':fecha', ':hora_inicio', ':hora_fin', ':estado'];
            $params = [
                ':docente_id' => $docente['id'],
                ':horario_id' => $horario['id'],
                ':fecha' => $fechaActual,
                ':hora_inicio' => $horario['hora_inicio'],
                ':hora_fin' => $horario['hora_fin'],
                ':estado' => $estado
            ];
            
            if ($hasHoraMarcacion) {
                $columns[] = 'hora_marcacion';
                $values[] = ':hora_marcacion';
                $params[':hora_marcacion'] = $horaMarcacion->format('Y-m-d H:i:s');
            }
            
            if ($hasTiempoMarcacion) {
                $columns[] = 'tiempo_marcacion';
                $values[] = ':tiempo_marcacion';
                $params[':tiempo_marcacion'] = $tiempoMarcacion;
            }
            
            // Agregar registrado_por siempre
            $columns[] = 'registrado_por';
            $values[] = ':registrado_por';
            $params[':registrado_por'] = $docente['id'];
            
            // Construir SQL final
            $sql = "INSERT INTO asistencia_docente (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $values) . ")";
            
            $this->db->query($sql, $params);
            
            // Registrar actividad
            ActivityLogger::logCreate('asistencia_docente', null, [
                'docente_id' => $docente['id'],
                'horario_id' => $horario['id'],
                'fecha' => $fechaActual,
                'estado' => $estado
            ]);
        
        return [
            'success' => true,
            'message' => $mensaje,
            'data' => [
                    'hora_marcacion' => $horaMarcacionTime,
                    'fecha' => $fechaActual,
                'estado' => $estado,
                    'materia' => $horario['materia_nombre'] ?? 'N/A',
                    'grupo' => $horario['grupo_numero'] ?? 'N/A',
                    'aula' => $horario['aula_nombre'] ?? 'N/A'
                ]
            ];
        } catch (Exception $e) {
            error_log("Error registrando asistencia: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al registrar la asistencia: ' . $e->getMessage()
            ];
        }
    }
    
    private function getHorarioById($horarioId, $docenteId)
    {
        try {
            $sql = "SELECT h.*, 
                           g.numero as grupo_numero,
                           g.semestre,
                           m.codigo as materia_codigo,
                           m.nombre as materia_nombre,
                           a.nombre as aula_nombre,
                           a.codigo as aula_codigo
                    FROM horarios h
                    INNER JOIN grupos g ON h.grupo_id = g.id
                    INNER JOIN materias m ON g.materia_id = m.id
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    WHERE h.id = :horario_id AND h.docente_id = :docente_id AND h.activo = true";
            
            $result = $this->db->query($sql, [':horario_id' => $horarioId, ':docente_id' => $docenteId]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            error_log("Error getting horario: " . $e->getMessage());
            return null;
        }
    }
    
    private function yaRegistroAsistencia($horarioId, $docenteId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                    WHERE horario_id = :horario_id AND docente_id = :docente_id AND fecha = CURRENT_DATE";
            $result = $this->db->query($sql, [':horario_id' => $horarioId, ':docente_id' => $docenteId]);
            return ($result[0]['total'] ?? 0) > 0;
        } catch (Exception $e) {
            error_log("Error verificando asistencia: " . $e->getMessage());
            return false;
        }
    }

    private function getAsistencias($user)
    {
        try {
            if ($user['rol'] === 'docente') {
                // Verificar qué columnas existen
                $checkColumns = "SELECT column_name 
                               FROM information_schema.columns 
                               WHERE table_name = 'asistencia_docente' 
                               AND column_name = 'hora_marcacion'";
                $existingColumns = $this->db->query($checkColumns);
                $hasHoraMarcacion = count($existingColumns) > 0;
                
                $horaMarcacionField = $hasHoraMarcacion ? 'ad.hora_marcacion' : 'ad.created_at as hora_marcacion';
                
                $sql = "SELECT 
                            ad.id,
                            ad.fecha,
                            ad.hora_inicio,
                            ad.hora_fin,
                            ad.estado,
                            $horaMarcacionField,
                            h.id as horario_id,
                            m.nombre as materia_nombre,
                            m.codigo as materia_codigo,
                            g.numero as grupo_numero,
                            g.semestre,
                            a.nombre as aula_nombre,
                            a.codigo as aula_codigo
                        FROM asistencia_docente ad
                        INNER JOIN horarios h ON ad.horario_id = h.id
                        INNER JOIN grupos g ON h.grupo_id = g.id
                        INNER JOIN materias m ON g.materia_id = m.id
                        LEFT JOIN aulas a ON h.aula_id = a.id
                        WHERE ad.docente_id = :docente_id
                        ORDER BY ad.fecha DESC, ad.hora_inicio DESC
                        LIMIT 100";
                
                return $this->db->query($sql, [':docente_id' => $user['id']]);
            } else {
                // Para administradores/coordinadores, mostrar todas las asistencias
                $checkColumns = "SELECT column_name 
                               FROM information_schema.columns 
                               WHERE table_name = 'asistencia_docente' 
                               AND column_name = 'hora_marcacion'";
                $existingColumns = $this->db->query($checkColumns);
                $hasHoraMarcacion = count($existingColumns) > 0;
                
                $horaMarcacionField = $hasHoraMarcacion ? 'ad.hora_marcacion' : 'ad.created_at as hora_marcacion';
                
                $sql = "SELECT 
                            ad.id,
                            ad.fecha,
                            ad.hora_inicio,
                            ad.hora_fin,
                            ad.estado,
                            $horaMarcacionField,
                            u.nombre || ' ' || u.apellido as docente_nombre,
                            m.nombre as materia_nombre,
                            g.numero as grupo_numero
                        FROM asistencia_docente ad
                        INNER JOIN usuarios u ON ad.docente_id = u.id
                        INNER JOIN horarios h ON ad.horario_id = h.id
                        INNER JOIN grupos g ON h.grupo_id = g.id
                        INNER JOIN materias m ON g.materia_id = m.id
                        ORDER BY ad.fecha DESC, ad.hora_inicio DESC
                        LIMIT 200";
                
                return $this->db->query($sql);
            }
        } catch (Exception $e) {
            error_log("Error getting asistencias: " . $e->getMessage());
            return [];
        }
    }

    private function getAsistenciasDia($user)
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM asistencia_docente WHERE fecha = CURRENT_DATE";
            $params = [];

            if ($user['rol'] === 'docente') {
                $sql .= " AND docente_id = :docente_id";
                $params[':docente_id'] = $user['id'];
            }

            $result = $this->db->query($sql, $params);
            return (int)($result[0]['total'] ?? 0);
        } catch (Exception $e) {
            error_log('Error obteniendo asistencias del día: ' . $e->getMessage());
            return 0;
        }
    }

    private function getAusencias($user)
    {
        try {
            $sqlBase = "SELECT ad.*, 
                               u.nombre || ' ' || u.apellido AS docente_nombre,
                               asi.fecha AS asistencia_fecha,
                               asi.estado AS asistencia_estado,
                               m.nombre AS materia_nombre,
                               g.numero AS grupo_numero
                        FROM ausencias_docente ad
                        LEFT JOIN usuarios u ON ad.docente_id = u.id
                        LEFT JOIN asistencia_docente asi ON ad.asistencia_id = asi.id
                        LEFT JOIN horarios h ON asi.horario_id = h.id
                        LEFT JOIN grupos g ON h.grupo_id = g.id
                        LEFT JOIN materias m ON g.materia_id = m.id";

            if ($user['rol'] === 'docente') {
                $sql = $sqlBase . " WHERE ad.docente_id = :docente_id ORDER BY ad.fecha DESC, ad.id DESC";
                return $this->db->query($sql, [':docente_id' => $user['id']]);
            }

            $sql = $sqlBase . " ORDER BY ad.fecha DESC, ad.id DESC";
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Error obteniendo ausencias: ' . $e->getMessage());
            return [];
        }
    }

    private function getIncumplimientosDisponibles($user)
    {
        try {
            $sqlBase = "SELECT ad.id, ad.docente_id, ad.fecha, ad.estado, 
                               u.nombre || ' ' || u.apellido AS docente_nombre,
                               m.nombre AS materia_nombre, g.numero AS grupo_numero
                        FROM asistencia_docente ad
                        INNER JOIN usuarios u ON ad.docente_id = u.id
                        INNER JOIN horarios h ON ad.horario_id = h.id
                        INNER JOIN grupos g ON h.grupo_id = g.id
                        INNER JOIN materias m ON g.materia_id = m.id
                        WHERE ad.estado IN ('incumplido', 'ausente')";
        
        if ($user['rol'] === 'docente') {
                $sql = $sqlBase . " AND ad.docente_id = :docente_id
                                     AND NOT EXISTS (SELECT 1 FROM ausencias_docente au WHERE au.asistencia_id = ad.id)
                                     ORDER BY ad.fecha DESC";
                return $this->db->query($sql, [':docente_id' => $user['id']]);
            }

            $sql = $sqlBase . " ORDER BY ad.fecha DESC";
            return $this->db->query($sql);
        } catch (Exception $e) {
            error_log('Error obteniendo incumplimientos: ' . $e->getMessage());
            return [];
        }
    }

    private function getHorariosUsuario($user)
    {
        if ($user['rol'] === 'docente') {
            return $this->getHorariosConDisponibilidad($user);
        }
        return [];
    }
    
    private function getHorariosConDisponibilidad($user)
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
                        END as asistencia_registrada_hoy
                    FROM horarios h
                    INNER JOIN grupos g ON h.grupo_id = g.id
                    INNER JOIN materias m ON g.materia_id = m.id
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    WHERE h.docente_id = :docente_id AND h.activo = true
                    ORDER BY h.dia_semana, h.hora_inicio";
            
            $horarios = $this->db->query($sql, [':docente_id' => $user['id']]);
            
            // Asegurar zona horaria de Bolivia
            $timezone = new DateTimeZone('America/La_Paz');
            $horaActual = new DateTime('now', $timezone);
            // date('N') retorna 1=Lunes, 7=Domingo, que coincide con nuestro esquema
            $diaActual = (int)$horaActual->format('N');
            
        foreach ($horarios as &$horario) {
                $horario['dia_numero'] = $horario['dia_semana'];
            $horario['disponible'] = false;
            $horario['estado'] = 'no_disponible';
                $horario['mensaje'] = 'No es el día programado';
                
                // Solo verificar disponibilidad si es el día actual
                if ($horario['dia_semana'] != $diaActual) {
                    $horario['mensaje'] = 'No es el día programado (Día: ' . $horario['dia_nombre'] . ')';
                    continue;
                }
                
                // Verificar si ya se registró asistencia
                if ($horario['asistencia_registrada_hoy']) {
                    $horario['estado'] = 'registrada';
                    $horario['mensaje'] = 'Asistencia ya registrada';
                    continue;
                }
                
                // Crear objetos DateTime desde la hora con fecha actual para comparación
                // PostgreSQL puede devolver TIME en formato 'HH:MM:SS' o 'HH:MM'
                $horaInicioStr = trim($horario['hora_inicio']);
                $horaFinStr = trim($horario['hora_fin']);
                
                // Limpiar y normalizar formato de hora
                // Remover cualquier carácter extraño
                $horaInicioStr = preg_replace('/[^0-9:]/', '', $horaInicioStr);
                $horaFinStr = preg_replace('/[^0-9:]/', '', $horaFinStr);
                
                // Normalizar formato: asegurar que tenga formato HH:MM:SS
                $partsInicio = explode(':', $horaInicioStr);
                $partsFin = explode(':', $horaFinStr);
                
                if (count($partsInicio) == 2) {
                    $horaInicioStr = $partsInicio[0] . ':' . str_pad($partsInicio[1], 2, '0', STR_PAD_LEFT) . ':00';
                } elseif (count($partsInicio) == 3) {
                    $horaInicioStr = str_pad($partsInicio[0], 2, '0', STR_PAD_LEFT) . ':' . 
                                    str_pad($partsInicio[1], 2, '0', STR_PAD_LEFT) . ':' . 
                                    str_pad($partsInicio[2], 2, '0', STR_PAD_LEFT);
                }
                
                if (count($partsFin) == 2) {
                    $horaFinStr = $partsFin[0] . ':' . str_pad($partsFin[1], 2, '0', STR_PAD_LEFT) . ':00';
                } elseif (count($partsFin) == 3) {
                    $horaFinStr = str_pad($partsFin[0], 2, '0', STR_PAD_LEFT) . ':' . 
                                 str_pad($partsFin[1], 2, '0', STR_PAD_LEFT) . ':' . 
                                 str_pad($partsFin[2], 2, '0', STR_PAD_LEFT);
                }
                
                // Crear DateTime con fecha actual + hora del horario
                $fechaActual = $horaActual->format('Y-m-d');
                
                // Crear DateTime con formato completo y zona horaria de Bolivia
                $timezone = new DateTimeZone('America/La_Paz');
                $horaInicio = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaInicioStr, $timezone);
                $horaFin = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaFinStr, $timezone);
                
                // Verificar que los objetos DateTime se crearon correctamente
                if (!$horaInicio instanceof DateTime || !$horaFin instanceof DateTime) {
                    error_log("Error creando DateTime - Inicio: " . $horaInicioStr . ", Fin: " . $horaFinStr . 
                             ", Horario ID: " . $horario['id']);
                    $horario['estado'] = 'error';
                    $horario['mensaje'] = 'Error al procesar horario';
                    continue;
                }
                
                // Crear ventanas de tiempo para marcación
                // Ventana permitida: 20 minutos antes del inicio hasta 10 minutos después del inicio
            $ventanaInicio = clone $horaInicio;
                $ventanaInicio->modify('-20 minutes');
                
            $ventanaFin = clone $horaInicio;
                $ventanaFin->modify('+10 minutes');
                
                // Comparar con hora actual
            if ($horaActual >= $ventanaInicio && $horaActual <= $ventanaFin) {
                    // Dentro de la ventana permitida
                $horario['disponible'] = true;
                $horario['estado'] = 'disponible';
                $horario['mensaje'] = 'Disponible para marcar';
            } elseif ($horaActual < $ventanaInicio) {
                    // Antes de la ventana
                $horario['estado'] = 'pendiente';
                $horario['mensaje'] = 'Disponible a las ' . $ventanaInicio->format('H:i');
                    $horario['disponible'] = false;
                } elseif ($horaActual > $ventanaFin && $horaActual <= $horaFin) {
                    // Después de la ventana pero dentro del tiempo de clase
                    // Permitir marcar como "asistido tarde"
                    $horario['disponible'] = true;
                    $horario['estado'] = 'fuera_ventana';
                    $horario['mensaje'] = 'Fuera de ventana de marcación (solo se puede marcar 20 min antes hasta 10 min después del inicio). Puede marcar como asistido tarde.';
                } elseif ($horaActual > $horaFin) {
                    // Pasó el tiempo de la clase
                    // Si no se registró asistencia, marcar como incumplido
                    if (!$horario['asistencia_registrada_hoy']) {
                        // Verificar si ya existe un registro de incumplido para hoy
                        $existeIncumplido = $this->verificarIncumplido($horario['id'], $user['id']);
                        if (!$existeIncumplido) {
                            // Crear registro de incumplido automáticamente
                            $this->registrarIncumplido($user['id'], $horario);
                        }
                        $horario['estado'] = 'incumplido';
                        $horario['mensaje'] = 'No se registró asistencia dentro del tiempo permitido';
                        $horario['disponible'] = false;
                    } else {
                $horario['estado'] = 'vencido';
                        $horario['mensaje'] = 'Ventana de marcación expirada';
                        $horario['disponible'] = false;
                    }
                } else {
                    // Por defecto, no disponible
                    $horario['disponible'] = false;
                    $horario['estado'] = 'no_disponible';
                    $horario['mensaje'] = 'Fuera del horario';
                }
            }
            
            return $horarios;
        } catch (Exception $e) {
            error_log("Error getting horarios con disponibilidad: " . $e->getMessage());
            return [];
        }
    }

    private function getReportes()
    {
        return [
            ['docente' => 'Juan Pérez', 'total_clases' => 40, 'asistencias' => 38, 'ausencias' => 2, 'porcentaje' => 95.0],
            ['docente' => 'María González', 'total_clases' => 40, 'asistencias' => 40, 'ausencias' => 0, 'porcentaje' => 100.0]
        ];
    }
    
    /**
     * Verificar si ya existe un registro de incumplido para hoy
     */
    private function verificarIncumplido($horarioId, $docenteId)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM asistencia_docente 
                    WHERE horario_id = :horario_id 
                    AND docente_id = :docente_id 
                    AND fecha = CURRENT_DATE 
                    AND estado = 'incumplido'";
            $result = $this->db->query($sql, [':horario_id' => $horarioId, ':docente_id' => $docenteId]);
            return ($result[0]['total'] ?? 0) > 0;
        } catch (Exception $e) {
            error_log("Error verificando incumplido: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar y registrar automáticamente incumplidos para todos los horarios del día
     */
    private function verificarYRegistrarIncumplidos($docenteId)
    {
        try {
            // Asegurar zona horaria de Colombia
            $timezone = new DateTimeZone('America/Bogota');
            $horaActual = new DateTime('now', $timezone);
            $diaActual = (int)$horaActual->format('N');
            $fechaActual = $horaActual->format('Y-m-d');
            
            // Obtener todos los horarios del docente para el día actual que ya terminaron
            $sql = "SELECT 
                        h.id,
                        h.hora_inicio,
                        h.hora_fin,
                        h.dia_semana
                    FROM horarios h
                    WHERE h.docente_id = :docente_id 
                    AND h.activo = true
                    AND h.dia_semana = :dia_semana
                    AND NOT EXISTS (
                        SELECT 1 FROM asistencia_docente ad 
                        WHERE ad.horario_id = h.id 
                        AND ad.docente_id = :docente_id
                        AND ad.fecha = :fecha
                    )";
            
            $horarios = $this->db->query($sql, [
                ':docente_id' => $docenteId,
                ':dia_semana' => $diaActual,
                ':fecha' => $fechaActual
            ]);
            
            foreach ($horarios as $horario) {
                // Normalizar formato de hora
                $horaInicioStr = $horario['hora_inicio'];
                if (strlen($horaInicioStr) == 5) {
                    $horaInicioStr .= ':00';
                }
                $horaFinStr = $horario['hora_fin'];
                if (strlen($horaFinStr) == 5) {
                    $horaFinStr .= ':00';
                }
                
                // Crear DateTime con fecha actual
                $horaInicio = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaInicioStr, $timezone);
                if (!$horaInicio) {
                    $horaInicio = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaInicioStr, 0, 5), $timezone);
                }
                
                $horaFin = DateTime::createFromFormat('Y-m-d H:i:s', $fechaActual . ' ' . $horaFinStr, $timezone);
                if (!$horaFin) {
                    $horaFin = DateTime::createFromFormat('Y-m-d H:i', $fechaActual . ' ' . substr($horaFinStr, 0, 5), $timezone);
                }
                
                // Verificar que los objetos DateTime se crearon correctamente
                if (!$horaInicio instanceof DateTime || !$horaFin instanceof DateTime) {
                    continue;
                }
                
                // Si la hora actual es mayor que la hora de fin de la clase, registrar como incumplido
                if ($horaActual > $horaFin) {
                    $this->registrarIncumplido($docenteId, $horario);
                }
            }
        } catch (Exception $e) {
            error_log("Error verificando incumplidos automáticos: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar automáticamente como incumplido
     */
    private function registrarIncumplido($docenteId, $horario)
    {
        try {
            // Asegurar zona horaria de Bolivia
            $timezone = new DateTimeZone('America/La_Paz');
            $horaActualObj = new DateTime('now', $timezone);
            $fechaActual = $horaActualObj->format('Y-m-d');
            $horaActual = $horaActualObj->format('Y-m-d H:i:s');
            
            // Normalizar formato de hora
            $horaInicioStr = $horario['hora_inicio'];
            if (strlen($horaInicioStr) == 5) {
                $horaInicioStr .= ':00';
            }
            $horaFinStr = $horario['hora_fin'];
            if (strlen($horaFinStr) == 5) {
                $horaFinStr .= ':00';
            }
            
            // Verificar si ya existe un registro para este horario hoy
            $sqlCheck = "SELECT COUNT(*) as total FROM asistencia_docente 
                        WHERE docente_id = :docente_id 
                        AND horario_id = :horario_id 
                        AND fecha = :fecha";
            $checkResult = $this->db->query($sqlCheck, [
                ':docente_id' => $docenteId,
                ':horario_id' => $horario['id'],
                ':fecha' => $fechaActual
            ]);
            
            if (($checkResult[0]['total'] ?? 0) > 0) {
                // Ya existe un registro, no crear uno nuevo
                return;
            }
            
            // Verificar qué columnas existen
            $checkColumns = "SELECT column_name 
                           FROM information_schema.columns 
                           WHERE table_name = 'asistencia_docente' 
                           AND column_name IN ('hora_marcacion', 'tiempo_marcacion')";
            $existingColumns = $this->db->query($checkColumns);
            $columnNames = array_column($existingColumns, 'column_name');
            $hasHoraMarcacion = in_array('hora_marcacion', $columnNames);
            $hasTiempoMarcacion = in_array('tiempo_marcacion', $columnNames);
            
            // Construir SQL dinámicamente según las columnas disponibles
            $columns = ['docente_id', 'horario_id', 'fecha', 'hora_inicio', 'hora_fin', 'estado'];
            $values = [':docente_id', ':horario_id', ':fecha', ':hora_inicio', ':hora_fin', ':estado'];
            $params = [
                ':docente_id' => $docenteId,
                ':horario_id' => $horario['id'],
                ':fecha' => $fechaActual,
                ':hora_inicio' => $horaInicioStr,
                ':hora_fin' => $horaFinStr,
                ':estado' => 'incumplido'
            ];
            
            if ($hasHoraMarcacion) {
                $columns[] = 'hora_marcacion';
                $values[] = ':hora_marcacion';
                $params[':hora_marcacion'] = $horaActual;
            }
            
            if ($hasTiempoMarcacion) {
                $columns[] = 'tiempo_marcacion';
                $values[] = ':tiempo_marcacion';
                $params[':tiempo_marcacion'] = null; // Para incumplido no hay tiempo de marcación
            }
            
            // Agregar registrado_por y observaciones siempre
            $columns[] = 'registrado_por';
            $values[] = ':registrado_por';
            $params[':registrado_por'] = $docenteId;
            
            $columns[] = 'observaciones';
            $values[] = ':observaciones';
            $params[':observaciones'] = 'Registro automático: no se marcó asistencia dentro del tiempo permitido';
            
            // Construir SQL final
            $sql = "INSERT INTO asistencia_docente (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $values) . ")";
            
            $this->db->query($sql, $params);
            
            // Registrar actividad
            ActivityLogger::logCreate('asistencia_docente', null, [
                'docente_id' => $docenteId,
                'horario_id' => $horario['id'],
                'fecha' => $fechaActual,
                'estado' => 'incumplido',
                'automatico' => true
            ]);
        } catch (Exception $e) {
            error_log("Error registrando incumplido automático: " . $e->getMessage());
        }
    }
}
