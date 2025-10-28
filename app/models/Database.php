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
            $stmt->execute($params);
            
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
