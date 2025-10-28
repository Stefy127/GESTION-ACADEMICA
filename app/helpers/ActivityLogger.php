<?php
/**
 * Helper para registrar actividades en los logs
 */
class ActivityLogger
{
    private static $db = null;
    
    private static function getDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Registrar actividad
     */
    public static function log($action, $table = null, $recordId = null, $oldData = null, $newData = null)
    {
        // Obtener usuario actual de la sesión
        $userId = $_SESSION['user']['id'] ?? null;
        
        if (!$userId) {
            return; // No registrar si no hay usuario en sesión
        }
        
        try {
            $db = self::getDb();
            $sql = "INSERT INTO logs_actividad (usuario_id, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip_address, user_agent) 
                    VALUES (:user_id, :action, :table, :record_id, :old_data, :new_data, :ip, :user_agent)";
            
            $db->query($sql, [
                'user_id' => $userId,
                'action' => $action,
                'table' => $table,
                'record_id' => $recordId,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }
    
    /**
     * Log de creación
     */
    public static function logCreate($table, $recordId, $data)
    {
        self::log("crear $table", $table, $recordId, null, $data);
    }
    
    /**
     * Log de actualización
     */
    public static function logUpdate($table, $recordId, $oldData, $newData)
    {
        self::log("actualizar $table", $table, $recordId, $oldData, $newData);
    }
    
    /**
     * Log de eliminación
     */
    public static function logDelete($table, $recordId, $data = null)
    {
        self::log("eliminar $table", $table, $recordId, $data, null);
    }
    
    /**
     * Log de login
     */
    public static function logLogin($userId)
    {
        self::log("login", "usuarios", $userId);
    }
    
    /**
     * Log de logout
     */
    public static function logLogout($userId)
    {
        self::log("logout", "usuarios", $userId);
    }
    
    /**
     * Log de visualización
     */
    public static function logView($table, $recordId = null)
    {
        self::log("ver $table", $table, $recordId);
    }
}

