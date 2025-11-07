-- Migración para agregar el campo password_changed a la tabla usuarios
-- Este campo indica si un docente ha cambiado su contraseña por primera vez

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'usuarios' 
        AND column_name = 'password_changed'
    ) THEN
        ALTER TABLE usuarios 
        ADD COLUMN password_changed BOOLEAN DEFAULT false;
        
        -- Marcar como true para usuarios existentes (ya han usado el sistema)
        UPDATE usuarios 
        SET password_changed = true 
        WHERE password_changed = false;
    END IF;
END $$;

