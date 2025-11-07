<?php
/**
 * Clase para manejar la conexión a la base de datos
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';
        $this->connect();
    }
    
    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establecer conexión con PostgreSQL
     */
    private function connect()
    {
        $dbConfig = $this->config['database'];
        
        // Log para debugging (remover en producción)
        error_log("DEBUG DB Config: " . json_encode($dbConfig));
        
        // Detectar si estamos en Cloud Run (host es un socket Unix)
        if (strpos($dbConfig['host'], '/cloudsql/') === 0) {
            // Cloud Run usa un socket Unix para Cloud SQL
            $dsn = "pgsql:host={$dbConfig['host']};dbname={$dbConfig['name']}";
            error_log("DEBUG: Using Cloud SQL Unix socket: {$dbConfig['host']}");
        } else {
            // Conexión local o directa
            $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}";
            error_log("DEBUG: Using direct connection: {$dbConfig['host']}:{$dbConfig['port']}");
        }
        
        error_log("DEBUG: DSN = $dsn");
        
        try {
            $this->connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            // Configurar zona horaria de Bolivia en PostgreSQL
            $this->connection->exec("SET timezone = 'America/La_Paz'");
            
            error_log("DEBUG: Database connection successful");
        } catch (PDOException $e) {
            error_log("DEBUG: Database connection failed: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar consulta preparada
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            
            // Preparar parámetros y manejar tipos de datos explícitamente
            foreach ($params as $key => $value) {
                // Detectar si es un campo booleano SOLO por el nombre del parámetro (NO por el SQL)
                // Solo campos conocidos como booleanos en nuestra base de datos
                $isBooleanField = (stripos($key, 'password_changed') !== false);
                
                // Lista de nombres de parámetros que son campos booleanos
                $booleanParamNames = [
                    'password_changed',
                    'activo',
                    'activa'
                ];
                
                $isBooleanParam = false;
                foreach ($booleanParamNames as $boolParam) {
                    if (stripos($key, $boolParam) !== false) {
                        $isBooleanParam = true;
                        break;
                    }
                }
                
                // Manejar tipos de datos explícitamente
                if ($isBooleanParam && !is_int($value) && !is_float($value)) {
                    // Para campos booleanos conocidos, convertir a booleano explícito
                    $boolValue = false; // Valor por defecto
                    
                    if (is_bool($value)) {
                        $boolValue = $value;
                    } elseif ($value === true || $value === 'true' || $value === '1' || $value === 1 || $value === 'on') {
                        $boolValue = true;
                    } elseif ($value === false || $value === 'false' || $value === '0' || $value === 0 || $value === '' || $value === null) {
                        $boolValue = false;
                    } else {
                        // Cualquier otro valor no vacío se considera true
                        $boolValue = !empty($value);
                    }
                    
                    // Usar bindValue con PDO::PARAM_BOOL explícitamente
                    $stmt->bindValue($key, $boolValue, PDO::PARAM_BOOL);
                } elseif (is_int($value) || (is_string($value) && ctype_digit($value) && stripos($key, 'id') !== false)) {
                    // Parámetros de ID o enteros
                    $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                } elseif (is_null($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_NULL);
                } elseif (is_bool($value)) {
                    // Si es booleano pero no está en la lista de campos booleanos conocidos
                    // Convertir a string para evitar problemas
                    $stmt->bindValue($key, $value ? 'true' : 'false', PDO::PARAM_STR);
                } else {
                    // Por defecto, tratar como string
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            
            // Si es una consulta SELECT, retornar los resultados
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll();
            }
            
            // Para INSERT, UPDATE, DELETE retornar el número de filas afectadas
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit()
    {
        return $this->connection->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }
    
    /**
     * Obtener la conexión PDO directamente
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
