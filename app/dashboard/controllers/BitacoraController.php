<?php
/**
 * Controlador para la bitácora de actividades
 */
class BitacoraController extends Controller
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        // Solo administradores pueden ver la bitácora
        if (!Middleware::checkRole(['administrador'])) {
            return $this->view->renderWithLayout('errors/403', ['title' => 'Acceso Denegado']);
        }
        
        // Registrar acceso al módulo
        ActivityLogger::logView('bitacora', null);
        
        $user = $this->getCurrentUser();
        $activities = $this->getActivities();
        
        // Obtener opciones para filtros
        $usuarios = $this->getUsuarios();
        $acciones = $this->getAcciones();
        $tablas = $this->getTablas();
        
        $data = [
            'title' => 'Bitácora de Actividades',
            'user' => $user,
            'activities' => $activities,
            'usuarios' => $usuarios,
            'acciones' => $acciones,
            'tablas' => $tablas,
            'filtros' => [
                'usuario_id' => $this->getGet('usuario_id'),
                'accion' => $this->getGet('accion'),
                'tabla' => $this->getGet('tabla'),
                'busqueda' => $this->getGet('busqueda')
            ]
        ];
        
        return $this->view->renderWithLayout('bitacora/index', $data);
    }
    
    public function exportar()
    {
        // Solo administradores pueden exportar
        if (!Middleware::checkRole(['administrador'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $formato = $this->getGet('formato') ?: 'csv';
        $usuarioId = $this->getGet('usuario_id');
        
        $datos = $this->getActivitiesForExport($usuarioId);

        if ($formato === 'xlsx') {
            $this->exportarXLSX($datos, $usuarioId);
        } else {
            $this->exportarCSV($datos, $usuarioId);
        }
    }
    
    /**
     * Obtener actividades con paginación y filtros
     */
    private function getActivities()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        // Obtener filtros
        $usuarioId = $this->getGet('usuario_id');
        $accion = $this->getGet('accion');
        $tabla = $this->getGet('tabla');
        $busqueda = $this->getGet('busqueda');
        
        try {
            // Construir condiciones WHERE
            $whereConditions = [];
            $params = [];
            
            if (!empty($usuarioId)) {
                $whereConditions[] = "l.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioId;
            }
            
            if (!empty($accion)) {
                $whereConditions[] = "l.accion = :accion";
                $params[':accion'] = $accion;
            }
            
            if (!empty($tabla)) {
                $whereConditions[] = "l.tabla_afectada = :tabla";
                $params[':tabla'] = $tabla;
            }
            
            if (!empty($busqueda)) {
                $whereConditions[] = "(u.nombre || ' ' || u.apellido ILIKE :busqueda OR l.accion ILIKE :busqueda OR l.tabla_afectada ILIKE :busqueda OR l.ip_address ILIKE :busqueda)";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Contar total
            $countSql = "SELECT COUNT(*) as total 
                        FROM logs_actividad l
                        LEFT JOIN usuarios u ON l.usuario_id = u.id
                        $whereClause";
            $countResult = $this->db->query($countSql, $params);
            $total = $countResult[0]['total'] ?? 0;
            
            // Obtener actividades con paginación
            $sql = "SELECT 
                        l.id,
                        l.usuario_id,
                        u.nombre || ' ' || u.apellido as nombre_usuario,
                        l.accion,
                        l.tabla_afectada,
                        l.registro_id,
                        l.created_at,
                        l.ip_address,
                        l.user_agent,
                        l.datos_anteriores,
                        l.datos_nuevos
                    FROM logs_actividad l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    $whereClause
                    ORDER BY l.created_at DESC
                    LIMIT $limit OFFSET $offset";
            
            $activities = $this->db->query($sql, $params);
            
            return [
                'activities' => $activities,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error loading activities: " . $e->getMessage());
            return ['activities' => [], 'total' => 0, 'page' => 1, 'limit' => $limit];
        }
    }
    
    /**
     * Obtener actividades para exportación (sin paginación)
     */
    private function getActivitiesForExport($usuarioId = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            // Obtener filtros desde GET
            $usuarioIdFiltro = $this->getGet('usuario_id');
            $accion = $this->getGet('accion');
            $tabla = $this->getGet('tabla');
            $busqueda = $this->getGet('busqueda');
            
            // Usar usuario_id del parámetro o del filtro
            $usuarioIdFinal = $usuarioId ?? $usuarioIdFiltro;
            
            if (!empty($usuarioIdFinal)) {
                $whereConditions[] = "l.usuario_id = :usuario_id";
                $params[':usuario_id'] = $usuarioIdFinal;
            }
            
            if (!empty($accion)) {
                $whereConditions[] = "l.accion = :accion";
                $params[':accion'] = $accion;
            }
            
            if (!empty($tabla)) {
                $whereConditions[] = "l.tabla_afectada = :tabla";
                $params[':tabla'] = $tabla;
            }
            
            if (!empty($busqueda)) {
                $whereConditions[] = "(u.nombre || ' ' || u.apellido ILIKE :busqueda OR l.accion ILIKE :busqueda OR l.tabla_afectada ILIKE :busqueda OR l.ip_address ILIKE :busqueda)";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        l.id,
                        u.nombre || ' ' || u.apellido as nombre_usuario,
                        l.accion,
                        l.tabla_afectada,
                        l.registro_id,
                        l.created_at,
                        l.ip_address,
                        l.user_agent
                    FROM logs_actividad l
                    LEFT JOIN usuarios u ON l.usuario_id = u.id
                    $whereClause
                    ORDER BY l.created_at DESC";
            
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            error_log("Error loading activities for export: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener lista de usuarios que tienen actividades
     */
    private function getUsuarios()
    {
        try {
            $sql = "SELECT DISTINCT u.id, u.nombre || ' ' || u.apellido as nombre
                    FROM logs_actividad l
                    INNER JOIN usuarios u ON l.usuario_id = u.id
                    ORDER BY u.apellido, u.nombre";
            return $this->db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener lista de acciones únicas
     */
    private function getAcciones()
    {
        try {
            $sql = "SELECT DISTINCT accion
                    FROM logs_actividad
                    WHERE accion IS NOT NULL
                    ORDER BY accion";
            $result = $this->db->query($sql);
            return array_column($result, 'accion');
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener lista de tablas únicas
     */
    private function getTablas()
    {
        try {
            $sql = "SELECT DISTINCT tabla_afectada
                    FROM logs_actividad
                    WHERE tabla_afectada IS NOT NULL
                    ORDER BY tabla_afectada";
            $result = $this->db->query($sql);
            return array_column($result, 'tabla_afectada');
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Exportar a CSV
     */
    private function exportarCSV($datos, $usuarioId)
    {
        $csv = [];
        
        // Encabezados
        $csv[] = "ID,Usuario,Acción,Tabla,Registro ID,Fecha,IP,Navegador";
        
        // Datos
        foreach ($datos as $actividad) {
            $csv[] = sprintf('"%s","%s","%s","%s","%s","%s","%s","%s"',
                $actividad['id'],
                $this->limpiarCSV($actividad['nombre_usuario'] ?? 'Sistema'),
                $this->limpiarCSV($actividad['accion'] ?? ''),
                $this->limpiarCSV($actividad['tabla_afectada'] ?? ''),
                $actividad['registro_id'] ?? '',
                $actividad['created_at'] ?? '',
                $this->limpiarCSV($actividad['ip_address'] ?? ''),
                $this->limpiarCSV($actividad['user_agent'] ?? '')
            );
        }

        $nombreArchivo = $usuarioId ? 'bitacora-usuario-' . $usuarioId . '-' . date('Y-m-d') . '.csv' : 'bitacora-completa-' . date('Y-m-d') . '.csv';
        $this->descargarCSV($nombreArchivo, implode("\n", $csv));
    }
    
    /**
     * Exportar a XLSX
     */
    private function exportarXLSX($datos, $usuarioId)
    {
        if (!extension_loaded('zip')) {
            $this->exportarCSV($datos, $usuarioId);
            return;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        unlink($tempFile);
        $zipFile = $tempFile . '.xlsx';
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            $this->exportarCSV($datos, $usuarioId);
            return;
        }

        try {
            $this->crearEstructuraXLSX($zip, $datos);
            $zip->close();

            $nombreArchivo = $usuarioId ? 'bitacora-usuario-' . $usuarioId . '-' . date('Y-m-d') . '.xlsx' : 'bitacora-completa-' . date('Y-m-d') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . filesize($zipFile));
            header('Cache-Control: max-age=0');
            
            readfile($zipFile);
            unlink($zipFile);
            exit;

        } catch (Exception $e) {
            if ($zip->close() === false) {}
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }
            $this->exportarCSV($datos, $usuarioId);
        }
    }
    
    /**
     * Crear estructura XLSX
     */
    private function crearEstructuraXLSX($zip, $datos)
    {
        // Crear [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // Crear _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // Crear xl/_rels/workbook.xml.rels
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

        // Crear xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets>
<sheet name="Bitácora" sheetId="1" r:id="rId1"/>
</sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // Crear sharedStrings.xml
        $uniqueStrings = ['ID', 'Usuario', 'Acción', 'Tabla', 'Registro ID', 'Fecha', 'IP', 'Navegador'];
        foreach ($datos as $actividad) {
            $usuario = $actividad['nombre_usuario'] ?? 'Sistema';
            $accion = $actividad['accion'] ?? '';
            $tabla = $actividad['tabla_afectada'] ?? '';
            $fecha = $actividad['created_at'] ?? '';
            $ip = $actividad['ip_address'] ?? '';
            $navegador = $actividad['user_agent'] ?? '';
            
            if (!in_array($usuario, $uniqueStrings)) $uniqueStrings[] = $usuario;
            if (!in_array($accion, $uniqueStrings)) $uniqueStrings[] = $accion;
            if (!in_array($tabla, $uniqueStrings)) $uniqueStrings[] = $tabla;
            if (!in_array($fecha, $uniqueStrings)) $uniqueStrings[] = $fecha;
            if (!in_array($ip, $uniqueStrings)) $uniqueStrings[] = $ip;
            if (!in_array($navegador, $uniqueStrings)) $uniqueStrings[] = $navegador;
        }
        
        $sharedStringsXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($uniqueStrings) . '" uniqueCount="' . count($uniqueStrings) . '">';
        
        $stringMap = [];
        foreach ($uniqueStrings as $index => $string) {
            $stringMap[$string] = $index;
            $sharedStringsXML .= '<si><t>' . htmlspecialchars($string, ENT_XML1) . '</t></si>';
        }
        
        $sharedStringsXML .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXML);

        // Crear sheet1.xml
        $sheetXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData>';
        
        // Fila 1: Encabezados
        $sheetXML .= '<row r="1">';
        $headers = ['ID', 'Usuario', 'Acción', 'Tabla', 'Registro ID', 'Fecha', 'IP', 'Navegador'];
        $colIndex = 0;
        foreach ($headers as $header) {
            $colLetter = $this->indexToColumn($colIndex);
            $stringIndex = array_search($header, array_keys($stringMap));
            $sheetXML .= '<c r="' . $colLetter . '1" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
        }
        $sheetXML .= '</row>';
        
        // Filas de datos
        $rowNum = 2;
        foreach ($datos as $actividad) {
            $sheetXML .= '<row r="' . $rowNum . '">';
            $colIndex = 0;
            
            // ID
            $colLetter = $this->indexToColumn($colIndex);
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '"><v>' . $actividad['id'] . '</v></c>';
            $colIndex++;
            
            // Usuario
            $colLetter = $this->indexToColumn($colIndex);
            $usuario = $actividad['nombre_usuario'] ?? 'Sistema';
            $stringIndex = $stringMap[$usuario] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
            
            // Acción
            $colLetter = $this->indexToColumn($colIndex);
            $accion = $actividad['accion'] ?? '';
            $stringIndex = $stringMap[$accion] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
            
            // Tabla
            $colLetter = $this->indexToColumn($colIndex);
            $tabla = $actividad['tabla_afectada'] ?? '';
            $stringIndex = $stringMap[$tabla] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
            
            // Registro ID
            $colLetter = $this->indexToColumn($colIndex);
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '"><v>' . ($actividad['registro_id'] ?? '') . '</v></c>';
            $colIndex++;
            
            // Fecha
            $colLetter = $this->indexToColumn($colIndex);
            $fecha = $actividad['created_at'] ?? '';
            $stringIndex = $stringMap[$fecha] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
            
            // IP
            $colLetter = $this->indexToColumn($colIndex);
            $ip = $actividad['ip_address'] ?? '';
            $stringIndex = $stringMap[$ip] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
            
            // Navegador
            $colLetter = $this->indexToColumn($colIndex);
            $navegador = $actividad['user_agent'] ?? '';
            $stringIndex = $stringMap[$navegador] ?? 0;
            $sheetXML .= '<c r="' . $colLetter . $rowNum . '" t="s"><v>' . $stringIndex . '</v></c>';
            
            $sheetXML .= '</row>';
            $rowNum++;
        }
        
        $sheetXML .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXML);
    }
    
    /**
     * Convertir índice a letra de columna
     */
    private function indexToColumn($index)
    {
        $column = '';
        $index++;
        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)) . $column;
            $index = intval($index / 26);
        }
        return $column;
    }
    
    /**
     * Limpiar texto para CSV
     */
    private function limpiarCSV($texto)
    {
        if (empty($texto)) {
            return '';
        }
        $texto = str_replace(["\r\n", "\r", "\n"], " ", $texto);
        $texto = str_replace('"', '""', $texto);
        return trim($texto);
    }
    
    /**
     * Descargar CSV
     */
    private function descargarCSV($nombreArchivo, $contenido)
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "\xEF\xBB\xBF";
        echo $contenido;
        exit;
    }
}
