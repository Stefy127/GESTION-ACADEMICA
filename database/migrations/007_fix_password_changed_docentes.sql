-- Corrección: marcar como password_changed = false para docentes que nunca iniciaron sesión
-- Esto fuerza el flujo de cambio de contraseña en el primer login para docentes
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns
        WHERE table_name = 'usuarios' AND column_name = 'password_changed'
    ) THEN
        UPDATE usuarios u
        SET password_changed = false
        FROM roles r
        WHERE u.rol_id = r.id
          AND r.nombre = 'docente'
          AND u.ultimo_acceso IS NULL
          AND (u.password_changed IS NULL OR u.password_changed = true);
    END IF;
END $$;
