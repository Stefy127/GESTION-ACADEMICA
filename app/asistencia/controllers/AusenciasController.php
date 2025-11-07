<?php
class AusenciasController extends Controller
{
    private $middleware;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->middleware = new Middleware();
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $this->middleware->requireAuth();
        $user = $this->middleware->getCurrentUser();

        // Registrar acceso al módulo
        ActivityLogger::logView('ausencias', null);

        $ausencias = $this->getAusencias($user);
        $incumplimientos = $this->getIncumplimientosDisponibles($user);

        $data = [
            'title' => 'Gestión de Ausencias',
            'user' => $user,
            'ausencias' => $ausencias,
            'incumplimientos' => $incumplimientos,
            'csrf_token' => $this->middleware->generateCSRFToken()
        ];

        return $this->view->renderWithLayout('asistencia/ausencias/index', $data);
    }

    public function store()
    {
        $this->middleware->requireAuth();

        if (!$this->isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $token = $this->getPost('csrf_token');
        if (!$this->middleware->verifyCSRFToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }

        $user = $this->middleware->getCurrentUser();

        $data = [
            'asistencia_id' => $this->getPost('asistencia_id'),
            'docente_id' => $this->getPost('docente_id'),
            'fecha' => $this->getPost('fecha'),
            'justificacion' => $this->getPost('justificacion'),
            'estado' => $this->getPost('estado') ?? 'pendiente'
        ];

        $errors = $this->validarDatosAusencia($data, $user, true);
        if (!empty($errors)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos', 'errors' => $errors]);
        }

        $docenteId = $this->resolverDocenteId($user, $data['docente_id'], $data['asistencia_id']);
        if (!$docenteId) {
            return $this->jsonResponse(['success' => false, 'message' => 'No se pudo determinar el docente para la ausencia']);
        }

        $fecha = $this->resolverFechaAusencia($data['fecha'], $data['asistencia_id']);
        if (!$fecha) {
            return $this->jsonResponse(['success' => false, 'message' => 'No se pudo determinar la fecha de la ausencia']);
        }

        $archivo = $this->procesarArchivoSoporte($_FILES['archivo_soporte'] ?? null);
        if ($archivo === false && !empty($_FILES['archivo_soporte']['name'])) {
            // Solo error si se intentó subir un archivo pero falló
            return $this->jsonResponse(['success' => false, 'message' => 'El archivo de soporte no es válido. Se permiten PDF, PNG, JPG, JPEG.']);
        }
        // Si no hay archivo, $archivo será null, lo cual está permitido

        try {
            // Evitar inserciones duplicadas en caso de doble envío rápido desde el cliente
            // Buscamos un registro muy reciente con los mismos datos clave
            $dupCheckSql = "SELECT id, created_at FROM ausencias_docente
                            WHERE docente_id = :docente_id
                              AND (asistencia_id IS NOT DISTINCT FROM :asistencia_id)
                              AND fecha = :fecha
                              AND COALESCE(justificacion, '') = COALESCE(:justificacion, '')
                            ORDER BY id DESC
                            LIMIT 1";
            $dupParams = [
                ':docente_id' => $docenteId,
                ':asistencia_id' => !empty($data['asistencia_id']) ? (int)$data['asistencia_id'] : null,
                ':fecha' => $fecha,
                ':justificacion' => $data['justificacion'] ?? null
            ];
            $dupResult = $this->db->query($dupCheckSql, $dupParams);
            if (!empty($dupResult)) {
                $last = $dupResult[0];
                if (!empty($last['created_at'])) {
                    $createdAt = new DateTime($last['created_at']);
                    $now = new DateTime();
                    $interval = $now->getTimestamp() - $createdAt->getTimestamp();
                    // Si el registro fue creado hace menos de 10 segundos, lo consideramos duplicado
                    if ($interval <= 10) {
                        return $this->jsonResponse(['success' => true, 'message' => 'Solicitud recibida (posible duplicado detectado).']);
                    }
                }
            }
            // Insertar y almacenar archivo en la base de datos (si se subió)
            $sql = "INSERT INTO ausencias_docente (docente_id, asistencia_id, fecha, justificacion, archivo_soporte_name, archivo_soporte_mime, archivo_soporte_base64, estado, creado_por, actualizado_por)
                    VALUES (:docente_id, :asistencia_id, :fecha, :justificacion, :archivo_name, :archivo_mime, :archivo_base64, :estado, :creado_por, :actualizado_por)";

            $params = [
                ':docente_id' => $docenteId,
                ':asistencia_id' => !empty($data['asistencia_id']) ? (int)$data['asistencia_id'] : null,
                ':fecha' => $fecha,
                ':justificacion' => $data['justificacion'] ?? null,
                ':archivo_name' => $archivo['name'] ?? null,
                ':archivo_mime' => $archivo['mime'] ?? null,
                ':archivo_base64' => $archivo['base64'] ?? null,
                ':estado' => $data['estado'],
                ':creado_por' => $user['id'],
                ':actualizado_por' => $user['id']
            ];

            $this->db->query($sql, $params);

            ActivityLogger::logCreate('ausencias_docente', null, $params);

            return $this->jsonResponse(['success' => true, 'message' => 'Ausencia registrada correctamente']);
        } catch (Exception $e) {
            error_log('Error creando ausencia: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al registrar la ausencia'], 500);
        }
    }

    public function update($id)
    {
        $this->middleware->requireAuth();

        if (!$this->isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $token = $this->getPost('csrf_token');
        if (!$this->middleware->verifyCSRFToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }

        $user = $this->middleware->getCurrentUser();
        $ausencia = $this->getAusenciaById($id);
        if (!$ausencia) {
            return $this->jsonResponse(['success' => false, 'message' => 'Ausencia no encontrada'], 404);
        }

        if ($user['rol'] === 'docente' && $ausencia['docente_id'] != $user['id']) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para actualizar esta ausencia'], 403);
        }

        $data = [
            'justificacion' => $this->getPost('justificacion'),
            'estado' => $this->getPost('estado') ?? $ausencia['estado']
        ];

        $errors = $this->validarDatosAusencia($data, $user, false);
        if (!empty($errors)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos', 'errors' => $errors]);
        }

        $archivo = $this->procesarArchivoSoporte($_FILES['archivo_soporte'] ?? null, $ausencia);
        if ($archivo === false) {
            return $this->jsonResponse(['success' => false, 'message' => 'El archivo de soporte no es válido. Se permiten PDF, PNG, JPG, JPEG.']);
        }

        try {
            // Actualizar ausencia y, si llegó un nuevo archivo, actualizar los campos correspondientes
            $sql = "UPDATE ausencias_docente
                    SET justificacion = :justificacion,
                        archivo_soporte_name = :archivo_name,
                        archivo_soporte_mime = :archivo_mime,
                        archivo_soporte_base64 = :archivo_base64,
                        estado = :estado,
                        actualizado_por = :actualizado_por,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";

            $params = [
                ':justificacion' => $data['justificacion'] ?? null,
                ':archivo_name' => $archivo['name'] ?? $ausencia['archivo_soporte_name'] ?? null,
                ':archivo_mime' => $archivo['mime'] ?? $ausencia['archivo_soporte_mime'] ?? null,
                ':archivo_base64' => $archivo['base64'] ?? $ausencia['archivo_soporte_base64'] ?? null,
                ':estado' => $data['estado'],
                ':actualizado_por' => $user['id'],
                ':id' => (int)$id
            ];

            $this->db->query($sql, $params);

            ActivityLogger::logUpdate('ausencias_docente', $id, $ausencia, $params);

            return $this->jsonResponse(['success' => true, 'message' => 'Ausencia actualizada correctamente']);
        } catch (Exception $e) {
            error_log('Error actualizando ausencia: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar la ausencia'], 500);
        }
    }

    public function delete($id)
    {
        $this->middleware->requireAuth();

        if (!$this->isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $token = $this->getPost('csrf_token');
        if (!$this->middleware->verifyCSRFToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido'], 403);
        }

        $user = $this->middleware->getCurrentUser();
        $ausencia = $this->getAusenciaById($id);
        if (!$ausencia) {
            return $this->jsonResponse(['success' => false, 'message' => 'Ausencia no encontrada'], 404);
        }

        if ($user['rol'] === 'docente' && $ausencia['docente_id'] != $user['id']) {
            return $this->jsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar esta ausencia'], 403);
        }

        try {
            $sql = "DELETE FROM ausencias_docente WHERE id = :id";
            $this->db->query($sql, [':id' => (int)$id]);

            // No hay archivo en FS cuando almacenamos en DB; si hubiera lógica adicional para limpiar
            // recursos asociados se puede agregar aquí. Actualmente no es necesario.

            ActivityLogger::logDelete('ausencias_docente', $id, $ausencia);

            return $this->jsonResponse(['success' => true, 'message' => 'Ausencia eliminada correctamente']);
        } catch (Exception $e) {
            error_log('Error eliminando ausencia: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar la ausencia'], 500);
        }
    }

    public function download($id)
    {
        $this->middleware->requireAuth();
        $ausencia = $this->getAusenciaById($id);

        if (!$ausencia || (empty($ausencia['archivo_soporte_name']) && empty($ausencia['archivo_soporte_base64']))) {
            http_response_code(404);
            echo 'Archivo no encontrado';
            exit;
        }

        $user = $this->middleware->getCurrentUser();
        if ($user['rol'] === 'docente' && $ausencia['docente_id'] != $user['id']) {
            http_response_code(403);
            echo 'No autorizado';
            exit;
        }

        // Leer archivo desde la base de datos (base64)
        $mime = $ausencia['archivo_soporte_mime'] ?? 'application/octet-stream';
        $nombre = $ausencia['archivo_soporte_name'] ?? ('ausencia_' . $ausencia['id']);
        $base64 = $ausencia['archivo_soporte_base64'] ?? '';
        if (empty($base64)) {
            http_response_code(404);
            echo 'Archivo no encontrado';
            exit;
        }

        $data = base64_decode($base64);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Content-Length: ' . strlen($data));
        echo $data;
        exit;
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

    private function validarDatosAusencia($data, $user, $isCreate = true)
    {
        $errors = [];

        if ($isCreate) {
            // Si no hay asistencia_id, la fecha es obligatoria
            if (empty($data['asistencia_id']) && empty($data['fecha'])) {
                $errors['fecha'] = 'Debes seleccionar un registro de asistencia o especificar una fecha.';
            }
            
            // Si hay asistencia_id pero no fecha, está bien (se obtendrá de la asistencia)
            // Si hay fecha pero no asistencia_id, también está bien
        }

        if (!empty($data['fecha']) && !strtotime($data['fecha'])) {
            $errors['fecha'] = 'La fecha no es válida';
        }

        if (empty($data['justificacion']) || trim($data['justificacion']) === '') {
            $errors['justificacion'] = 'La justificación es obligatoria';
        }

        if (!empty($data['estado']) && !in_array($data['estado'], ['pendiente', 'aprobado', 'rechazado'])) {
            $errors['estado'] = 'Estado inválido';
        }

        if ($user['rol'] === 'docente' && !empty($data['estado']) && $data['estado'] !== 'pendiente') {
            $errors['estado'] = 'No tienes permisos para cambiar el estado';
        }

        return $errors;
    }

    private function resolverDocenteId($user, $docenteId, $asistenciaId)
    {
        if ($user['rol'] === 'docente') {
            return $user['id'];
        }

        if (!empty($docenteId)) {
            return (int)$docenteId;
        }

        if (!empty($asistenciaId)) {
            $sql = "SELECT docente_id FROM asistencia_docente WHERE id = :id";
            $result = $this->db->query($sql, [':id' => (int)$asistenciaId]);
            return $result ? (int)$result[0]['docente_id'] : null;
        }

        return null;
    }

    private function resolverFechaAusencia($fecha, $asistenciaId)
    {
        if (!empty($asistenciaId)) {
            $sql = "SELECT fecha FROM asistencia_docente WHERE id = :id";
            $result = $this->db->query($sql, [':id' => (int)$asistenciaId]);
            if ($result) {
                return $result[0]['fecha'];
            }
        }

        return !empty($fecha) ? $fecha : null;
    }

    private function procesarArchivoSoporte($archivo, $archivoExistente = null)
    {
        // Ahora almacenamos archivos en la base de datos como base64.
        // Si no llega archivo, devolver null (o conservar existente si se pasa el arreglo de la ausencia)
        if (empty($archivo) || empty($archivo['name'])) {
            // Cuando se pasa $archivoExistente (registro de ausencia), devolvemos null para indicar que no hay cambio
            return null;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $mime = mime_content_type($archivo['tmp_name']);
        if (!in_array($mime, $permitidos)) {
            return false;
        }

        // Leer contenido y codificar en base64 para almacenamiento sencillo vía TEXT
        $content = file_get_contents($archivo['tmp_name']);
        if ($content === false) {
            error_log('No se pudo leer el archivo temporal: ' . ($archivo['tmp_name'] ?? '')); 
            return false;
        }

        $base64 = base64_encode($content);
        $originalName = basename($archivo['name']);

        return [
            'name' => $originalName,
            'mime' => $mime,
            'base64' => $base64
        ];
    }

    // Cuando los archivos se guardan en la BD no necesitamos eliminar archivos en disco.
    private function eliminarArchivo($archivo)
    {
        // Método conservado por compatibilidad; no hace nada cuando se usa almacenamiento en BD.
        return;
    }

    private function getAusenciaById($id)
    {
        $sql = "SELECT * FROM ausencias_docente WHERE id = :id";
        $result = $this->db->query($sql, [':id' => (int)$id]);
        return $result ? $result[0] : null;
    }

    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

