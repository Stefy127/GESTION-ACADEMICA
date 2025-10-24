<?php
/**
 * Clase base para todos los modelos
 */
abstract class Model
{
    protected $db;
    protected $table;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql);
    }
    
    /**
     * Obtener un registro por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    /**
     * Crear un nuevo registro
     */
    public function create($data)
    {
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        return $this->db->query($sql, $data);
    }
    
    /**
     * Actualizar un registro
     */
    public function update($id, $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);
        
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        $data['id'] = $id;
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Eliminar un registro
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
