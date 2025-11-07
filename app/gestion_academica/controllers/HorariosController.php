<?php
/**
 * Controlador para gestión de horarios
 */
class HorariosController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

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

        $horario = $this->getHorario($id);
        if (!$horario) {
            return $this->view->renderWithLayout('errors/404', ['title' => 'Horario no encontrado']);
        }

        $data = [
            'title' => 'Editar Horario',
            'user' => $this->getCurrentUser(),
            'horario' => $horario,
            'grupos' => $this->getGrupos(),
            'aulas' => $this->getAulas(),
            'docentes' => $this->getDocentes()
        ];

        return $this->view->renderWithLayout('horarios/edit', $data);
    }

    public function store()
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        try {
            $grupoId = intval($_POST['grupo_id'] ?? 0);
            $aulaId = intval($_POST['aula_id'] ?? 0);
            $docenteId = intval($_POST['docente_id'] ?? 0);
            $diaSemana = intval($_POST['dia_semana'] ?? 0);
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';

            // Validar datos básicos
            if (!$grupoId || !$aulaId || !$docenteId || !$diaSemana || !$horaInicio || !$horaFin) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                return;
            }

            // Validar que la hora de fin sea posterior a la hora de inicio
            if (strtotime($horaFin) <= strtotime($horaInicio)) {
                echo json_encode(['success' => false, 'message' => 'La hora de fin debe ser posterior a la hora de inicio']);
                return;
            }

            // Verificar conflictos de horarios
            $conflicts = $this->checkConflicts($grupoId, $aulaId, $docenteId, $diaSemana, $horaInicio, $horaFin);
            
            // Verificar si hay conflictos reales
            $hasConflicts = false;
            $conflictMessages = [];
            foreach ($conflicts as $type => $conflict) {
                if ($conflict['exists']) {
                    $hasConflicts = true;
                    $conflictMessages[] = $conflict['message'];
                }
            }
            
            if ($hasConflicts) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Conflictos de horario detectados:',
                    'conflicts' => $conflictMessages,
                    'details' => $conflicts
                ]);
                return;
            }

            // Insertar horario
            $sql = "INSERT INTO horarios (grupo_id, aula_id, docente_id, dia_semana, hora_inicio, hora_fin, activo)
                    VALUES (:grupo_id, :aula_id, :docente_id, :dia_semana, :hora_inicio, :hora_fin, true)";
            $params = [
                ':grupo_id' => $grupoId,
                ':aula_id' => $aulaId,
                ':docente_id' => $docenteId,
                ':dia_semana' => $diaSemana,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin
            ];

            $this->db->query($sql, $params);

            // Obtener ID del horario creado
            $horarioIdSql = "SELECT id FROM horarios WHERE grupo_id = :grupo_id AND aula_id = :aula_id 
                            AND docente_id = :docente_id AND dia_semana = :dia_semana 
                            AND hora_inicio = :hora_inicio AND hora_fin = :hora_fin 
                            ORDER BY created_at DESC LIMIT 1";
            $horarioIdResult = $this->db->query($horarioIdSql, $params);
            $horarioId = $horarioIdResult && count($horarioIdResult) > 0 ? $horarioIdResult[0]['id'] : null;

            // Registrar actividad
            ActivityLogger::logCreate('horarios', $horarioId, [
                'grupo_id' => $grupoId,
                'aula_id' => $aulaId,
                'docente_id' => $docenteId,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Horario creado exitosamente',
                'redirect' => '/horarios'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el horario: ' . $e->getMessage()
            ]);
        }
    }

    public function update($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        try {
            $grupoId = intval($_POST['grupo_id'] ?? 0);
            $aulaId = intval($_POST['aula_id'] ?? 0);
            $docenteId = intval($_POST['docente_id'] ?? 0);
            $diaSemana = intval($_POST['dia_semana'] ?? 0);
            $horaInicio = $_POST['hora_inicio'] ?? '';
            $horaFin = $_POST['hora_fin'] ?? '';

            // Validar datos básicos
            if (!$grupoId || !$aulaId || !$docenteId || !$diaSemana || !$horaInicio || !$horaFin) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                return;
            }

            // Validar que la hora de fin sea posterior a la hora de inicio
            if (strtotime($horaFin) <= strtotime($horaInicio)) {
                echo json_encode(['success' => false, 'message' => 'La hora de fin debe ser posterior a la hora de inicio']);
                return;
            }

            // Obtener datos anteriores
            $oldSql = "SELECT * FROM horarios WHERE id = :id";
            $oldData = $this->db->query($oldSql, [':id' => $id]);

            // Verificar conflictos de horarios (excluyendo el horario actual)
            $conflicts = $this->checkConflicts($grupoId, $aulaId, $docenteId, $diaSemana, $horaInicio, $horaFin, $id);
            
            // Verificar si hay conflictos reales
            $hasConflicts = false;
            $conflictMessages = [];
            foreach ($conflicts as $type => $conflict) {
                if ($conflict['exists']) {
                    $hasConflicts = true;
                    $conflictMessages[] = $conflict['message'];
                }
            }
            
            if ($hasConflicts) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Conflictos de horario detectados:',
                    'conflicts' => $conflictMessages,
                    'details' => $conflicts
                ]);
                return;
            }

            // Actualizar horario
            $sql = "UPDATE horarios SET grupo_id = :grupo_id, aula_id = :aula_id, docente_id = :docente_id,
                    dia_semana = :dia_semana, hora_inicio = :hora_inicio, hora_fin = :hora_fin,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            $params = [
                ':grupo_id' => $grupoId,
                ':aula_id' => $aulaId,
                ':docente_id' => $docenteId,
                ':dia_semana' => $diaSemana,
                ':hora_inicio' => $horaInicio,
                ':hora_fin' => $horaFin,
                ':id' => $id
            ];

            $this->db->query($sql, $params);

            // Registrar actividad
            ActivityLogger::logUpdate('horarios', $id, $oldData, [
                'grupo_id' => $grupoId,
                'aula_id' => $aulaId,
                'docente_id' => $docenteId,
                'dia_semana' => $diaSemana,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Horario actualizado exitosamente',
                'redirect' => '/horarios'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el horario: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        if (!Middleware::checkRole(['administrador', 'coordinador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        try {
            // Obtener datos antes de eliminar
            $sql = "SELECT * FROM horarios WHERE id = :id";
            $horarioData = $this->db->query($sql, [':id' => $id]);

            if (empty($horarioData)) {
                echo json_encode(['success' => false, 'message' => 'Horario no encontrado']);
                return;
            }

            // Soft delete
            $sql = "UPDATE horarios SET activo = false, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);

            // Registrar actividad
            ActivityLogger::logDelete('horarios', $id, $horarioData);

            echo json_encode([
                'success' => true,
                'message' => 'Horario eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el horario: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar conflictos de horarios
     * @param int $grupoId ID del grupo
     * @param int $aulaId ID del aula
     * @param int $docenteId ID del docente
     * @param int $diaSemana Día de la semana (1-7)
     * @param string $horaInicio Hora de inicio
     * @param string $horaFin Hora de fin
     * @param int|null $excludeId ID del horario a excluir (para actualizaciones)
     * @return array Array con los conflictos detectados
     */
    private function checkConflicts($grupoId, $aulaId, $docenteId, $diaSemana, $horaInicio, $horaFin, $excludeId = null)
    {
        $conflicts = [
            'aula' => ['exists' => false, 'message' => ''],
            'docente' => ['exists' => false, 'message' => ''],
            'grupo' => ['exists' => false, 'message' => '']
        ];

        // Construir condición para excluir el horario actual si se está actualizando
        $excludeCondition = $excludeId ? "AND h.id != :exclude_id" : "";
        $params = [':dia_semana' => $diaSemana, ':hora_inicio' => $horaInicio, ':hora_fin' => $horaFin];
        if ($excludeId) {
            $params[':exclude_id'] = $excludeId;
        }

        // Verificar conflicto de aula (el aula está ocupada en ese horario)
        $sqlAula = "SELECT h.*, a.nombre as aula_nombre, g.numero as grupo_numero, 
                           u.nombre || ' ' || u.apellido as docente_nombre
                    FROM horarios h
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    LEFT JOIN grupos g ON h.grupo_id = g.id
                    LEFT JOIN usuarios u ON h.docente_id = u.id
                    WHERE h.aula_id = :aula_id AND h.dia_semana = :dia_semana 
                    AND h.activo = true
                    AND (
                        (h.hora_inicio < :hora_fin AND h.hora_fin > :hora_inicio)
                    )
                    $excludeCondition";
        $paramsAula = array_merge($params, [':aula_id' => $aulaId]);
        $conflictAula = $this->db->query($sqlAula, $paramsAula);
        
        if (!empty($conflictAula)) {
            $conflicts['aula']['exists'] = true;
            $conflictInfo = $conflictAula[0];
            $conflicts['aula']['message'] = "El aula '{$conflictInfo['aula_nombre']}' está ocupada en este horario por el grupo '{$conflictInfo['grupo_numero']}' con el docente '{$conflictInfo['docente_nombre']}'";
            $conflicts['aula']['data'] = $conflictInfo;
        }

        // Verificar conflicto de docente (el docente tiene otro horario en ese tiempo)
        $sqlDocente = "SELECT h.*, a.nombre as aula_nombre, g.numero as grupo_numero,
                              u.nombre || ' ' || u.apellido as docente_nombre
                       FROM horarios h
                       LEFT JOIN aulas a ON h.aula_id = a.id
                       LEFT JOIN grupos g ON h.grupo_id = g.id
                       LEFT JOIN usuarios u ON h.docente_id = u.id
                       WHERE h.docente_id = :docente_id AND h.dia_semana = :dia_semana
                       AND h.activo = true
                       AND (
                           (h.hora_inicio < :hora_fin AND h.hora_fin > :hora_inicio)
                       )
                       $excludeCondition";
        $paramsDocente = array_merge($params, [':docente_id' => $docenteId]);
        $conflictDocente = $this->db->query($sqlDocente, $paramsDocente);
        
        if (!empty($conflictDocente)) {
            $conflicts['docente']['exists'] = true;
            $conflictInfo = $conflictDocente[0];
            $conflicts['docente']['message'] = "El docente '{$conflictInfo['docente_nombre']}' tiene otro horario en este tiempo con el grupo '{$conflictInfo['grupo_numero']}' en el aula '{$conflictInfo['aula_nombre']}'";
            $conflicts['docente']['data'] = $conflictInfo;
        }

        // Verificar conflicto de grupo (el grupo tiene otro horario en ese tiempo)
        $sqlGrupo = "SELECT h.*, a.nombre as aula_nombre, g.numero as grupo_numero,
                            u.nombre || ' ' || u.apellido as docente_nombre
                     FROM horarios h
                     LEFT JOIN aulas a ON h.aula_id = a.id
                     LEFT JOIN grupos g ON h.grupo_id = g.id
                     LEFT JOIN usuarios u ON h.docente_id = u.id
                     WHERE h.grupo_id = :grupo_id AND h.dia_semana = :dia_semana
                     AND h.activo = true
                     AND (
                         (h.hora_inicio < :hora_fin AND h.hora_fin > :hora_inicio)
                     )
                     $excludeCondition";
        $paramsGrupo = array_merge($params, [':grupo_id' => $grupoId]);
        $conflictGrupo = $this->db->query($sqlGrupo, $paramsGrupo);
        
        if (!empty($conflictGrupo)) {
            $conflicts['grupo']['exists'] = true;
            $conflictInfo = $conflictGrupo[0];
            $conflicts['grupo']['message'] = "El grupo '{$conflictInfo['grupo_numero']}' tiene otro horario en este tiempo con el docente '{$conflictInfo['docente_nombre']}' en el aula '{$conflictInfo['aula_nombre']}'";
            $conflicts['grupo']['data'] = $conflictInfo;
        }

        return $conflicts;
    }

    private function getHorarios()
    {
        try {
            $sql = "SELECT h.*, 
                           g.numero as grupo_numero, g.semestre as grupo_semestre,
                           a.nombre as aula_nombre, a.codigo as aula_codigo,
                           u.nombre || ' ' || u.apellido as docente_nombre,
                           CASE h.dia_semana
                               WHEN 1 THEN 'Lunes'
                               WHEN 2 THEN 'Martes'
                               WHEN 3 THEN 'Miércoles'
                               WHEN 4 THEN 'Jueves'
                               WHEN 5 THEN 'Viernes'
                               WHEN 6 THEN 'Sábado'
                               WHEN 7 THEN 'Domingo'
                           END as dia_nombre
                    FROM horarios h
                    LEFT JOIN grupos g ON h.grupo_id = g.id
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    LEFT JOIN usuarios u ON h.docente_id = u.id
                    WHERE h.activo = true
                    ORDER BY h.dia_semana, h.hora_inicio";
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getHorario($id)
    {
        try {
            $sql = "SELECT h.*, 
                           g.numero as grupo_numero,
                           a.nombre as aula_nombre,
                           u.nombre || ' ' || u.apellido as docente_nombre
                    FROM horarios h
                    LEFT JOIN grupos g ON h.grupo_id = g.id
                    LEFT JOIN aulas a ON h.aula_id = a.id
                    LEFT JOIN usuarios u ON h.docente_id = u.id
                    WHERE h.id = :id AND h.activo = true";
            $result = $this->db->query($sql, [':id' => $id]);
            return $result ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getGrupos()
    {
        try {
            return $this->db->query("SELECT id, numero, semestre, turno FROM grupos WHERE activo = true ORDER BY numero");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAulas()
    {
        try {
            return $this->db->query("SELECT id, nombre, codigo, capacidad FROM aulas WHERE activa = true ORDER BY nombre");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getDocentes()
    {
        try {
            $sql = "SELECT u.id, u.nombre || ' ' || u.apellido as nombre
                    FROM usuarios u
                    INNER JOIN roles r ON u.rol_id = r.id
                    WHERE r.nombre = 'docente' AND u.activo = true
                    ORDER BY u.nombre, u.apellido";
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }
}
