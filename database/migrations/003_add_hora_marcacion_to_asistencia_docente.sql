-- Migración para agregar la columna hora_marcacion a la tabla asistencia_docente
-- Si la columna ya existe, este script no hará nada

-- Verificar si la columna existe antes de agregarla
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'asistencia_docente' 
        AND column_name = 'hora_marcacion'
    ) THEN
        ALTER TABLE asistencia_docente 
        ADD COLUMN hora_marcacion TIMESTAMP;
        
        -- Actualizar registros existentes con la fecha actual si no tienen hora_marcacion
        UPDATE asistencia_docente 
        SET hora_marcacion = COALESCE(created_at, CURRENT_TIMESTAMP)
        WHERE hora_marcacion IS NULL;
        
        -- Hacer la columna NOT NULL después de actualizar los valores existentes
        ALTER TABLE asistencia_docente 
        ALTER COLUMN hora_marcacion SET NOT NULL;
        
        RAISE NOTICE 'Columna hora_marcacion agregada exitosamente';
    ELSE
        RAISE NOTICE 'La columna hora_marcacion ya existe';
    END IF;
END $$;

