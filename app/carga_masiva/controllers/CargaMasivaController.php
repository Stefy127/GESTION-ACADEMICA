<?php
/**
 * Controlador para carga masiva
 */
class CargaMasivaController extends Controller
{
    private $db;
    private $uploadDir;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        // Definir directorio de uploads absoluto desde la raíz del proyecto
        // Usar __DIR__ para obtener la ruta actual del archivo
        // __DIR__ = /var/www/html/app/carga_masiva/controllers/
        // Necesitamos ir 3 niveles arriba para llegar a /var/www/html/
        $currentDir = __DIR__;
        $basePath = dirname(dirname(dirname($currentDir)));
        $this->uploadDir = $basePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        
        // Verificar que el path sea correcto (debe contener /var/www/html o la ruta del proyecto)
        if (strpos($this->uploadDir, '/var/www/html/') === false && strpos($this->uploadDir, 'html') === false) {
            // Si el path no parece correcto, usar path absoluto basado en public/index.php
            $publicIndexPath = __DIR__ . '/../../../../public/index.php';
            if (file_exists($publicIndexPath)) {
                $basePath = dirname(dirname($publicIndexPath));
                $this->uploadDir = $basePath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            }
        }
        
        // Crear directorio de uploads si no existe
        if (!file_exists($this->uploadDir)) {
            if (!@mkdir($this->uploadDir, 0777, true)) {
                error_log("Error: No se pudo crear el directorio de uploads: " . $this->uploadDir);
                // Intentar con sys_get_temp_dir() como alternativa
                $this->uploadDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gestion_academica_uploads' . DIRECTORY_SEPARATOR;
                if (!file_exists($this->uploadDir)) {
                    @mkdir($this->uploadDir, 0777, true);
                }
            }
        }
        
        // Asegurar permisos de escritura
        if (file_exists($this->uploadDir) && !is_writable($this->uploadDir)) {
            error_log("Error: El directorio de uploads no tiene permisos de escritura: " . $this->uploadDir);
            // Intentar cambiar permisos
            @chmod($this->uploadDir, 0777);
        }
        
        error_log("DEBUG CargaMasiva: Upload dir configurado como: " . $this->uploadDir);
    }

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

    public function descargarPlantilla($tipo, $formato = null)
    {
        if (!Middleware::checkRole(['administrador'])) {
            http_response_code(403);
            echo 'Acceso denegado';
            return;
        }

        $tiposValidos = ['docentes', 'materias', 'grupos', 'aulas', 'horarios'];
        if (!in_array($tipo, $tiposValidos)) {
            http_response_code(400);
            echo 'Tipo de plantilla inválido';
            return;
        }

        // Obtener formato desde GET si no viene como parámetro
        if ($formato === null) {
            $formato = $this->getGet('formato') ?: 'csv';
        }

        $formato = strtolower($formato);
        if (!in_array($formato, ['csv', 'xlsx'])) {
            $formato = 'csv';
        }

        $this->generarPlantilla($tipo, $formato);
    }

    public function procesar()
    {
        if (!Middleware::checkRole(['administrador'])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }

        try {
            $tipo = $_POST['tipo'] ?? '';
            $validar = isset($_POST['validar']) && $_POST['validar'] === 'true';
            $actualizar = isset($_POST['actualizar']) && $_POST['actualizar'] === 'true';

            if (empty($tipo)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de datos no especificado']);
                return;
            }

            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
                return;
            }

            $archivo = $_FILES['archivo'];
            
            // Validar tamaño (5MB máximo)
            if ($archivo['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo de 5MB']);
                return;
            }

            // Validar extensión
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de archivo no soportado. Use CSV, XLSX o XLS']);
                return;
            }

            // Validar que el directorio de uploads existe y es escribible
            if (!file_exists($this->uploadDir)) {
                if (!@mkdir($this->uploadDir, 0777, true)) {
                    // Intentar cambiar permisos del directorio padre
                    $parentDir = dirname($this->uploadDir);
                    if (file_exists($parentDir)) {
                        @chmod($parentDir, 0755);
                    }
                    if (!@mkdir($this->uploadDir, 0777, true)) {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Error: No se pudo crear el directorio de uploads. Path: ' . $this->uploadDir . '. Verifique permisos del servidor.'
                        ]);
                        return;
                    }
                }
            }
            
            // Intentar cambiar permisos si no es escribible
            if (!is_writable($this->uploadDir)) {
                @chmod($this->uploadDir, 0777);
                if (!is_writable($this->uploadDir)) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Error: El directorio de uploads no tiene permisos de escritura. Path: ' . $this->uploadDir
                    ]);
                    return;
                }
            }
            
            // Limpiar nombre del archivo (remover caracteres especiales)
            $nombreArchivoLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
            $nombreArchivo = uniqid() . '_' . $nombreArchivoLimpio;
            $rutaArchivo = rtrim($this->uploadDir, '/') . '/' . $nombreArchivo;
            
            if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                $errorMsg = 'Error al guardar el archivo. ';
                $errorMsg .= 'Path intentado: ' . $rutaArchivo . '. ';
                $errorMsg .= 'Directorio existe: ' . (file_exists($this->uploadDir) ? 'Sí' : 'No') . '. ';
                $errorMsg .= 'Directorio escribible: ' . (is_writable($this->uploadDir) ? 'Sí' : 'No');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                return;
            }

            // Procesar archivo según su tipo
            $resultado = $this->procesarArchivo($rutaArchivo, $tipo, $extension, $validar, $actualizar);

            // Guardar registro de carga masiva
            $user = $this->getCurrentUser();
            $this->guardarRegistroCarga($tipo, $archivo['name'], $resultado, $user['id']);

            // Eliminar archivo temporal
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Archivo procesado exitosamente',
                'resultado' => $resultado
            ]);

        } catch (Exception $e) {
            // Log del error para debugging
            error_log("Error en carga masiva: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'trace' => (ini_get('display_errors') ? $e->getTraceAsString() : null)
            ]);
        }
    }

    private function procesarArchivo($rutaArchivo, $tipo, $extension, $validar, $actualizar)
    {
        $datos = [];

        // Leer archivo según extensión
        if ($extension === 'csv') {
            $datos = $this->leerCSV($rutaArchivo);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $datos = $this->leerXLSX($rutaArchivo);
        } else {
            throw new Exception('Formato de archivo no soportado. Use CSV o XLSX.');
        }

        if (empty($datos)) {
            throw new Exception('El archivo está vacío o no se pudo leer. Verifique que el archivo tenga datos además de los encabezados.');
        }

        // Procesar según tipo
        switch ($tipo) {
            case 'docentes':
                return $this->procesarDocentes($datos, $validar, $actualizar);
            case 'materias':
                return $this->procesarMaterias($datos, $validar, $actualizar);
            case 'grupos':
                return $this->procesarGrupos($datos, $validar, $actualizar);
            case 'aulas':
                return $this->procesarAulas($datos, $validar, $actualizar);
            case 'horarios':
                return $this->procesarHorarios($datos, $validar, $actualizar);
            default:
                throw new Exception('Tipo de datos no válido');
        }
    }

    private function leerCSV($rutaArchivo)
    {
        $datos = [];
        
        // Leer el contenido del archivo y limpiar BOM UTF-8 si existe
        $contenido = file_get_contents($rutaArchivo);
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido); // Remover BOM UTF-8
        
        // Guardar temporalmente sin BOM
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tempFile, $contenido);
        
        $handle = fopen($tempFile, 'r');
        
        if ($handle === false) {
            unlink($tempFile);
            throw new Exception('No se pudo abrir el archivo');
        }

        // Leer encabezados
        $encabezados = fgetcsv($handle);
        if ($encabezados === false || empty($encabezados)) {
            fclose($handle);
            unlink($tempFile);
            throw new Exception('No se pudieron leer los encabezados del archivo. Verifique que el archivo tenga una fila de encabezados.');
        }

        // Limpiar encabezados (quitar espacios y convertir a minúsculas para comparación)
        $encabezados = array_map('trim', $encabezados);
        $encabezadosOriginales = $encabezados; // Guardar originales para el array_combine

        // Leer datos
        $filaNumero = 1; // Contador para mensajes de error
        while (($fila = fgetcsv($handle)) !== false) {
            $filaNumero++;
            
            // Filtrar filas vacías
            if (empty(array_filter($fila))) {
                continue;
            }
            
            // Normalizar número de columnas
            if (count($fila) < count($encabezados)) {
                // Rellenar con valores vacíos si faltan columnas
                $fila = array_pad($fila, count($encabezados), '');
            } elseif (count($fila) > count($encabezados)) {
                // Truncar si hay columnas extras
                $fila = array_slice($fila, 0, count($encabezados));
            }
            
            try {
                $filaCombinada = array_combine($encabezadosOriginales, $fila);
                if ($filaCombinada !== false) {
                    // Limpiar valores de la fila
                    $filaCombinada = array_map('trim', $filaCombinada);
                    $datos[] = $filaCombinada;
                }
            } catch (Exception $e) {
                // Si hay error al combinar, continuar con la siguiente fila
                error_log("Error al procesar fila $filaNumero: " . $e->getMessage());
                continue;
            }
        }

        fclose($handle);
        unlink($tempFile);
        
        return $datos;
    }

    private function leerXLSX($rutaArchivo)
    {
        $datos = [];
        
        // Verificar que la extensión ZIP esté disponible
        if (!extension_loaded('zip')) {
            throw new Exception('La extensión ZIP de PHP no está disponible. No se puede leer archivos XLSX.');
        }

        // Abrir el archivo XLSX como ZIP
        $zip = new ZipArchive();
        if ($zip->open($rutaArchivo) !== TRUE) {
            throw new Exception('No se pudo abrir el archivo XLSX. Verifique que el archivo no esté corrupto.');
        }

        try {
            // Leer sharedStrings.xml para obtener los strings compartidos
            $sharedStrings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $sharedStringsXML = $zip->getFromName('xl/sharedStrings.xml');
                if ($sharedStringsXML !== false) {
                    $xml = simplexml_load_string($sharedStringsXML);
                    if ($xml !== false && isset($xml->si)) {
                        foreach ($xml->si as $si) {
                            $value = '';
                            if (isset($si->t)) {
                                $value = (string)$si->t;
                            }
                            $sharedStrings[] = $value;
                        }
                    }
                }
            }

            // Leer la primera hoja (xl/worksheets/sheet1.xml)
            $sheetFiles = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet.xml', 'sheet1.xml', 'xl/workbook.xml'];
            $sheetContent = false;
            $sheetFile = null;
            
            foreach ($sheetFiles as $file) {
                if ($zip->locateName($file) !== false) {
                    $sheetContent = $zip->getFromName($file);
                    $sheetFile = $file;
                    break;
                }
            }

            // Si no encontramos sheet1.xml, buscar la primera hoja disponible
            if ($sheetContent === false) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    if (strpos($filename, 'xl/worksheets/sheet') !== false && strpos($filename, '.xml') !== false) {
                        $sheetContent = $zip->getFromName($filename);
                        $sheetFile = $filename;
                        break;
                    }
                }
            }

            if ($sheetContent === false) {
                throw new Exception('No se pudo encontrar la hoja de cálculo en el archivo XLSX.');
            }

            // Parsear el XML de la hoja
            $xml = simplexml_load_string($sheetContent);
            if ($xml === false) {
                throw new Exception('No se pudo parsear el contenido de la hoja de cálculo.');
            }

            // Obtener namespaces del XML
            $namespaces = $xml->getNamespaces(true);
            $mainNamespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            
            // Intentar obtener el namespace por defecto
            if (isset($namespaces[''])) {
                $mainNamespace = $namespaces[''];
            }
            
            // Intentar leer filas de diferentes formas
            $rows = false;
            
            // Método 1: Intentar con namespace registrado
            try {
                @$xml->registerXPathNamespace('x', $mainNamespace);
                $rows = @$xml->xpath('//x:row');
            } catch (Exception $e) {
                error_log("Error al leer con namespace x: " . $e->getMessage());
            }
            
            // Método 2: Intentar sin namespace si falla
            if ($rows === false || empty($rows)) {
                try {
                    $rows = @$xml->xpath('//row');
                } catch (Exception $e) {
                    error_log("Error al leer sin namespace: " . $e->getMessage());
                }
            }
            
            // Método 3: Leer directamente usando children() si xpath falla
            if ($rows === false || empty($rows)) {
                try {
                    $sheetData = $xml->children($mainNamespace);
                    if (isset($sheetData->sheetData)) {
                        $sheetData = $sheetData->sheetData;
                    }
                    if (isset($sheetData->row)) {
                        $rows = [];
                        foreach ($sheetData->row as $row) {
                            $rows[] = $row;
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error al leer con children: " . $e->getMessage());
                }
            }
            
            // Método 4: Intentar sin namespace usando children()
            if ($rows === false || empty($rows)) {
                try {
                    $sheetData = $xml->children();
                    if (isset($sheetData->sheetData)) {
                        $sheetData = $sheetData->sheetData;
                    }
                    if (isset($sheetData->row)) {
                        $rows = [];
                        foreach ($sheetData->row as $row) {
                            $rows[] = $row;
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error al leer con children sin namespace: " . $e->getMessage());
                }
            }
            
            if ($rows === false || empty($rows)) {
                throw new Exception('No se encontraron datos en el archivo XLSX. Verifique que la primera fila contenga encabezados y las siguientes filas contengan datos.');
            }
            
            $encabezados = [];
            $esPrimeraFila = true;

            foreach ($rows as $row) {
                $rowData = [];
                
                // Intentar obtener celdas de diferentes formas
                $cells = false;
                
                // Método 1: Con namespace
                try {
                    @$row->registerXPathNamespace('x', $mainNamespace);
                    $cells = @$row->xpath('.//x:c');
                } catch (Exception $e) {
                    // Ignorar error
                }
                
                // Método 2: Sin namespace
                if ($cells === false || empty($cells)) {
                    try {
                        $cells = @$row->xpath('.//c');
                    } catch (Exception $e) {
                        // Ignorar error
                    }
                }
                
                // Método 3: Usando children()
                if ($cells === false || empty($cells)) {
                    try {
                        $rowChildren = $row->children($mainNamespace);
                        if (isset($rowChildren->c)) {
                            $cells = [];
                            foreach ($rowChildren->c as $cell) {
                                $cells[] = $cell;
                            }
                        }
                    } catch (Exception $e) {
                        // Ignorar error
                    }
                }
                
                // Método 4: Usando children() sin namespace
                if ($cells === false || empty($cells)) {
                    try {
                        $rowChildren = $row->children();
                        if (isset($rowChildren->c)) {
                            $cells = [];
                            foreach ($rowChildren->c as $cell) {
                                $cells[] = $cell;
                            }
                        }
                    } catch (Exception $e) {
                        // Ignorar error
                    }
                }
                
                if ($cells === false || empty($cells)) {
                    continue; // Saltar fila si no tiene celdas
                }
                
                // Obtener el índice de la fila
                $rowIndex = isset($row['r']) ? (int)$row['r'] : null;
                
                foreach ($cells as $cell) {
                    $cellValue = '';
                    $cellType = isset($cell['t']) ? (string)$cell['t'] : '';
                    
                    // Obtener la columna desde el atributo 'r' (ej: "A1", "B2")
                    $r = isset($cell['r']) ? (string)$cell['r'] : '';
                    $colIndex = 0;
                    
                    if ($r) {
                        preg_match('/^([A-Z]+)/', $r, $matches);
                        if (!empty($matches[1])) {
                            $colIndex = $this->columnToIndex($matches[1]);
                        }
                    }
                    
                    // Leer el valor de la celda - intentar múltiples métodos
                    $cellValue = '';
                    
                    // Método 1: Con namespace usando children()
                    try {
                        $cellChildren = $cell->children($mainNamespace);
                        if (isset($cellChildren->v)) {
                            $rawValue = (string)$cellChildren->v;
                            
                            if ($cellType === 's') {
                                $index = (int)$rawValue;
                                if (isset($sharedStrings[$index])) {
                                    $cellValue = $sharedStrings[$index];
                                }
                            } elseif ($cellType === 'str' || $cellType === 'inlineStr') {
                                if (isset($cellChildren->is)) {
                                    $isChildren = $cellChildren->is->children($mainNamespace);
                                    if (isset($isChildren->t)) {
                                        $cellValue = (string)$isChildren->t;
                                    } else {
                                        $cellValue = $rawValue;
                                    }
                                } else {
                                    $cellValue = $rawValue;
                                }
                            } else {
                                $cellValue = $rawValue;
                            }
                        } elseif (isset($cellChildren->is)) {
                            $isChildren = $cellChildren->is->children($mainNamespace);
                            if (isset($isChildren->t)) {
                                $cellValue = (string)$isChildren->t;
                            }
                        }
                    } catch (Exception $e) {
                        // Continuar con siguiente método
                    }
                    
                    // Método 2: Sin namespace usando children()
                    if (empty($cellValue)) {
                        try {
                            $cellChildrenNoNS = $cell->children();
                            if (isset($cellChildrenNoNS->v)) {
                                $rawValue = (string)$cellChildrenNoNS->v;
                                
                                if ($cellType === 's') {
                                    $index = (int)$rawValue;
                                    if (isset($sharedStrings[$index])) {
                                        $cellValue = $sharedStrings[$index];
                                    }
                                } else {
                                    $cellValue = $rawValue;
                                }
                            } elseif (isset($cellChildrenNoNS->is)) {
                                $isChildren = $cellChildrenNoNS->is->children();
                                if (isset($isChildren->t)) {
                                    $cellValue = (string)$isChildren->t;
                                }
                            }
                        } catch (Exception $e) {
                            // Continuar con siguiente método
                        }
                    }
                    
                    // Método 3: Acceso directo a propiedades
                    if (empty($cellValue)) {
                        try {
                            if (isset($cell->v)) {
                                $rawValue = (string)$cell->v;
                                
                                if ($cellType === 's') {
                                    $index = (int)$rawValue;
                                    if (isset($sharedStrings[$index])) {
                                        $cellValue = $sharedStrings[$index];
                                    }
                                } else {
                                    $cellValue = $rawValue;
                                }
                            } elseif (isset($cell->is)) {
                                if (isset($cell->is->t)) {
                                    $cellValue = (string)$cell->is->t;
                                }
                            }
                        } catch (Exception $e) {
                            // Valor queda vacío
                        }
                    }

                    // Almacenar en la posición correcta
                    $rowData[$colIndex] = $cellValue;
                }

                // Si es la primera fila, usar como encabezados
                if ($esPrimeraFila) {
                    // Normalizar encabezados - crear array ordenado
                    $maxCol = !empty($rowData) ? max(array_keys($rowData)) : -1;
                    $encabezadosArray = [];
                    for ($i = 0; $i <= $maxCol; $i++) {
                        $encabezadosArray[] = isset($rowData[$i]) ? trim($rowData[$i]) : '';
                    }
                    $encabezados = array_map('strtolower', $encabezadosArray);
                    $esPrimeraFila = false;
                } else {
                    // Combinar con encabezados
                    if (!empty($encabezados)) {
                        $maxCol = max(max(array_keys($rowData)), count($encabezados) - 1);
                        $normalizedRow = [];
                        
                        for ($i = 0; $i < count($encabezados); $i++) {
                            $header = $encabezados[$i];
                            $normalizedRow[$header] = isset($rowData[$i]) ? trim($rowData[$i]) : '';
                        }
                        
                        // Filtrar filas vacías
                        if (!empty(array_filter($normalizedRow, function($v) { return $v !== ''; }))) {
                            $datos[] = $normalizedRow;
                        }
                    }
                }
            }

        } finally {
            $zip->close();
        }

        if (empty($datos)) {
            throw new Exception('No se encontraron datos en el archivo XLSX. Verifique que la primera fila contenga encabezados y las siguientes filas contengan datos.');
        }

        error_log("XLSX leído exitosamente: " . count($datos) . " filas de datos");
        return $datos;
    }

    /**
     * Convertir letra de columna a índice (A=0, B=1, ..., Z=25, AA=26, etc.)
     */
    private function columnToIndex($column)
    {
        $index = 0;
        $length = strlen($column);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function procesarDocentes($datos, $validar, $actualizar)
    {
        $resultado = [
            'total' => count($datos),
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        $rolDocenteId = $this->getRolId('docente');
        if (!$rolDocenteId) {
            throw new Exception('No se encontró el rol de docente');
        }

        foreach ($datos as $index => $fila) {
            $numeroFila = $index + 2; // +2 porque la fila 1 son encabezados y el índice empieza en 0

            try {
                // Normalizar claves del array (mayúsculas/minúsculas)
                $fila = array_change_key_case($fila, CASE_LOWER);
                
                // Validar campos requeridos
                $camposRequeridos = ['ci', 'nombre', 'apellido', 'email'];
                foreach ($camposRequeridos as $campo) {
                    if (!isset($fila[$campo]) || trim($fila[$campo]) === '') {
                        throw new Exception("Campo requerido vacío: $campo");
                    }
                }

                // Validar email
                if ($validar && !filter_var($fila['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email inválido: {$fila['email']}");
                }

                // Verificar si existe
                $existe = $this->db->query("SELECT id FROM usuarios WHERE ci = :ci OR email = :email", [
                    ':ci' => $fila['ci'],
                    ':email' => $fila['email']
                ]);

                if (!empty($existe)) {
                    if ($actualizar) {
                        // Actualizar
                        $passwordHash = !empty($fila['password']) ? password_hash($fila['password'], PASSWORD_DEFAULT) : $existe[0]['password_hash'];
                        $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, 
                                telefono = :telefono, password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                        $this->db->query($sql, [
                            ':nombre' => $fila['nombre'],
                            ':apellido' => $fila['apellido'],
                            ':email' => $fila['email'],
                            ':telefono' => $fila['telefono'] ?? null,
                            ':password_hash' => $passwordHash,
                            ':id' => $existe[0]['id']
                        ]);
                        $resultado['exitosos']++;
                    } else {
                        throw new Exception("Usuario ya existe (CI: {$fila['ci']} o Email: {$fila['email']})");
                    }
                } else {
                    // Crear nuevo
                    $password = $fila['password'] ?? 'password123'; // Contraseña por defecto
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    
                    try {
                        // Verificar si la columna password_changed existe
                        $checkColumnSql = "SELECT COUNT(*) as total 
                                          FROM information_schema.columns 
                                          WHERE table_name = 'usuarios' 
                                          AND column_name = 'password_changed'";
                        $columnResult = $this->db->query($checkColumnSql);
                        $hasPasswordChangedColumn = (($columnResult[0]['total'] ?? 0) > 0);
                        
                        if ($hasPasswordChangedColumn) {
                            // Para docentes creados por carga masiva, establecer password_changed en false
                            $sql = "INSERT INTO usuarios (ci, nombre, apellido, email, telefono, password_hash, rol_id, activo, password_changed)
                                    VALUES (:ci, :nombre, :apellido, :email, :telefono, :password_hash, :rol_id, true, false)";
                        } else {
                            $sql = "INSERT INTO usuarios (ci, nombre, apellido, email, telefono, password_hash, rol_id, activo)
                                    VALUES (:ci, :nombre, :apellido, :email, :telefono, :password_hash, :rol_id, true)";
                        }
                        
                        $this->db->query($sql, [
                            ':ci' => trim($fila['ci']),
                            ':nombre' => trim($fila['nombre']),
                            ':apellido' => trim($fila['apellido']),
                            ':email' => trim($fila['email']),
                            ':telefono' => !empty($fila['telefono']) ? trim($fila['telefono']) : null,
                            ':password_hash' => $passwordHash,
                            ':rol_id' => $rolDocenteId
                        ]);
                        $resultado['exitosos']++;
                    } catch (Exception $dbError) {
                        // Capturar errores específicos de base de datos
                        $errorMsg = $dbError->getMessage();
                        if (strpos($errorMsg, 'duplicate key') !== false || strpos($errorMsg, 'unique constraint') !== false) {
                            throw new Exception("Usuario duplicado (CI: {$fila['ci']} o Email: {$fila['email']})");
                        } else {
                            throw new Exception("Error de base de datos: " . $errorMsg);
                        }
                    }
                }
            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = "Fila $numeroFila: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    private function procesarMaterias($datos, $validar, $actualizar)
    {
        $resultado = [
            'total' => count($datos),
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        foreach ($datos as $index => $fila) {
            $numeroFila = $index + 2;

            try {
                // Validar campos requeridos
                if (empty($fila['codigo']) || empty($fila['nombre'])) {
                    throw new Exception("Campos requeridos vacíos: código o nombre");
                }

                // Verificar si existe
                $existe = $this->db->query("SELECT id FROM materias WHERE codigo = :codigo", [
                    ':codigo' => $fila['codigo']
                ]);

                if (!empty($existe)) {
                    if ($actualizar) {
                        $sql = "UPDATE materias SET nombre = :nombre, descripcion = :descripcion, 
                                nivel = :nivel, carga_horaria = :carga_horaria, updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                        $this->db->query($sql, [
                            ':nombre' => $fila['nombre'],
                            ':descripcion' => $fila['descripcion'] ?? null,
                            ':nivel' => $fila['nivel'] ?? null,
                            ':carga_horaria' => intval($fila['carga_horaria'] ?? 0),
                            ':id' => $existe[0]['id']
                        ]);
                        $resultado['exitosos']++;
                    } else {
                        throw new Exception("Materia ya existe (Código: {$fila['codigo']})");
                    }
                } else {
                    $sql = "INSERT INTO materias (codigo, nombre, descripcion, nivel, carga_horaria, activa)
                            VALUES (:codigo, :nombre, :descripcion, :nivel, :carga_horaria, true)";
                    $this->db->query($sql, [
                        ':codigo' => $fila['codigo'],
                        ':nombre' => $fila['nombre'],
                        ':descripcion' => $fila['descripcion'] ?? null,
                        ':nivel' => $fila['nivel'] ?? null,
                        ':carga_horaria' => intval($fila['carga_horaria'] ?? 0)
                    ]);
                    $resultado['exitosos']++;
                }
            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = "Fila $numeroFila: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    private function procesarGrupos($datos, $validar, $actualizar)
    {
        $resultado = [
            'total' => count($datos),
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        foreach ($datos as $index => $fila) {
            $numeroFila = $index + 2;

            try {
                // Validar campos requeridos
                if (empty($fila['numero'])) {
                    throw new Exception("Campo requerido vacío: numero");
                }

                // Obtener materia_id si se proporciona código de materia
                $materiaId = null;
                if (!empty($fila['materia_codigo'])) {
                    $materia = $this->db->query("SELECT id FROM materias WHERE codigo = :codigo", [
                        ':codigo' => $fila['materia_codigo']
                    ]);
                    if (empty($materia)) {
                        throw new Exception("Materia no encontrada: {$fila['materia_codigo']}");
                    }
                    $materiaId = $materia[0]['id'];
                }

                // Obtener docente_id si se proporciona email o CI
                $docenteId = null;
                if (!empty($fila['docente_email']) || !empty($fila['docente_ci'])) {
                    $condicion = !empty($fila['docente_email']) ? 'email = :docente' : 'ci = :docente';
                    $valor = !empty($fila['docente_email']) ? $fila['docente_email'] : $fila['docente_ci'];
                    $docente = $this->db->query("SELECT id FROM usuarios WHERE $condicion AND rol_id = (SELECT id FROM roles WHERE nombre = 'docente')", [
                        ':docente' => $valor
                    ]);
                    if (!empty($docente)) {
                        $docenteId = $docente[0]['id'];
                    }
                }

                // Verificar si existe
                $existe = $this->db->query("SELECT id FROM grupos WHERE numero = :numero AND activo = true", [
                    ':numero' => $fila['numero']
                ]);

                if (!empty($existe)) {
                    if ($actualizar) {
                        $sql = "UPDATE grupos SET semestre = :semestre, turno = :turno, 
                                materia_id = :materia_id, docente_id = :docente_id, 
                                capacidad_maxima = :capacidad_maxima, updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                        $this->db->query($sql, [
                            ':semestre' => $fila['semestre'] ?? null,
                            ':turno' => $fila['turno'] ?? null,
                            ':materia_id' => $materiaId,
                            ':docente_id' => $docenteId,
                            ':capacidad_maxima' => intval($fila['capacidad_maxima'] ?? 30),
                            ':id' => $existe[0]['id']
                        ]);
                        $resultado['exitosos']++;
                    } else {
                        throw new Exception("Grupo ya existe (Número: {$fila['numero']})");
                    }
                } else {
                    $sql = "INSERT INTO grupos (numero, semestre, turno, materia_id, docente_id, capacidad_maxima, activo)
                            VALUES (:numero, :semestre, :turno, :materia_id, :docente_id, :capacidad_maxima, true)";
                    $this->db->query($sql, [
                        ':numero' => $fila['numero'],
                        ':semestre' => $fila['semestre'] ?? null,
                        ':turno' => $fila['turno'] ?? null,
                        ':materia_id' => $materiaId,
                        ':docente_id' => $docenteId,
                        ':capacidad_maxima' => intval($fila['capacidad_maxima'] ?? 30)
                    ]);
                    $resultado['exitosos']++;
                }
            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = "Fila $numeroFila: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    private function procesarAulas($datos, $validar, $actualizar)
    {
        $resultado = [
            'total' => count($datos),
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        foreach ($datos as $index => $fila) {
            $numeroFila = $index + 2;

            try {
                // Validar campos requeridos
                if (empty($fila['codigo']) || empty($fila['nombre'])) {
                    throw new Exception("Campos requeridos vacíos: código o nombre");
                }

                // Verificar si existe
                $existe = $this->db->query("SELECT id FROM aulas WHERE codigo = :codigo", [
                    ':codigo' => $fila['codigo']
                ]);

                if (!empty($existe)) {
                    if ($actualizar) {
                        $sql = "UPDATE aulas SET nombre = :nombre, capacidad = :capacidad, 
                                tipo = :tipo, ubicacion = :ubicacion, equipamiento = :equipamiento, 
                                updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                        $this->db->query($sql, [
                            ':nombre' => $fila['nombre'],
                            ':capacidad' => intval($fila['capacidad'] ?? 0),
                            ':tipo' => $fila['tipo'] ?? null,
                            ':ubicacion' => $fila['ubicacion'] ?? null,
                            ':equipamiento' => $fila['equipamiento'] ?? null,
                            ':id' => $existe[0]['id']
                        ]);
                        $resultado['exitosos']++;
                    } else {
                        throw new Exception("Aula ya existe (Código: {$fila['codigo']})");
                    }
                } else {
                    $sql = "INSERT INTO aulas (codigo, nombre, capacidad, tipo, ubicacion, equipamiento, activa)
                            VALUES (:codigo, :nombre, :capacidad, :tipo, :ubicacion, :equipamiento, true)";
                    $this->db->query($sql, [
                        ':codigo' => $fila['codigo'],
                        ':nombre' => $fila['nombre'],
                        ':capacidad' => intval($fila['capacidad'] ?? 0),
                        ':tipo' => $fila['tipo'] ?? null,
                        ':ubicacion' => $fila['ubicacion'] ?? null,
                        ':equipamiento' => $fila['equipamiento'] ?? null
                    ]);
                    $resultado['exitosos']++;
                }
            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = "Fila $numeroFila: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    private function procesarHorarios($datos, $validar, $actualizar)
    {
        $resultado = [
            'total' => count($datos),
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        foreach ($datos as $index => $fila) {
            $numeroFila = $index + 2;

            try {
                // Validar campos requeridos
                $camposRequeridos = ['grupo_numero', 'dia_semana', 'hora_inicio', 'hora_fin'];
                foreach ($camposRequeridos as $campo) {
                    if (empty($fila[$campo])) {
                        throw new Exception("Campo requerido vacío: $campo");
                    }
                }

                // Obtener grupo_id
                $grupo = $this->db->query("SELECT id FROM grupos WHERE numero = :numero AND activo = true", [
                    ':numero' => $fila['grupo_numero']
                ]);
                if (empty($grupo)) {
                    throw new Exception("Grupo no encontrado: {$fila['grupo_numero']}");
                }
                $grupoId = $grupo[0]['id'];

                // Obtener aula_id si se proporciona código
                $aulaId = null;
                if (!empty($fila['aula_codigo'])) {
                    $aula = $this->db->query("SELECT id FROM aulas WHERE codigo = :codigo", [
                        ':codigo' => $fila['aula_codigo']
                    ]);
                    if (!empty($aula)) {
                        $aulaId = $aula[0]['id'];
                    }
                }

                // Obtener docente_id si se proporciona
                $docenteId = null;
                if (!empty($fila['docente_email']) || !empty($fila['docente_ci'])) {
                    $condicion = !empty($fila['docente_email']) ? 'email = :docente' : 'ci = :docente';
                    $valor = !empty($fila['docente_email']) ? $fila['docente_email'] : $fila['docente_ci'];
                    $docente = $this->db->query("SELECT id FROM usuarios WHERE $condicion AND rol_id = (SELECT id FROM roles WHERE nombre = 'docente')", [
                        ':docente' => $valor
                    ]);
                    if (!empty($docente)) {
                        $docenteId = $docente[0]['id'];
                    }
                }

                // Convertir día de la semana
                $diaSemana = $this->convertirDiaSemana($fila['dia_semana']);

                // Validar horas
                if (strtotime($fila['hora_fin']) <= strtotime($fila['hora_inicio'])) {
                    throw new Exception("La hora de fin debe ser posterior a la hora de inicio");
                }

                // Verificar conflictos si se valida
                if ($validar) {
                    $conflicts = $this->checkConflicts($grupoId, $aulaId, $docenteId, $diaSemana, $fila['hora_inicio'], $fila['hora_fin']);
                    $hasConflicts = false;
                    foreach ($conflicts as $conflict) {
                        if ($conflict['exists']) {
                            $hasConflicts = true;
                            break;
                        }
                    }
                    if ($hasConflicts) {
                        throw new Exception("Conflicto de horario detectado");
                    }
                }

                // Insertar horario
                $sql = "INSERT INTO horarios (grupo_id, aula_id, docente_id, dia_semana, hora_inicio, hora_fin, activo)
                        VALUES (:grupo_id, :aula_id, :docente_id, :dia_semana, :hora_inicio, :hora_fin, true)";
                $this->db->query($sql, [
                    ':grupo_id' => $grupoId,
                    ':aula_id' => $aulaId,
                    ':docente_id' => $docenteId,
                    ':dia_semana' => $diaSemana,
                    ':hora_inicio' => $fila['hora_inicio'],
                    ':hora_fin' => $fila['hora_fin']
                ]);
                $resultado['exitosos']++;

            } catch (Exception $e) {
                $resultado['fallidos']++;
                $resultado['errores'][] = "Fila $numeroFila: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    private function convertirDiaSemana($dia)
    {
        $dias = [
            'lunes' => 1, 'monday' => 1, '1' => 1,
            'martes' => 2, 'tuesday' => 2, '2' => 2,
            'miercoles' => 3, 'miércoles' => 3, 'wednesday' => 3, '3' => 3,
            'jueves' => 4, 'thursday' => 4, '4' => 4,
            'viernes' => 5, 'friday' => 5, '5' => 5,
            'sabado' => 6, 'sábado' => 6, 'saturday' => 6, '6' => 6,
            'domingo' => 7, 'sunday' => 7, '7' => 7
        ];
        
        $diaLower = strtolower(trim($dia));
        return isset($dias[$diaLower]) ? $dias[$diaLower] : null;
    }

    private function checkConflicts($grupoId, $aulaId, $docenteId, $diaSemana, $horaInicio, $horaFin)
    {
        // Reutilizar lógica del HorariosController si es posible
        // Por ahora, una versión simplificada
        $conflicts = [
            'aula' => ['exists' => false, 'message' => ''],
            'docente' => ['exists' => false, 'message' => ''],
            'grupo' => ['exists' => false, 'message' => '']
        ];

        if ($aulaId) {
            $sql = "SELECT COUNT(*) as count FROM horarios 
                    WHERE aula_id = :aula_id AND dia_semana = :dia_semana 
                    AND activo = true
                    AND (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)";
            $result = $this->db->query($sql, [
                ':aula_id' => $aulaId,
                ':dia_semana' => $diaSemana,
                ':hora_fin' => $horaFin,
                ':hora_inicio' => $horaInicio
            ]);
            if (!empty($result) && $result[0]['count'] > 0) {
                $conflicts['aula']['exists'] = true;
            }
        }

        if ($docenteId) {
            $sql = "SELECT COUNT(*) as count FROM horarios 
                    WHERE docente_id = :docente_id AND dia_semana = :dia_semana 
                    AND activo = true
                    AND (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)";
            $result = $this->db->query($sql, [
                ':docente_id' => $docenteId,
                ':dia_semana' => $diaSemana,
                ':hora_fin' => $horaFin,
                ':hora_inicio' => $horaInicio
            ]);
            if (!empty($result) && $result[0]['count'] > 0) {
                $conflicts['docente']['exists'] = true;
            }
        }

        if ($grupoId) {
            $sql = "SELECT COUNT(*) as count FROM horarios 
                    WHERE grupo_id = :grupo_id AND dia_semana = :dia_semana 
                    AND activo = true
                    AND (hora_inicio < :hora_fin AND hora_fin > :hora_inicio)";
            $result = $this->db->query($sql, [
                ':grupo_id' => $grupoId,
                ':dia_semana' => $diaSemana,
                ':hora_fin' => $horaFin,
                ':hora_inicio' => $horaInicio
            ]);
            if (!empty($result) && $result[0]['count'] > 0) {
                $conflicts['grupo']['exists'] = true;
            }
        }

        return $conflicts;
    }

    private function generarPlantilla($tipo, $formato = 'csv')
    {
        if ($formato === 'xlsx') {
            $this->generarPlantillaXLSX($tipo);
        } else {
            $this->generarPlantillaCSV($tipo);
        }
    }

    private function generarPlantillaCSV($tipo)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_' . $tipo . '.csv"');

        $output = fopen('php://output', 'w');
        
        // Agregar BOM para UTF-8 (para Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $this->escribirDatosPlantilla($output, $tipo);

        fclose($output);
        exit;
    }

    private function generarPlantillaXLSX($tipo)
    {
        // Verificar que la extensión ZIP esté disponible
        if (!extension_loaded('zip')) {
            // Si no hay ZIP, generar CSV como fallback
            $this->generarPlantillaCSV($tipo);
            return;
        }

        // Crear archivo ZIP temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        unlink($tempFile);
        $zipFile = $tempFile . '.xlsx';
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            // Si falla, generar CSV como fallback
            $this->generarPlantillaCSV($tipo);
            return;
        }

        try {
            // Crear estructura XLSX básica
            $this->crearEstructuraXLSX($zip, $tipo);
            
            $zip->close();

            // Enviar archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="plantilla_' . $tipo . '.xlsx"');
            header('Content-Length: ' . filesize($zipFile));
            header('Cache-Control: max-age=0');
            
            readfile($zipFile);
            unlink($zipFile);
            exit;

        } catch (Exception $e) {
            if ($zip->close() === false) {
                // Ya está cerrado
            }
            if (file_exists($zipFile)) {
                unlink($zipFile);
            }
            // Fallback a CSV
            $this->generarPlantillaCSV($tipo);
        }
    }

    private function escribirDatosPlantilla($output, $tipo)
    {
        switch ($tipo) {
            case 'docentes':
                fputcsv($output, ['ci', 'nombre', 'apellido', 'email', 'telefono', 'password']);
                fputcsv($output, ['12345678', 'Juan', 'Pérez', 'juan.perez@example.com', '555-0101', 'password123']);
                break;
            
            case 'materias':
                fputcsv($output, ['codigo', 'nombre', 'descripcion', 'nivel', 'carga_horaria']);
                fputcsv($output, ['MAT101', 'Matemáticas I', 'Fundamentos de matemáticas', 'Primer Semestre', '4']);
                break;
            
            case 'grupos':
                fputcsv($output, ['numero', 'semestre', 'turno', 'materia_codigo', 'docente_email', 'capacidad_maxima']);
                fputcsv($output, ['G1-MAT101', '2024-1', 'Mañana', 'MAT101', 'juan.perez@example.com', '30']);
                break;
            
            case 'aulas':
                fputcsv($output, ['codigo', 'nombre', 'capacidad', 'tipo', 'ubicacion', 'equipamiento']);
                fputcsv($output, ['A101', 'Aula 101', '30', 'Teórica', 'Edificio A - Primer Piso', 'Proyector, Pizarra']);
                break;
            
            case 'horarios':
                fputcsv($output, ['grupo_numero', 'dia_semana', 'hora_inicio', 'hora_fin', 'aula_codigo', 'docente_email']);
                fputcsv($output, ['G1-MAT101', 'Lunes', '08:00:00', '10:00:00', 'A101', 'juan.perez@example.com']);
                break;
        }
    }

    private function crearEstructuraXLSX($zip, $tipo)
    {
        // Obtener datos de la plantilla
        $headers = [];
        $exampleRow = [];
        
        switch ($tipo) {
            case 'docentes':
                $headers = ['ci', 'nombre', 'apellido', 'email', 'telefono', 'password'];
                $exampleRow = ['12345678', 'Juan', 'Pérez', 'juan.perez@example.com', '555-0101', 'password123'];
                break;
            
            case 'materias':
                $headers = ['codigo', 'nombre', 'descripcion', 'nivel', 'carga_horaria'];
                $exampleRow = ['MAT101', 'Matemáticas I', 'Fundamentos de matemáticas', 'Primer Semestre', '4'];
                break;
            
            case 'grupos':
                $headers = ['numero', 'semestre', 'turno', 'materia_codigo', 'docente_email', 'capacidad_maxima'];
                $exampleRow = ['G1-MAT101', '2024-1', 'Mañana', 'MAT101', 'juan.perez@example.com', '30'];
                break;
            
            case 'aulas':
                $headers = ['codigo', 'nombre', 'capacidad', 'tipo', 'ubicacion', 'equipamiento'];
                $exampleRow = ['A101', 'Aula 101', '30', 'Teórica', 'Edificio A - Primer Piso', 'Proyector, Pizarra'];
                break;
            
            case 'horarios':
                $headers = ['grupo_numero', 'dia_semana', 'hora_inicio', 'hora_fin', 'aula_codigo', 'docente_email'];
                $exampleRow = ['G1-MAT101', 'Lunes', '08:00:00', '10:00:00', 'A101', 'juan.perez@example.com'];
                break;
        }

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
<sheet name="Sheet1" sheetId="1" r:id="rId1"/>
</sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // Crear sharedStrings.xml
        $allStrings = array_merge($headers, $exampleRow);
        $sharedStringsXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($allStrings) . '" uniqueCount="' . count($allStrings) . '">';
        
        foreach ($allStrings as $string) {
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
        $colIndex = 0;
        foreach ($headers as $header) {
            $colLetter = $this->indexToColumn($colIndex);
            $stringIndex = $colIndex;
            $sheetXML .= '<c r="' . $colLetter . '1" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
        }
        $sheetXML .= '</row>';
        
        // Fila 2: Ejemplo
        $sheetXML .= '<row r="2">';
        $colIndex = 0;
        foreach ($exampleRow as $value) {
            $colLetter = $this->indexToColumn($colIndex);
            $stringIndex = count($headers) + $colIndex;
            $sheetXML .= '<c r="' . $colLetter . '2" t="s"><v>' . $stringIndex . '</v></c>';
            $colIndex++;
        }
        $sheetXML .= '</row>';
        
        $sheetXML .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXML);
    }

    /**
     * Convertir índice a letra de columna (0=A, 1=B, ..., 25=Z, 26=AA, etc.)
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

    private function guardarRegistroCarga($tipo, $nombreArchivo, $resultado, $usuarioId)
    {
        $sql = "INSERT INTO carga_masiva (tipo, archivo_nombre, total_registros, registros_exitosos, 
                registros_fallidos, errores, procesado_por, estado)
                VALUES (:tipo, :archivo_nombre, :total_registros, :registros_exitosos, 
                :registros_fallidos, :errores, :procesado_por, 'completado')";
        
        $this->db->query($sql, [
            ':tipo' => $tipo,
            ':archivo_nombre' => $nombreArchivo,
            ':total_registros' => $resultado['total'],
            ':registros_exitosos' => $resultado['exitosos'],
            ':registros_fallidos' => $resultado['fallidos'],
            ':errores' => !empty($resultado['errores']) ? implode("\n", $resultado['errores']) : null,
            ':procesado_por' => $usuarioId
        ]);
    }

    private function getRolId($nombreRol)
    {
        $rol = $this->db->query("SELECT id FROM roles WHERE nombre = :nombre", [
            ':nombre' => $nombreRol
        ]);
        return !empty($rol) ? $rol[0]['id'] : null;
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
